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
	$this->template->childs = $this->ct->getChildsFrom(2);
	$this->template->parents = $this->ct->getParentsFrom(4);
    }

}
