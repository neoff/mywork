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

class Template extends Blitz{
	public function __construct(){}
	public function __destruct(){}
}
	
	$tpl = new Blitz(ROOT_PATH . '/public/index.html');
	print $tpl->parse();
	
	$obj = new SimpleXMLElement('<root>
    <a>1.9</a>
    <b>1.9</b>
</root>');

var_dump($obj->a + $obj->b);
var_dump((float)$obj->a + (float)$obj->b);
var_dump((string)$obj->a + (string)$obj->b);