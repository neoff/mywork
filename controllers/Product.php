<?php
/**
 *
 *
 * @package    controllers
 * @subpackage Product
 * @since      13.10.2010 10:21:04
 * @author     enesterov
 * @category   controllers
 */

	namespace Controllers;
	use Models;

class ControllerProduct extends Template{

	public function index( $array )
	{
		list($region_id, $product_id, $asc, $reviews)=$array;
		
		$options = array("_warecode"=>$product_id);
		list($where, $array) = Models\Warez::SetParam($region_id, $options);
		$productes = Models\Warez::sql($region_id, $where, $array);
		$productes = $productes[0];
		
		//$a=Models\Desclist::all(array('warecode'=>$product_id));
		//print_r($a);
		
		$product_m = $this->Set("product");
		$product_m->addChild("product_id", $product_id);
		$product_m->addChild("region_id", $region_id);
		$product_m->addChild("title", ToUTF($productes->ware));
		$product_m->addChild("small_price", $productes->inetprice);
		$product_m->addChild("price", $productes->price);
		$product_m->addChild("rating");
		$product_m->addChild("reviews_num");
		$product_m->addChild("description", StripTags($productes->descr));
		$options = $product_m->addChild("options");
		
		$options_m=Models\Desclist::all(array('warecode'=>$product_id));
		foreach ($options_m as $key => $val)
		{
			$option = $options->addChild("option");
			$option->addChild("name", ToUTF($val->prname));
			$option->addChild("value", ToUTF($val->prval));
		}
		
		if($asc)
		{
			$asc = $product_m->addChild("asc");
			$asc_m=array();
			foreach ($asc_m as $key => $val)
			{
				$prod = $asc->addChild("product");
				$prod->addChild("product_id");
				$prod->addChild("title");
				$prod->addChild("description");
				$prod->addChild("rating");
				$prod->addChild("reviews_num");
				$prod->addChild("small_price");
				$prod->addChild("price");
				$image = $prod->addChild("image");
				$image->addAttribute("width", "");
				$image->addAttribute("height", "");
			}
		}
		if($reviews)
		{
			$reviews = $product_m->addChild("reviews");
			$reviews_m=array();
			foreach ($reviews_m as $key => $val)
			{
				$review = $reviews->addChild("review");
				$review->addChild("date");
				$review->addChild("author");
				$review->addChild("city");
				$review->addChild("rating");
				$review->addChild("title");
				$review->addChild("text");
			}
		}
	}
}