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
	use Template;
	

class ControllerProduct extends Template\Template{
	
	public function index( $array )
	{
		//print_r($array);
		list($region_id, $product_id, $ask, $reviews)=$array;
		
		$options = array("_warecode"=>$product_id);
		list($where, $array) = Models\Warez::SetParam($region_id, $options);
		$productes = Models\Warez::sql($region_id, $where, $array);
		$productes = $productes[0];
		
		//$a=Models\Desclist::all(array('warecode'=>$product_id));
		//print_r($a);
		
		$this->product="";
		$this->product->addChild("product_id", $product_id);
		$this->product->addChild("region_id", $region_id);
		$this->product->addChild("title", ToUTF($productes->name));
		$this->product->addChild("small_price", $productes->small_price);
		$this->product->addChild("price", $productes->price);
//		$rewiews = Models\Reviews::first(array('select' => 'count(rating) c, sum(rating) s', 
//								'conditions' => array('warecode = ?', $product_id)));
//		
		$productes->getRatingRev();
		$this->product->addChild("rating", $productes->rating);
		$this->product->addChild("reviews_num", $productes->reviews);
//		$description = Models\Description::first(array("warecode"=>$product_id));
//		if($description)
//			$description = $description->reviewtext;
		$productes->getDesctiptions();
		$this->product->addChild("description", StripTags($productes->description));
		
		if(!$ask && !$reviews)
		{
			$options = $this->product->addChild("options");
			$options_m=Models\Optionlist::all(array('warecode'=>$product_id));
			foreach ($options_m as $key => $val)
			{
				$option = $options->addChild("option");
				$option->addChild("name", ToUTF($val->prname));
				$option->addChild("value", ToUTF($val->prval));
			}
		}
		if($ask)
		{
			$ask = $this->product->addChild("ask");
			//$ask_m=array();
			$ask_m=Models\Link::getAccess($region_id, $product_id);
			//print_r($ask_m);
			foreach ($ask_m as $key => $val)
			{
				$prod = $ask->addChild("product");
				$prod->addChild("product_id", $val->warecode);
				$prod->addChild("title", ToUTF($val->ware));
//				$description = Models\Description::first(array("warecode"=>$product_id));
//				if($description)
//					$description = $description->reviewtext;
				$val->getDesctiptions();
				$prod->addChild("description", StripTags($val->description));
//				$rewiews = Models\Reviews::first(array('select' => 'count(rating) c, sum(rating) s', 'conditions' => array('warecode = ?', $val->warecode)));
				//print_r($rewiews);
				$val->getRatingRev();
				$prod->addChild("rating", $val->rating);
				$prod->addChild("reviews_num", $val->reviews);
				$prod->addChild("small_price", $val->inetprice);
				$prod->addChild("price", $val->price);
				$image = $prod->addChild("image", "http://www.mvideo.ru/Pdb/$val->warecode.jpg");
				$image->addAttribute("width", "180");
				$image->addAttribute("height", "180");
			}
		}
		if($reviews)
		{
			$reviews = $this->product->addChild("reviews");
			$reviews_m=Models\Reviews::all(array('warecode'=>$product_id));
			foreach ($reviews_m as $key => $val)
			{
				$review = $reviews->addChild("review");
				$review->addChild("date", $val->add_date->format('Y-m-d'));
				$review->addChild("author", ToUTF($val->name));
				$review->addChild("city",  ToUTF($val->city));
				$review->addChild("rating", $val->rating);
				$review->addChild("title",  StripTags($val->title));
				$review->addChild("text",  StripTags($val->text));
			}
		}
	}
}