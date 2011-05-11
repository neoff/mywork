<?php
/**
  * Фасад длля работы с шаблонами
  * 
  * @package    InterfaceTemplate
  * @subpackage Template
  * @since      03.05.2011 9:43:15
  * @author     enesterov
  * @category   lib
  */

	namespace Controllers;
	use Models;
	use Template;
	
abstract class InterfaceTemplate extends Template\Template{
	
	/**
	 * текущий номер региона
	 * @var string
	 */
	protected $region_id;
	
	/**
	 * текущий номер категории
	 * @var int
	 */
	protected $category_id;
	
	/**
	 * текущая акция
	 * @var string
	 */
	protected $action_id;
	
	/**
	 * результатт поиска в БД
	 * @var obj
	 */
	protected $searches;
	
	/**
	 * модификатор для создания $dir_id
	 * @var int
	 */
	protected static $Mult = 1000000000;
	
	/**
	 * модификатор для создания $class_id
	 * @var int
	 */
	protected static $MultC = 100000;
	
	/**
	 * модификатор для создания $group_id
	 * @var int
	 */
	protected static $MultG = 1;
	
	/**
	 * текущая страница
	 * @var int
	 */
	protected $page;
	
	/**
	 * ID продукта
	 * @var unsigned long int
	 */
	protected $product_id;
	
	/**
	 * запрос аксессуаров
	 * @var bool
	 */
	protected $ask;
	
	/**
	 * апрос обзоров
	 * @var bool
	 */
	protected $reviews;
	
	/**
	 * массив с сайта mvideo массив всех  DIR
	 * @var array
	 */
	protected static $TmpDir = array();
	
	/**
	 * массив с сайта mvideo глобальных настроек
	 * @var array
	 */
	protected static $GlobalConfig = array();
	
	/**
	 * массив с сайта mvideo производителей товаров
	 * @var array
	 */
	protected static $Brands = array();
	
	/**
	 * массив с сайта mvideo используемых DIR
	 * @var array
	 */
	protected static $Dirs = array();
	
	/**
	 * массив с сайта mvideo используемых CLASS
	 * @var array
	 */
	protected static $Classes = array();
	
	/**
	 * массив с сайта mvideo используюемых GROUP
	 * @var array
	 */
	protected static $Groups = array();
	
	/**
	 * поле в массиве sdir
	 * @var stirng
	 */
	protected static $Sname = 'catalogname';
	
	/**
	 * массив продуктов в акции
	 * @var array
	 */
	protected $action_val = array();
	
	/**
	 * заглушка для мемкеша
	 * @var string
	 */
	protected $mkey;
	
	/**
	 * заглушка для мемкеша
	 * @var int
	 */
	protected $mtime;
	
	/**
	 * текущая федеральная акция
	 * @param obj $prod - контейнер в который выводить блоки
	 */
	protected function getActionFederal($prod)
	{
		$time = time();
		
		foreach (self::$GlobalConfig['fed_act'] as $key => $val) 
		{
			
			if($key < $time)
			{
				if($val['end_date'] >= $time)
				{
					$url = str_replace("/", "", $val['link']);//link
					$this->getActionsVal($url);
					$imgfile = "imgs/action/main/$url.jpg";
					
					$this->mkey = $url;
					$this->mtime = $val['end_date'];
					
					return $this->displayCategoryAction($prod, $val['link'], $val['name'], $imgfile);
				}
			}
		}
		
	}
	
	/**
	 * выводим на страницу информацию о акции и блок акции
	 * @param obj $prod - контейнер в который выводить блоки
	 * @param string $name
	 * @param string $description
	 * @param string $imgfile
	 */
	protected function displayCategoryAction($prod, $name, $description, $imgfile = "")
	{
		$url = str_replace("/", "", $name);//link
		$url_name = "http://www.mvideo.ru/".$url."-cond/";
		
		if(!$imgfile)
		{
			$imgfile = "imgs/action/header_$url.jpg";
			$url_name = "http://www.mvideo.ru/".str_replace("_", "-", $url)."/?ref=left_bat_".$name;
		}
		
		
		//$this->displayActionImage($imgfile);
		$this->displayImage($prod, "", "", false, $imgfile);
		
		$prod->addChild("action_id", $this->action_id);
		$prod->addChild("action_name", ToUTF($description));
		$prod->addChild("action_cond", $url_name);
		$prod->addChild("action_url", "http://www.mvideo.ru/".$url."/");
		
		return $url;
	}
	
	/**
	 * собирает товары участвующие в акции
	 * в массив $this->action_val
	 * @param string $name
	 */
	protected function getActionsVal($name)
	{
		
		$options = array('select' => 'w.warecode',
						'from' => 'segment_cache sc',
						'joins'=>" join warez_$this->region_id w on (sc.warecode=w.warecode)",
						'conditions' =>"sc.region_id=$this->region_id and sc.segment_name='$name' ");
		
		if($this->searches)
			$options['conditions'] .= $this->searches;
		$segment = Models\Segments::find('all', $options);
		foreach ($segment as $val)
		{
			$this->action_val[] = $val->warecode;
		}
		return False;
	}
	
