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
		
		header('Content-type: text/xml; charset=utf-8');
		$xmlstr = "<!DOCTYPE $dtd SYSTEM \"http://localhost/public/$dtd.dtd\">\n<mvideo_xml date=\"" 
		. date("Y-m-d H:i:s") . "\"></mvideo_xml>";
		$this->xml = new \SimpleXMLElement($xmlstr);
		
	}
	public function __destruct()
	{
		echo $this->xml->asXML();
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



