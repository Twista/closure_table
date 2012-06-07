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

    public function __construct(Nette\Database\Connection $database) {
	$this->db = $database;
    }

    public function getChildsFrom($id) {
	/* alternative
	 * use
	 * return $this->db->table($this->ctable)->where('ancestor',$id)->order('depth ASC');
	 * and in template
	 * $item->ref('descendant')->name or whatever from table
	 */
	return $this->db->query("SELECT c.*, cc.depth FROM " . $this->table . " c
				    JOIN " . $this->ctable . " cc
					ON (c.category_id = cc.descendant)
				   WHERE cc.ancestor = ?", $id);
    }

    public function getParentsFrom($id) {
	return $this->db->query("SELECT c.*, depth FROM " . $this->table . " c
				    JOIN " . $this->ctable . " cc
					ON (c.category_id = cc.ancestor)
				WHERE cc.descendant = ?", $id);
    }

}

?>