	/**
	 * выводит на страницу блок описания товара
	 * @param obj $product - контейнер в который выводить блоки
	 * @param obj $val - значение Актив Рекорд
	 */
	protected function displayDescription($prod, $val)
	{
		$val->getDesctiptions();
		$prod->addChild("description", StripTags($val->description));
	}
	
	/**
	 * выводит блоки inet_price, old_price и price
	 * @param obj $prod - контейнер в который выводить блоки
	 * @param obj $val - значение Актив Рекорд
	 */
	protected function displayPrice($prod, $val)
	{
		$prod->addChild("inet_price", $val->inetprice);
		if($val->oldprice)
			$old_price = $val->oldprice;
		else
			$old_price = $val->price;
		$prod->addChild("old_price", $old_price);
		$prod->addChild("price", $val->price);
	}
	
	/**
	 * выводит на страницу блок забрать в магазине
	 * @param obj $product - контейнер в который выводить блоки
	 * @param obj $val - значение Актив Рекорд
	 */
	protected function displayPickup($product, $val)
	{
		$pickup = 0;
		if(!$this->action_val)
		{
			$pickup = Models\Shops::getPickup($this->region_id, $val);
		}
		$product->addChild("pickup", $pickup);
	}
	
	/**
	 * выводит на страницу блок доставки
	 * @param obj $product - контейнер в который выводить блоки
	 * @param obj $val - значение Актив Рекорд
	 */
	protected function displayDelivery($product, $val)
	{
		$delivery = Models\Segments::freeDelivery($val->warecode, $this->region_id, $val);
		$product->addChild("delivery", $delivery);
	}
	
	/**
	 * выводин блок рейтинга
	 * @param obj $prod - переменная блок в который надо выводить
	 * @param obj $val - Актив рекорд значение для выбора рейтинга
	 */
	protected function displayRating($prod, $val)
	{
		$val->getRatingRev();
		$prod->addChild("rating", $val->rating);
		$prod->addChild("reviews_num", $val->reviews);
	}
	
	/**
	 * выводит на страницу блок картинки
	 * в определенный контейнер <b>$prod</b>
	 * @param obj $prod - контейнер для вывода картинки
	 * @param int $code - имя картинки (warecode)
	 * @param string $small
	 * @param bool $main
	 * @param string $imgs - адрес картинки
	 */
	protected function displayImage($prod, $code, $small="", $size = array(), $imgs="")
	{
		$img = "http://www.mvideo.ru/Pdb$small/$code.jpg";
		if($imgs)
			$img = "http://www.mvideo.ru/$imgs";
		
		$temp = ""; 
		if(!$size)
		{
			
			$Headers  = get_headers($img);
			
			if($Headers[0]=='HTTP/1.1 200 OK')
			{
				$tmpsize = (int)substr($Headers[4], strlen('Content-Length: '));
				//$size = $Headers[4];
				//var_dump($size);
				if($tmpsize > 0)
				{
					$temp = getimagesize($img);
				}
			}
		}
		if(!$temp && !$imgs)
			$temp = array(90, 81, 0, 0);
		
		if($size)
		{
			$temp = $size;
			$a_size = array_count_values($size);
			if($a_size < 4)
				$temp = array_fill($a_size, 4-$a_size, 0);
		}
		
		if($temp)
		{
			
			list($width, $height, $type, $attr) = $temp;
			
			$image = $prod->addChild("image", $img);
			$image->addAttribute("width", $width);
			$image->addAttribute("height", $height);
			/*if($main)
				$image->addAttribute("main", "1");*/
		}
	}
	
	/**
	 * подключает сторонние файлы, задает статические переменные
	 */
	protected function includeFiles()
	{
		$GlobalConfig=array();
		$rfile = MVIDEO_PATH;
		
		$GlobalConfig['RegionID']=$this->region_id;
		require_once $rfile . '/lib/federal_info.lib.php';
		require_once $rfile . '/lib/sdirs.lib.php';
		require_once $rfile . '/www/classifier_'.$this->region_id.'.inc.php';
		
		self::$GlobalConfig = $GlobalConfig;
		self::$Brands = $Brands;
		self::$Dirs = $Dirs;
		self::$Classes = $Classes;
		self::$Groups = $Groups;
		
	}
	
	/**
	 * устанавливает основные переменные из $_GET запроса
	 */
	protected function setVar()
	{
		parent::setVar();
		$this->region_id = get_key('region_id', 0);
		$this->category_id = get_key('category_id', -1);
		$this->action_id = get_key('action', -1);
		$this->searches = get_key('search');
		$this->page = get_key('page', 0);
		$this->product_id = get_key('product_id');
		$this->ask = get_key('aks');
		$this->reviews = get_key('reviews');
	}
}