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
		$doc=$this->xml->asXML();
		
		
		//print preg_replace("/></", ">\n<", $doc);
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