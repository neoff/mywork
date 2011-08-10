<?php
/**
  * 
  *
  * @package    Links
  * @subpackage Controllers
  * @since      Aug 9, 2011 2:49:29 PM
  * @author     enesterov
  * @category   controller
  *
  */
  
	namespace Controllers;

	use Models;
	use Template;
	
class ControllerLinks extends Template\Template{
	
	private $link;
	private $class_link = array();
	
	public function index()
	{
		$this->setVar();
		$this->link = get_key('links');
		$this->getLink();
	}
	
	private function getLink()
	{
		$this->listClassLinks();
		if($this->link === '0')
			return $this->displayLinks();
		
		if(isset($this->class_link[strtolower($this->link)]))
			return $this->displayLink($this->class_link[strtolower($this->link)]);
			//var_dump($this->class_link[strtolower($this->link)]);
		
		return false;
		
	}
	
	private function listClassLinks()
	{
		foreach (get_class_methods($this) as $value)
		{
			if(strlen($value)>4)
			{
				$key = substr($value, 0, 4);
				if($key == 'link')
				{
					$this->class_link[strtolower(substr($value, 4))] = $value;
				}
			}
		}
		return $this->class_link;
	}
	
	
	
	private function displayLinks()
	{
		$this->links = "";
		//var_dump(get_class_methods($this));
		//var_dump($this->class_link);
		$desc = $this->listLinkDescription();
		foreach ($this->class_link as $key => $value)
		{
			//var
			$link = $this->links->addChild("link");
			$link->addChild("id", $key);
			$link->addChild("description", $desc[$key]["description"]);
		}
	}
	
	private function displayLink($link_id)
	{
		$link = $this->xml->addChild('link_content');
		//var_dump($link_id);
		$param = $this->{$link_id}();
		foreach ($param as $key => $value)
		{
			$link->addChild($key, $value);
		}
	}
	
	private function listLinkDescription()
	{
		return array(
					'pdo' => array("description"=>"Информация о ПДО", 
									"small"=>"http://service.mvideo.ru/pdo/", 
									"big"=>"http://service.mvideo.ru/pdo/", 
									"html"=>""
								),
					'credit' => array("description"=>"Информация о получении кредита", 
									"small"=>"http://www.mvideo.ru/credit/", 
									"big"=>"http://www.mvideo.ru/credit/", 
									"html"=>""
								),
					'delivery' => array("description"=>"Условия бесплатной доставки", 
										"small"=>"http://service.mvideo.ru/delivery/#moscow", 
										"big"=>"http://service.mvideo.ru/delivery/#moscow",
										"html"=>""
										),
					'service' => array("description"=>"Информация о сервисах", 
									"small"=>"http://www.mvideo-service.ru/index.aspx?c=7700000000000", 
									"big"=>"http://www.mvideo-service.ru/index.aspx?c=7700000000000", 
									"html"=>""
								),
					'pickup' => array("description"=>"Информация о програме Pick-up", 
									"small"=>"http://www.mvideo.ru/pickup/", 
									"big"=>"http://www.mvideo.ru/pickup/", 
									"html"=>""
								),
					);
	}
	private function linkCredit()
	{
		$return = $this->listLinkDescription();
		return $return['credit'];
	}
	
	private function linkPdo()
	{
		$return = $this->listLinkDescription();
		return $return['pdo'];
	}
	
	private function linkDelivery()
	{
		$return = $this->listLinkDescription();
		return $return['delivery'];
	}
	private function linkService()
	{
		$return = $this->listLinkDescription();
		return $return['service'];
	}
	private function linkPickup()
	{
		$return = $this->listLinkDescription();
		return $return['pickup'];
	}
	
}