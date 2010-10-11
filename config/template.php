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
	
	public function __construct()
	{
		header('Content-type: text/xml');
		$xmlstr = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n<mvideo_xml date=\"" . date("Y-m-d H:i:s") . "\"></mvideo_xml>";
		$this->xml = new \SimpleXMLElement($xmlstr);
		
	}
	public function __destruct()
	{
		echo $this->xml->asXML();
	}
	
	public function Set( $param, $val="" )
	{
		return $this->xml->addChild( $param, $val );
	}


}



