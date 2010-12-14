<?php
/**  
 *  РїР°РєРµС‚ РїРѕРґРєР»СЋС‡РµРЅРёСЏ С€Р°Р±РѕР»РЅРѕРІ Рё РёСЃРїРѕР»РЅРµРЅРёСЏ РєРѕРґР°
 * 
 * @package    controllers
 * @subpackage templaets
 * @since      07.10.2010 12:13:10
 * @author     enesterov
 * @category   controller
 */

	namespace Controllers;
	
class Template{
	private $xml;
	public $head;
	
	public function __construct()
	{
		$child = get_called_class();
		
		$dtd = preg_replace("/Controllers\\C/", "", $child);
		$dtd = preg_replace("/Controller/", "", $dtd);
		$dtd = strtolower($dtd);
		
		$xmlstr = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n<!DOCTYPE mvideo_xml SYSTEM \"/mobile/public/$dtd.dtd\">\n<mvideo_xml date=\"" . date("Y-m-d H:i:s") . "\">\n</mvideo_xml>";
		$this->xml = new \SimpleXMLElement($xmlstr);
		
	}
	public function __destruct()
	{
		header('Content-type: text/xml; charset=utf-8');
		echo preg_replace("/></", ">\n<", $this->xml->asXML());
		//echo $this->xml->asXML();
	}
	
	public function SetType($doc)
	{
		$this->head = $doc;
	}
	
	public function Set( $param, $val="" )
	{
		return $this->xml->addChild( $param, $val );
	}


}



