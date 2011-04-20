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
	private static $Mult = 1000000000;
	private static $MultC = 100000;
	private static $MultG = 1;
	private $region_id;
	private $product_id;
	private $ask = "";
	private $reviews = "";
	private $page = 0;
	
	
	public function index( $array )
	{
		//print_r($array);
		if($array)
		{
			$this->setVar();
		}
		//list($region_id, $product_id, $ask, $reviews, $page)=$array;
		
		$options = array("_warecode"=>$this->product_id);
		list($where, $array) = Models\Warez::SetParam($this->region_id, $options);
		$productes = Models\Warez::sql($this->region_id, $where, $array);
		if($productes)
		{
			$productes = $productes[0];
			
			
			$rfile = MVIDEO_PATH;
			require_once $rfile . '/www/classifier_'.$this->region_id.'.inc.php';
			$d = $productes->dirid*self::$Mult;
			$c = $productes->classid*self::$MultC;
			$g = $productes->grid*self::$MultG;
			$category->category_id = $d+$c+$g;
			$category->name = $Groups[$productes->dirid][$productes->classid][$productes->grid];
			$this->categories="";
			$this->categories->addChild("category_id", ($category)?$category->category_id:0);
			$this->categories->addChild("category_name", ($category)?ToUTF($category->name):"Список категорий");
			
			$this->product="";
			if($productes->inetqty>0)
				$this->product->addAttribute("inetSale", 1);
			
			$dic = $productes->getInetDiscountStatus($productes->warecode, $this->region_id);
			$this->product->addAttribute("card_discount", $dic);
			
			$this->product->addChild("product_id", $this->product_id);
			$this->product->addChild("region_id", $this->region_id);
			$this->product->addChild("title", StripTags($productes->ware));
			
			$imgs = $this->product->addChild("images");
			$img = $imgs->addChild("img", "http://www.mvideo.ru/Pdb/$this->product_id.jpg");
			$img->addAttribute("width", "180");
			$img->addAttribute("height", "180");
			$img->addAttribute("main", "1");
			
			$mov = $this->product->addChild("movies");
			//$mov->addChild("video", "http://www.mvideo.ru/Pdb/$this->product_id.jpg");
			
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
			
			//if(!$this->ask && !$reviews)
			//{
				$options = $this->product->addChild("options");
				$options_m=Models\Optionlist::all(array('warecode'=>$this->product_id));
				foreach ($options_m as $key => $val)
				{
					$option = $options->addChild("option");
					$option->addChild("name", StripTags($val->prname));
					$option->addChild("value", StripTags($val->prval));
				}
			//}
			if($this->ask)
			{
				$limit = true;
				if($this->ask==2)
					$limit = false;
				$ask = $this->product->addChild("aks");
				//$ask_m=array();
				
				$ask_m=Models\Link::getAccess($this->region_id, $this->product_id, $limit);
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
			if($this->reviews)
			{
				$reviews = $this->product->addChild("reviews");
				$reviews_m=Models\Reviews::all(array('warecode'=>$this->product_id, "approved"=>1));
				foreach ($reviews_m as $key => $val)
				{
					$review = $reviews->addChild("review");
					$date = "";
					if($val->add_date)
						$date = $val->add_date->format("d.m.Y");
					$review->addChild("date", $date);
					$review->addChild("author", ToUTF($val->name));
					$review->addChild("city",  ToUTF($val->city));
					$review->addChild("rating", $val->rating);
					$review->addChild("title",  StripTags($val->title));
					$review->addChild("text",  StripTags($val->text));
				}
			}
		}
	}
	
	/**
	 * устанавливает основные переменные из $_GET запроса
	 */
	private function setVar()
	{
		$this->region_id = get_key('region_id', 0);
		$this->product_id = get_key('product_id');
		$this->ask = get_key('aks');
		$this->reviews = get_key('reviews');
		$this->page = get_key('page', 0);
	}
}