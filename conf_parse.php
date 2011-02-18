<?php
/**  
 * 
 * 
 * @package    root
 * @subpackage Category
 * @since      08.10.2010 14:00:27
 * @author     enesterov
 * @category   utils
 */
 
class config{
		public function __set($name, $val)
		{
			$this->$name = $val;
		}
		public function __get($name)
		{
			$a = get_object_vars  ( $this  );
			if(!array_key_exists  ( $name , $a  ))
				$this->$name = "";
			return $this->$name;
			
			
		}
		public function __construct($file)
		{
			if(file_exists($file))
			{
				$conn = parse_ini_file(FILE);
				foreach ($conn as $key => $val) {
					$this->$key = $val;
				}
			}
		}
}