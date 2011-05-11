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
	use \Memcache;
	
abstract class Template {
	
	/**
	 * DOM объекты
	 * @var object
	 */
	public $xml;
	
	/**
	 * мэм кеш
	 * @var obj
	 */
	protected $mem;
	
	/**
	 * время жизни кеша
	 * @var int
	 */
	public $mem_time = 3600;
	
	/**
	 * сброс мемкеша
	 * @var bool
	 */
	public $mem_flush = false;
	
	/**
	 * ключ для мемкеша
	 * @var string
	 */
	public $mem_key = false;
	
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
		//var_dump($_SERVER);
		$this->mem = new \Memcache;
		$this->mem->pconnect('localhost', 11211);
		$child = get_called_class();
		
		$dtd = preg_replace("/Controllers\\C/", "", $child);
		$dtd = preg_replace("/Controller/", "", $dtd);
		$dtd = strtolower($dtd);
		
		//$this->mem_key = $dtd;
		
		$xmlstr = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n<!DOCTYPE mvideo_xml SYSTEM \"http://".$_SERVER['HTTP_HOST'].$_SERVER['DOCUMENT_URI']."public/$dtd.dtd\">\n<mvideo_xml date=\"" . date("c") . "\">\n</mvideo_xml>";
		
		$this->xml = new \SimpleXMLElement($xmlstr);
		
	}
	
	/**
	 * проверяет мемкеш, возвращает объект или bool
	 */
	public function getMemObj()
	{
		if($this->mem_key)
			return $this->mem->get($this->mem_key);
			
		return false;
	}
	
	/**
	 * выводит собраный документ на страницу
	 */
	public function __destruct()
	{
		global $except;
		
		$mem = $this->getMemObj();
		
		if(!$this->mem_flush && $mem)
		{
			return $this->displayXml($mem);
		}
		
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
				if (!$isValid) 
				{
					print 111;
					throw new \MyDomException($myDoc->errors);
				}
				
				if(DEBUG)
					$doc = preg_replace("/></", ">\n<", $doc);
				
				
				if($this->mem_key)
					$this->mem->set($this->mem_key, $doc, MEMCACHE_COMPRESSED, $this->mem_time);
					
				$this->displayXml($doc);
			/*}
			catch(\MyException $e)
			{
				echo $e;
			}*/
		}
	}
	
	/**
	 * выводит информацию на страницу
	 * @param string $xml
	 */
	private function displayXml($xml)
	{
		header('Content-type: text/xml; charset=utf-8');
		echo $xml;
		return true;
	}
	
	/**
	 * устанавливает основные переменные из $_GET запроса
	 */
	protected function setVar()
	{
		$this->mem_flush = get_key('flush', false);
	}
	

}
