<?php

/**
 * Homepage presenter.
 *
 * @author     John Doe
 * @package    MyApplication
 */
class HomepagePresenter extends BasePresenter {

    /** @var Ctable */
    private $ct;

    public function startup(){
	parent::startup();
	
	$this->ct = $this->getService('ctable');
    }

    public function renderDefault() {
	// potomky kategorie s id 2 (vcetne)
	$this->template->childs = $this->ct->getChildsFrom(2);
	// vsechyn rodice od id 4 (vcetne)
	$this->template->parents = $this->ct->getParentsFrom(4);
	// vlozit novou kategori
	$this->ct->insertItem(array('name' => 'test', '10'));
    }

}
