<?php

	function ToUTF($string)
	{
		return iconv("CP1251", "UTF-8", $string);
	}

	function StripTags($string)
	{
		$string = preg_replace("/<br\s*?\/?>/", "\n", $string);
		$string = strip_tags($string);
		return ToUTF($string);
	}