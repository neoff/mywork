<?php

	define ( 'LOWERCASE', 3 );
	define ( 'UPPERCASE', 1 );
	
	function ToUTF($string) {
		$encode =  detect_cyr_charset($string);
		return iconv ( $encode, "UTF-8", $string );
	}
	
	function StripTags($string) {
		$patterns = array ("/<br\s*?\/?>/", "/&nbsp;/", "/(&laquo;|&raquo;)/", "/&/", "/(<|>)/" );
		$replacements = array ("\n", " ", '"', " and ", "" );
		$string = preg_replace ( $patterns, $replacements, $string );
		$string = html_entity_decode ( $string );
		$string = strip_tags ( $string );
		return ToUTF ( $string );
	}
	
	
	function detect_cyr_charset($str) {
		
		$charsets = Array (
	
		'KOI8-R' => 0, 
	
		'CP1251' => 0, 
	
		'CP866' => 0, 
	
		'UTF-8' => 0, 
	
		'mac-cyrillic-2000' => 0 )
	
		;
		
		for($i = 0, $length = strlen ( $str ); $i < $length; $i ++) {
			
			$char = ord ( $str [$i] );
			
			//non-russian characters
			
	
			if ($char < 128 || $char > 256)
				continue;
			
			//CP866
			
	
			if (($char > 159 && $char < 176) || ($char > 223 && $char < 242))
				
				$charsets ['CP866'] += LOWERCASE;
			
			if (($char > 127 && $char < 160))
				$charsets ['CP866'] += UPPERCASE;
			
			//KOI8-R
			
	
			if (($char > 191 && $char < 223))
				$charsets ['KOI8-R'] += LOWERCASE;
			
			if (($char > 222 && $char < 256))
				$charsets ['KOI8-R'] += UPPERCASE;
			
			//CP1251
			
	
			if ($char > 223 && $char < 256)
				$charsets ['CP1251'] += LOWERCASE;
			
			if ($char > 191 && $char < 224)
				$charsets ['CP1251'] += UPPERCASE;
			
			//MAC
			
//	
//			if ($char > 221 && $char < 255)
//				$charsets ['mac-cyrillic-2000'] += LOWERCASE;
//			
//			if ($char > 127 && $char < 160)
//				$charsets ['mac-cyrillic-2000'] += UPPERCASE;
//			
			//ISO-8859-5
			
	
			if ($char > 207 && $char < 240)
				$charsets ['UTF-8'] += LOWERCASE;
			
			if ($char > 175 && $char < 208)
				$charsets ['UTF-8'] += UPPERCASE;
		
		}
		
		arsort ( $charsets );
		
		return key ( $charsets );
	}