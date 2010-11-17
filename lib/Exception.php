<?php
/**  
 * exception  настройка ошибок
 * 
 * @package    config
 * @subpackage exceprion
 * @since      07.10.2010 11:35:21
 * @author     enesterov
 * @category   none
 */

	set_error_handler(create_function('$c, $m, $f, $l', 'throw new MyException($m, $c, $f, $l);'), E_ALL);
	
class MyException extends Exception {
	public function __construct($message="", $code=2, $filename="", $lineno="") {
		parent::__construct($message, $code);
		//print $this->getMessage();
		//print_r($this->getTrace());
		$this->file = $filename;
		$this->line = $lineno;
	}
	public function __toString () 
	{
		print "Stack trace:<br>\n";
		foreach ($this->getTrace() as $key => $val)
		{
			$args = "";
			foreach ($val['args'] as $vals)
			{
				if($vals=="" or $vals===false) $vals="false";
				$args .=" ".$vals;
			}
			print sprintf("<b>#%s</b> %s(%s): %s%s%s(%s)<br>\n",
							$key,
							$val['file'],
							$val['line'],
							$val['class'],
							$val['type'],
							$val['function'],
							$args
						);
		}
		return " ";
	}
}


