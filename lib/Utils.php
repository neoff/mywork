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
	
	/**
	 * из массива делает URL
	 * @param array $url
	 */
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
	 * декоратор для валидации пост запросов
	 * helper, входящий массив должен быть в виде array("поле валидации" => array('' -> object( required, type)))
	 * @param array $array - массив для валидации
	 * @param array $post - пост запрос
	 */
	function validatePost($array, $post)
	{
		foreach ($array as $key => $value)
		{
			//var_dump($key);
			if(array_key_exists($key, $post))
			{
				//проверяем обязательное поле
				if(array_key_exists('required', get_object_vars($value)))
				{
					if(empty($post[$key]))
						return false;
					
				}
				
				//проверяем минимальную длинну
				if(array_key_exists('min', get_object_vars($value)))
				{
					if(strlen($post[$key] < $value->len))
						return false;
					
				}
				
				//проверяем максимальную длинну
				if(array_key_exists('max', get_object_vars($value)))
				{
					if(strlen($post[$key] > $value->len))
						return false;
					
				}
				//проверяем тип 
				if(array_key_exists('type', get_object_vars($value)))
				{
					$type = "is_".$value->type;
					if(!call_user_func($type, $post[$key]))
						return false;
					
				}
				//var_dump($key,$value, $post);
			}
			else
				return false;
		}
		return true;
	}
	
	/**
	 * собирает данные из пост запроса для записи в базу
	 * @param obj $obj
	 * @param obj $validate
	 * @param array $post
	 */
	function createDatabaseObject(&$obj, $validate, $post)
	{
		foreach ($validate as $key => $value)
		{
			$obj->$key = $post[$key];
		}
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
	
	
	