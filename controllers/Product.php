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

	private static function header(){
		
	}
	public function index( $array )
	{
		list($region_id, $product_id, $ask, $reviews)=$array;
		
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
		$rewiews = Models\Reviews::first(array('select' => 'count(grade) c, sum(grade) s', 
								'conditions' => array('warecode = ?', $product_id)));
		
		$product_m->addChild("rating", $rewiews->s);
		$product_m->addChild("reviews_num", $rewiews->c);
		$description = Models\Description::first(array("warecode"=>$product_id));
		if($description)
			$description = $description->reviewtext;
		$product_m->addChild("description", StripTags($description));
		
		if(!$ask)
		{
			$options = $product_m->addChild("options");
			$options_m=Models\Oprionlist::all(array('warecode'=>$product_id));
			foreach ($options_m as $key => $val)
			{
				$option = $options->addChild("option");
				$option->addChild("name", ToUTF($val->prname));
				$option->addChild("value", ToUTF($val->prval));
			}
		}
		if($ask)
		{
			$ask = $product_m->addChild("ask");
			//$ask_m=array();
			$ask_m=Models\Link::getAccess($region_id, $product_id);
			//print_r($ask_m);
			foreach ($ask_m as $key => $val)
			{
				$prod = $ask->addChild("product");
				$prod->addChild("product_id", $val->warecode);
				$prod->addChild("title", ToUTF($val->ware));
				$description = Models\Description::first(array("warecode"=>$product_id));
				if($description)
					$description = $description->reviewtext;
				$prod->addChild("description", StripTags($description));
				$rewiews = Models\Reviews::first(array('select' => 'count(grade) c, sum(grade) s', 'conditions' => array('warecode = ?', $val->warecode)));
				//print_r($rewiews);
				$prod->addChild("rating", $rewiews->s);
				$prod->addChild("reviews_num", $rewiews->c);
				$prod->addChild("small_price", $val->inetprice);
				$prod->addChild("price", $val->price);
				$image = $prod->addChild("image", "http://www.mvideo.ru/Pdb/$val->warecode.jpg");
				$image->addAttribute("width", "180");
				$image->addAttribute("height", "180");
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