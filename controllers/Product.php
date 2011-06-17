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
	

class ControllerProduct extends InterfaceTemplate{
	
	
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
			
			$this->displayCategory($category);
			$this->displayProduct($productes);
			$this->displayImage($this->product, $this->product_id);
			$this->displayImages($this->product, $this->product_id);
			$this->displayMove();
			$this->displayPrice($this->product, $productes);
			$this->displayPickup($this->product, $productes);
			$this->displayDelivery($this->product, $productes);
			$this->displayRating($this->product, $productes);
			$this->GetPdo($this->product, $productes);
			$this->GetService($this->product, $productes);
			$this->getProducts($this->product, $productes);
			$this->displayDescription($this->product, $productes);
			$this->displayOptions();
			
			if($this->ask)
				$this->displayAks();
				
			if($this->reviews)
				return $this->displayReviews();
		}
	}
	
	/**
	 * выводим на экран блок categories
	 * @param obj $category
	 */
	private function displayCategory($category)
	{
		$this->categories="";
		$this->categories->addChild("category_id", ($category)?$category->category_id:0);
		$this->categories->addChild("category_name", ($category)?ToUTF($category->name):"Список категорий");
	}
	
	private function displayProduct( $productes )
	{
		$this->product="";
		$this->product->addAttribute("inetSale", ($productes->inetqty>0)?1:0);
		$discount = $productes->getInetDiscountStatus($productes->warecode, $this->region_id);
		$this->product->addAttribute("card_discount", $discount);
		
		$this->product->addChild("product_id", $this->product_id);
		$this->product->addChild("region_id", $this->region_id);
		$this->product->addChild("title", StripTags($productes->ware));
	}
	
	
	private function displayImages($prod, $code)
	{
		$imgs = $prod->addChild("images");
		$oprions = array('select' => "DISTINCT filename", 'conditions' => 'warecode='.$code, 'order' => 'filename' );
		$pdb = Models\Pdb::find('all', $oprions);
		if($pdb)
		{
			$this->displayImage($imgs, $code."b");
			foreach ($pdb as $value) 
			{
				$this->displayImage($imgs, $code."b".$value->filename);
			}
			
		}
	}
	
	private function displayMove()
	{
		$mov = $this->product->addChild("movies");
		//$mov->addChild("video", "http://www.mvideo.ru/Pdb/$this->product_id.jpg");
	}
	
	
	
	
	
	
	private function getPdo($prod, $val)
	{
		$pdo = Models\Warez::getCertificate($this->region_id, $val->warecode, $val->price);
		if($pdo)
			return $this->displayPdo($prod, $pdo);
	}
	
	private function displayPdo($prod, $val)
	{
		$pdo = $prod->addChild("pdo");
		foreach ($val as $value) 
		{
			$opions = $pdo->addChild("option");
			$opions->addAttribute("id", $value->warecode);
			$opions->addChild("name", StripTags($value->ware));
			$opions->addChild("value", $value->certprice);
		}
	}
	
	private function getService($prod, $val)
	{
		$cert = Models\Ikupons::getKupon($this->region_id, $val->mark, $val->dirid, $val->classid, $val->grid);
		if($cert)
			return $this->displayService($prod, $cert);
	}
	
	private function displayService($prod, $val)
	{
		$pdo = $prod->addChild("service");
		foreach ($val as $value) 
		{
			$opions = $pdo->addChild("option");
			$opions->addAttribute("id", $value->ikupon_warecode);
			$opions->addChild("name", StripTags($value->fullname));
			$opions->addChild("type", StripTags($value->ikupon_type));
			$opions->addChild("value", $value->price);
		}
	}
	
	private function getProducts($prod, $val)
	{
		$product = Models\Warez::getReleted($this->region_id, $val);
		//var_dump($product);
		if($product)
			return $this->displayProducts($prod, $product);
	}
	
	private function displayProducts($prod, $val)
	{
		$related = $prod->addChild("similar_products");
		foreach ($val as $value) 
		{
			$rprod = $related->addChild("product");
			$rprod->addChild("product_id", $value->warecode);
			$rprod->addChild("product_name", StripTags($value->fullname));
			$rprod->addChild("description", StripTags($value->descr));
			$rprod->addChild("price", $value->price);
			$this->displayImage($rprod, $value->warecode, "_small/65");
		}
	}
	

	
	private function displayOptions()
	{
		$options = $this->product->addChild("options");
		$options_m=Models\Optionlist::all(array('warecode'=>$this->product_id));
		foreach ($options_m as $key => $val)
		{
			$option = $options->addChild("option");
			$option->addChild("name", StripTags($val->prname));
			$option->addChild("value", StripTags($val->prval));
		}
	}
	
	private function displayAks()
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
			
			$this->displayPrice($prod, $prod);
			
			$this->displayRating($prod, $val);
			
			$this->displayDescription($prod, $val);
			$this->displayImage($prod, $val->warecode);
		}
	}
	
	
	/**
	 * выводит на екран отзывы пользователей
	 * обрабатывает пост запрос на добавление нового отзыва
	 */
	private function displayReviews()
	{
		global $_POST;
		
		if($_POST)
			if($this->getReviews($_POST))
				return false;
		$reviews = $this->product->addChild("reviews");
		$reviews_m=Models\Reviews::all(array('warecode'=>$this->product_id, "approved"=>1));
		foreach ($reviews_m as $key => $val)
		{
			$review = $reviews->addChild("review");
			$date = "";
			if($val->add_date)
				$date = $val->add_date->format("c");
			$review->addChild("date", $date);
			$review->addChild("author", ToUTF($val->name));
			$review->addChild("city",  ToUTF($val->city));
			$review->addChild("rating", $val->rating);
			$review->addChild("title",  StripTags($val->title));
			$review->addChild("text",  StripTags($val->text));
		}
		
		return true;
	}
	
	/**
	 * собирает валидатор, проверяет пост запрос, записывает в базу данные
	 * @param array $post
	 */
	private function getReviews(&$post)
	{
		$reviews = new Models\Reviews();
		$required = (object)array('required' => true, 'type' => 'string');
		$validate = array('name' => $required, 
						'email' => $required, 
						'city' => $required, 
						'title' => $required, 
						'rating' => $required,
						'title' => $required,
						'text' => $required);
		if(!validatePost($validate, $post))
			return true;
			
		$this->createDatabaseObject($reviews, $validate, $post);
		$reviews->save();
		return false;
	}
	
	/**
	 * собирает данные из пост запроса для записи в базу
	 * @param obj $obj
	 * @param obj $validate
	 * @param array $post
	 */
	private function createDatabaseObject(&$obj, $validate, $post)
	{
		foreach ($validate as $key => $value)
		{
			$obj->$key = $post[$key];
		}
	}
	
	
}