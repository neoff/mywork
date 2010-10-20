<?php
/**  
 * 
 * 
 * @package    library
 * @subpackage Template
 * @since      18.10.2010 16:14:54
 * @author     enesterov
 * @category   none
 */

	namespace Template;
	
abstract class Template {
	public $xml;
	
	function __set($name="", $value = "")
	{
		//print $value;
		return $this->$name = $this->xml->addChild( $name, $value );
	}
	
	final public function __construct($data = "")
	{
		$child = get_called_class();
		
		$dtd = preg_replace("/Controllers\\C/", "", $child);
		$dtd = preg_replace("/Controller/", "", $dtd);
		$dtd = strtolower($dtd);
		
		$xmlstr = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n<!DOCTYPE mvideo_xml SYSTEM \"/public/$dtd.dtd\">\n<mvideo_xml date=\"" . date("Y-m-d H:i:s") . "\">\n</mvideo_xml>";
		
		$this->xml = new \SimpleXMLElement($xmlstr);
	}
	
	public function __destruct()
	{
		header('Content-type: text/xml; charset=utf-8');
		echo preg_replace("/></", ">\n<", $this->xml->asXML());
		//echo $this->xml->asXML();
	}
	

}