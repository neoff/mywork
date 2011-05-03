<?php
/**
  * выдает стартовую страницу и банер
  * 
  * @package    Start.php
  * @subpackage cotroller
  * @since      15.04.2011 13:09:29
  * @author     enesterov
  * @category   controller
  */

	namespace Controllers;
	use Models;
	use Template;
	
class ControllerStart extends InterfaceTemplate{
	public function index()
	{
		$this->region_id = 1;
		$this->includeFiles();
		$this->actions = 3;
		$this->startpage = "";
		$this->startpage->addChild("startpage_name");
		$this->getActionFederal($this->startpage);
		/*$this->startpage->addChild("banner_img");
		$this->startpage->addChild("action_name");
		$this->startpage->addChild("action_id");*/
	}
}