<?php

	define ( 'LOWERCASE', 3 );
	define ( 'UPPERCASE', 1 );
	
	spl_autoload_register('lib_autoload');
	
	/**
	 * автолоад для библиотек
	 * @param string $class_name
	 */
	function lib_autoload($class_name)
	{
		$path = LIB_PATH;
		$root = realpath(isset($path) ? $path : '.');
		$namespaces = get_namespaces($class_name);
		if ($namespaces)
		{
			$class_name = array_pop($namespaces);
			$directories = array();
	
			foreach ($namespaces as $directory)
			{
				$directories[] = $directory;
			}
			
			$root .= "/" . implode( $directories, "/" );
		}
		
		
		$file = "$root/$class_name.php";
		
		//var_dump($file);
		if (file_exists($file))
			require_once $file;
	}
	
	
	function makeUrl($url)
	{
		return implode($url, "/");
	}
	
	/**
	 * возвращает значение глобальной переменной $_GET или значение $returns 
	 * @param string $key
	 * @param (string|bool) $returns
	 */
	function get_key($key, $returns = false)
	{
		return (!array_key_exists($key, $_GET))?$returns:$_GET[$key];
	}
	
	/**
	 * возвращает массив из класса и namespace
	 * 
	 * @param string $class_name
	 * @return multitype:|NULL
	 * @access public
	 */
	function get_namespaces($class_name)
	{
		
		if (has_namespace($class_name))
			return explode('\\', $class_name);
		return null;
	}
	
	/**
	 * проверяет наличие namespace в названии класса
	 * @param string $class_name
	 * @return bool
	 */
	function has_namespace($class_name)
	{
		if (strpos($class_name, '\\') !== false)
			return true;
		return false;
	}
	
	/**
	 * переводит строку из cp1251 в utf-8
	 * @param string $string
	 */
	function ToUTF($string) {
		//$encode =  detect_cyr_charset($string);
		return iconv ( 'CP1251', "UTF-8", $string );
	}
	
	/**
	 * переводит строку и срезает лишние теги
	 * @param unknown_type $string
	 */
	function StripTags($string) {
		$patterns = array ("/<br\s*?\/?>/", "/&nbsp;/", "/(&laquo;|&raquo;)/", "/&/", "/(<|>)/" );
		$replacements = array ("\n", " ", '"', " and ", "" );
		$string = preg_replace ( $patterns, $replacements, $string );
		$string = stripslashes( $string );
		$string = html_entity_decode ( $string );
		$string = strip_tags ( $string );
		return ToUTF ( $string );
	}
	
	
	