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
	
class ControllerStart extends InterfaceTemplate
{
	/**
	 * ключ для мемкеша
	 * @var string
	 */
	protected $mkey;
	
	/**
	 * время хранения кеша
	 * @var int
	 */
	protected $mtime;
	
	public function index()
	{
		$this->setVar();
		$this->region_id = 1;
		$this->includeFiles();
		$this->action_id = 3;
		$this->mem_key = 'start';
		
		if(!$this->mem_flush && $this->getMemObj())
			return true;
		
		$this->startpage = "";
		$this->startpage->addChild("startpage_name");
		$this->getActionFederal($this->startpage);
		$this->mem_time = ($this->mtime - time());
		
		/*$this->startpage->addChild("banner_img");
		$this->startpage->addChild("action_name");
		$this->startpage->addChild("action_id");*/
	}
}