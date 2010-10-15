<?php

	function ToUTF($string)
	{
		return iconv("CP1251", "UTF-8", $string);
	}

	function StripTags($string)
	{
		$patterns = array("/<br\s*?\/?>/", "/&nbsp;/", "/(&laquo;|&raquo;)/", "/&/", "/(<|>)/");
		$replacements = array("\n", " ", '"', "and", "");
		$string = preg_replace($patterns, $replacements, $string);
		$string = html_entity_decode($string);
		$string = strip_tags($string);
		return ToUTF($string);
	}