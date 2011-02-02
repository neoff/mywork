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
		list($region_id, $product_id, $ask, $reviews, $page)=$array;
		
		$options = array("_warecode"=>$product_id);
		list($where, $array) = Models\Warez::SetParam($region_id, $options);
		$productes = Models\Warez::sql($region_id, $where, $array);
		if($productes)
		{
			$productes = $productes[0];
			
			//print_r($productes);
			$options = array("dirid"=>$productes->dirid, "classid"=>$productes->classid, "grid"=>$productes->grid);
			$category = Models\Category::find('fist', $options);
	
			$this->categories="";
			$this->categories->addChild("category_id", ($category)?$category->category_id:0);
			$this->categories->addChild("category_name", ($category)?ToUTF($category->name):"Список категорий");
			
			$this->product="";
			$this->product->addChild("product_id", $product_id);
			$this->product->addChild("region_id", $region_id);
			$this->product->addChild("title", StripTags($productes->ware));
			
			$imgs = $this->product->addChild("images");
			$img = $imgs->addChild("img", "http://www.mvideo.ru/Pdb/$product_id.jpg");
			$img->addAttribute("width", "180");
			$img->addAttribute("height", "180");
			$img->addAttribute("main", "1");
			
			$mov = $this->product->addChild("movies");
			//$mov->addChild("video", "http://www.mvideo.ru/Pdb/$product_id.jpg");
			
			$this->product->addChild("inet_price", $productes->inetprice);
			if($productes->oldprice)
				$old_price = $productes->oldprice;
			else
				$old_price = $productes->price;
			$this->product->addChild("old_price", $old_price);
			$this->product->addChild("price", $productes->price);
	
			$productes->getRatingRev();
			$this->product->addChild("rating", $productes->rating);
			$this->product->addChild("reviews_num", $productes->reviews);
	
			$productes->getDesctiptions();
			$this->product->addChild("description", StripTags($productes->description));
			
			//if(!$ask && !$reviews)
			//{
				$options = $this->product->addChild("options");
				$options_m=Models\Optionlist::all(array('warecode'=>$product_id));
				foreach ($options_m as $key => $val)
				{
					$option = $options->addChild("option");
					$option->addChild("name", StripTags($val->prname));
					$option->addChild("value", StripTags($val->prval));
				}
			//}
			if($ask)
			{
				$limit = true;
				if($ask==2)
					$limit = false;
				$ask = $this->product->addChild("aks");
				//$ask_m=array();
				
				$ask_m=Models\Link::getAccess($region_id, $product_id, $limit);
				//print_r($ask_m);
				$group = "";
				foreach ($ask_m as $key => $val)
				{
					if($group != $val->grid)
					{
						$group = $val->grid;
						$groups_m=Models\Groups::find('fist', array("grid"=>$val->grid));
						$gr = $ask->addChild("group");
						$gr->addAttribute("id", $val->grid);
						$gr->addAttribute("title", StripTags($groups_m->grname));
					}
					$prod = $gr->addChild("product");
					$prod->addChild("product_id", $val->warecode);
					$prod->addChild("title", StripTags($val->ware));
					
					$prod->addChild("inet_price", $val->inetprice);
					
					if($val->oldprice)
						$old_price = $val->oldprice;
					else
						$old_price = $val->price;
					$prod->addChild("old_price", $old_price);
					
					$prod->addChild("price", $val->price);
					
					$val->getRatingRev();
					$prod->addChild("rating", $val->rating);
					$prod->addChild("reviews_num", $val->reviews);
					
					$val->getDesctiptions();
					$prod->addChild("description", StripTags($val->description));
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
					$review->addChild("date", "aaa");
					$review->addChild("author", ToUTF($val->name));
					$review->addChild("city",  ToUTF($val->city));
					$review->addChild("rating", $val->rating);
					$review->addChild("title",  StripTags($val->title));
					$review->addChild("text",  StripTags($val->text));
				}
			}
		}
	}
}