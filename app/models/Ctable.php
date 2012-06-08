<?php

/**
 * Description of Ctable
 * closure table implementation
 *
 * @author Twista
 */
class Ctable {

    /** @var Nette\Database\Connection */
    private $db;

    /** @var string */
    private $table = 'category';

    /** @var string */
    private $ctable = 'category_closure';

    /**
     * constructor
     * @param Nette\Database\Connection $database
     */
    public function __construct(Nette\Database\Connection $database) {
	$this->db = $database;
    }

    /**
     * set main table
     * @param string $table_name
     * @return Ctable
     */
    public function setTable($table_name) {
	$this->table = $table_name;
	return $this;
    }

    /**
     * set closure table
     * @param string $table_name
     * @return Ctable
     */
    public function setClosureTable($table_name) {
	$this->ctable = $table_name;
	return $this;
    }

    /**
     * 
     * @param int $id
     * @return Nette\Database\Statement
     */
    public function getChildsFrom($id) {
	/* alternative
	 * use
	 * return $this->db->table($this->ctable)->where('ancestor',$id)->order('depth ASC');
	 * and in template
	 * $item->ref('descendant')->name or whatever from table
	 */
	return $this->db->query("SELECT c.*, cc.depth FROM {$this->table} c
				    JOIN {$this->ctable} cc
					ON (c.{$this->table}_id = cc.descendant)
				   WHERE cc.ancestor = ?", $id);
    }

    /**
     * get parents from specific id
     * @param int $id
     * @return Nette\Database\Statement
     */
    public function getParentsFrom($id) {
	return $this->db->query("SELECT c.*, depth FROM {$this->table} c
				    JOIN {$this->ctable} cc
					ON (c.{$this->table}_id = cc.ancestor)
				WHERE cc.descendant = ?", $id);
    }

    /**
     * insert new item
     * @param Array[] $item_data
     * @param int $parent_id
     */
    public function insertItem($item_data, $parent_id) {

	$row = $this->db->table($this->table)->insert($item_data);
	
	$row_id = (int) $row->getPrimary();
	$this->db->table($this->ctable)->insert(array(
	    'ancestor' => $row_id,
	    'descendant' => $row_id,
	    'depth' => 0
	));

	$this->db->query("INSERT INTO {$this->ctable} (ancestor,descendant, depth)
	  SELECT ancestor, {$row_id}, depth+1 FROM {$this->ctable}
	  WHERE descendant = ?", $parent_id);
    }

    /**
     * delete specific item and enstabilish tree
     * @param int $id
     */
    public function delete($id) {
	$this->db->query("UPDATE {$this->ctable} SET depth = depth-1
        WHERE ancestor != descendant
                AND descendant IN (SELECT descendant FROM (SELECT * FROM {$this->ctable}) as did WHERE ancestor = ?)", $id);
	$this->db->table($this->table)->get($id)->delete();
    }

    /**
     * delete item with all sub_items
     * @param int $id
     */
    public function deleteTree($id) {
	$this->db->query("DELETE cc_a FROM {$this->ctable} cc_a JOIN {$this->ctable} cc_d USING (descendant) WHERE cc_d.ancestor = ?", $id);
	$this->db->query("DELETE FROM {$this->table} WHERE category_id = ?", $id);
    }

    /**
     * check if id is leaf or root of tree
     * @param int $id
     * @return \Nette\Database\Statement
     */
    public function isLeafRoot($id) {
	return $this->db->query("SELECT c.*, cc.depth,
  IF((SELECT COUNT(*) FROM {$this->ctable} WHERE descendant = c.category_id)=?,?,0) is_root,
  IF((SELECT COUNT(*) FROM {$this->ctable} WHERE ancestor = c.category_id)=?,?,0) is_leaf
    FROM {$this->table} c
    JOIN {$this->ctable} cc
      ON (c.category_id = cc.descendant)
    WHERE cc.ancestor = ?", $id, $id, $id, $id, $id);
    }

    /**
     * move subtree
     * @param int $from
     * @param int $to
     */
    public function move($from, $to) {
	$this->db->query("DELETE cc_a FROM {$this->ctable} cc_a
  JOIN {$this->ctable} cc_d USING(descendant)
  LEFT JOIN {$this->ctable} cc_x
    ON cc_x.ancestor = cc_d.ancestor AND cc_x.descendant = cc_a.ancestor
  WHERE cc_d.ancestor = ? AND cc_x.ancestor IS NULL", $from);

	$this->db->query("INSERT INTO {$this->ctable} (ancestor, descendant, depth)
  SELECT supertree.ancestor, subtree.descendant, supertree.depth+subtree.depth+1
    FROM {$this->ctable} AS supertree JOIN {$this->ctable} AS subtree
    WHERE subtree.ancestor = ?
    AND supertree.descendant = ?", $from, $to);
    }

}

?>
