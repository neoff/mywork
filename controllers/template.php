<?php
/**  
 *  пакет подключения шаболнов и исполнения кода
 * 
 * @package    controllers
 * @subpackage templaets
 * @since      07.10.2010 12:13:10
 * @author     enesterov
 * @category   controller
 */

	namespace Template;
	
class Template{
	public $xml;
	private $x;
	public function __construct()
	{
		$xmlstr = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n<mvideo_xml date=\"" . date("Y-m-d H:i:s") . "\"></mvideo_xml>";
		$this->xml = new SimpleXMLElement($xmlstr);
	}
	public function __destruct()
	{
		$this->x = False;
		echo $this->xml->asXML();
	}
	public function set( $param, $val="" )
	{
		$this->x = $this->xml->addChild( $param, $val );
	}
	public function attr( $parm, $val = "" )
	{
		$this->x->addAttribute( $parm, $val );
	}

}

