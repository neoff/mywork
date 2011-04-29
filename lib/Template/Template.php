<?php
/**  
 * шаблонизатор для вывода ml файлов
 * 
 * @package    library
 * @subpackage Template
 * @since      18.10.2010 16:14:54
 * @author     enesterov
 * @category   none
 * 
 * @global Template - клас шаблонов
 * 
 */

	namespace Template;
	
abstract class Template {
	
	/**
	 * текущий номер региона
	 * @var string
	 */
	protected $region_id;
	
	/**
	 * текущий номер категории
	 * @var int
	 */
	protected $category_id;
	
	/**
	 * текущая акция
	 * @var string
	 */
	protected $actions;
	
	/**
	 * результатт поиска в БД
	 * @var obj
	 */
	protected $searches;
	
	/**
	 * модификатор для создания $dir_id
	 * @var int
	 */
	protected static $Mult = 1000000000;
	
	/**
	 * модификатор для создания $class_id
	 * @var int
	 */
	protected static $MultC = 100000;
	
	/**
	 * модификатор для создания $group_id
	 * @var int
	 */
	protected static $MultG = 1;
	
	/**
	 * текущая страница
	 * @var int
	 */
	protected $page;
	protected $product_id;
	protected $ask;
	protected $reviews;
	
	/**
	 * DOM объекты
	 * @var object
	 */
	public $xml;
	
	/**
	 * переназначаем сетер для создания ноды xml 
	 * @param string $name
	 * @param string $value
	 */
	function __set($name="", $value = "")
	{
		//print $value;
		return $this->$name = $this->xml->addChild( $name, $value );
	}
	
	/**
	 * конструктор создает заголовок xml файла 
	 * в зависимости от того, из какого класа он вызван 
	 * @param string $data
	 */
	public function __construct($data = "")
	{
		$child = get_called_class();
		
		$dtd = preg_replace("/Controllers\\C/", "", $child);
		$dtd = preg_replace("/Controller/", "", $dtd);
		$dtd = strtolower($dtd);
		
		$xmlstr = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n<!DOCTYPE mvideo_xml SYSTEM \"http://".$_SERVER['HTTP_HOST']."/mobile/public/$dtd.dtd\">\n<mvideo_xml date=\"" . date("Y-m-d H:i:s") . "\">\n</mvideo_xml>";
		
		$this->xml = new \SimpleXMLElement($xmlstr);
		
	}
	
	/**
	 * выводит собраный документ на страницу
	 */
	public function __destruct()
	{
		global $except;
		$doc=$this->xml->asXML();
		
		
		//print preg_replace("/></", ">\n<", $doc);
		if(!$except)
		{
			//try
			//{
				$dom = new \DOMDocument;
				$dom->loadXML($doc, LIBXML_DTDLOAD|LIBXML_DTDATTR);
				$myDoc = new MyDOMDocument($dom);
				$isValid = $myDoc->validate();
				/*if (!$isValid) 
				{
					throw new \MyDomException($myDoc->errors);
				}*/
				header('Content-type: text/xml; charset=utf-8');
				echo preg_replace("/></", ">\n<", $doc);
			/*}
			catch(\MyException $e)
			{
				echo $e;
			}*/
		}
	}
	
	/**
	 * устанавливает основные переменные из $_GET запроса
	 */
	protected function setVar()
	{
		$this->region_id = get_key('region_id', 0);
		$this->category_id = get_key('category_id', -1);
		$this->actions = get_key('action', -1);
		$this->searches = get_key('search');
		$this->page = get_key('page', 0);
		$this->product_id = get_key('product_id');
		$this->ask = get_key('aks');
		$this->reviews = get_key('reviews');
	}
	

}