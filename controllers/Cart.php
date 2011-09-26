<?php
/**
 * корзина пользователя пикап
 *
 * @package    Cart
 * @subpackage controllers
 * @since      24.09.2011 15:25:43
 * @author     enesterov
 * @category   controller
 */
	namespace Controllers;
	use Models\Shops;

	use Models;
	use Template;


class ControllerCart extends InterfaceTemplate{
	/**
	 * список товаров для заказа
	 * @var array
	 */
	private $warez = array();
	
	/**
	 * номер товара с которым произошла ошибка во время заказа
	 * @var int
	 */
	private $warez_id_rror;
	
	/**
	 * номер магазина с которым произошла ошибка во время заказа
	 * @var int
	 */
	private $shops_id_error;
	
	/**
	 * SOAP объект заказа
	 * @var obj
	 */
	private $order;
	
	/**
	 * содержимое заказа
	 * @var obj
	 */
	private $order_info;
	
	/**
	 * объект содержит информацию о товаре
	 * @var obj
	 */
	private $tmp_warez;
	
	/**
	 * описание ошибки
	 * @var string
	 */
	private $error;
	
	/**
	 * массив магазинов выбранных для заказа
	 * @var array
	 */
	private $shops;
	
	/**
	 * адреса магазинов в заказе
	 * @var array
	 */
	private $shop_address = array();
	
	/**
	 * массив для вывода на экран
	 * @var array
	 */
	private $out = array();
	
	/**
	 * основнфя точка входа, вызывает метода валидации передаваемых данных
	 * @param GET $array
	 * @return метод выводящий страницу на экран
	 */
	public function index($array)
	{
		ini_set("soap.wsdl_cache_enabled", "0");
		header('Content-type: text/html; charset=utf-8');
		if($array)
		{
			$this->setVar();
		}
		if(isset($_POST))
		{
			//смотрим клиента, собираем инфу о нем
			if($this->makeOrderInfo($_POST))
			{
				//смотрим товары на сайте мвидео
				if($this->getWarez($_POST))
				{
					//собираем магазины
					if($this->makeShops($_POST))
					{
						//делаем заказ в магазине
						$this->makeOrders();
					}
				}
			}
		}
		
		return $this->displayOutput();
	}
	
	/**
	 * собирает данные о пользователе
	 * формирует данные о заказе
	 * @param POST $array
	 * @return bool
	 */
	private function makeOrderInfo($array)
	{
		if(!isset($array['name']) || !isset($array['phone']))
		{
			$this->error = "Некорректно заполнены поля";
			return false;
		}
		
		$mail = "";
		if(isset($array['mail']))
			$mail = $array['mail'];
		
		$this->order = new \stdClass();
		$this->order->user_id = 'c16b61d8bc55d8792b8e60215fa4ca26';
		$this->order->fio	= $array['name'];
		$this->order->phone	= "+7".$array['phone'];
		$this->order->email	= $mail;
		$this->order->wares = array();
		
		return true;
		#(int)$array['shop_id'];
	}
	
	/**
	 * получает информацию о товаре, проверяет наличие товара на сайте М.Видео
	 * @param POST $array
	 * @return boolean
	 */
	private function getWarez($array)
	{
		$this->error = "Не выбрано ниодного товара";
		
		if(isset($array['ids']) && is_array($array['ids']))
			$this->error = false;
		
		if($this->error)
			return false;
		
		$client = new \SoapClient("http://www.mvideo.ru/soap/mvideo.wsdl",array("trace"=>1,"exceptions"=>0));
		
		try
		{
			$result = $client->GetWareInfo(implode(", ", array_keys($array['ids'])), $this->region_id);
		}
		catch (SoapFault $e)
		{
			$this->error = $e->faultstring;
			return false;
		}
		
		if (is_soap_fault($result))
		{
			
			$this->error = $result->faultstring;
			return false;
		}
		
		foreach($result->WareInfoList AS $ware)
		{
			//$ware->ware = mb_convert_encoding($ware->ware,'utf-8','cp1251');
			$ware->qty = $array['ids'][$ware->warecode];
			$this->warez[$ware->warecode] = $ware;
			if(!isset($array['shops'][$ware->warecode]))
			{
				$this->error = "Не выбран магазин";
				$this->warez_id_rror = $ware->warecode;
				return false;
			}
		}
		
		return true;
	}
	
	/**
	 * выводит xml страницу с результатом или с ошибкой
	 */
	private function displayOutput()
	{
		$error = !empty($this->error);
		$this->orders = "";
		$this->orders->addChild("error_code", $error);
		$this->orders->addChild("error_string", $this->error);
		if($error)
		{
			if($this->warez_id_rror)
			{
				$error_warez = $this->orders->addChild("error_warez");
				$error_warez->addChild("warez_id", $this->warez_id_rror);
			}
			if($this->shops_id_error)
			{
				$error_shops = $this->orders->addChild("error_shop");
				$error_shops->addChild("shop_id", $this->shops_id_error);
			}
			//обработка заказов
			/*if($this->order_info)
			{
				$order_error = $this->order->addChild("error_order");
				$order_error->addChild("error_order", );
			}*/
			//
		}

		if(!empty($this->out))
		{
			$order = $this->orders->addChild("order");
			foreach ($this->out as $value)
			{
				
				$order->addChild("order_id", $value->responseText);
				$order->addChild("order_date", date("c"));
				$order->addChild("order_shop_address", StripTags($this->shop_address[$value->order->shop_id]));
			}
		}

	}
	
	/**
	 * "раскладывает товары по магазинам"
	 * объеденяет товары выбранные в одинаховых магазинах
	 * @param POST $array
	 * @return bool
	 */
	private function makeShops($array)
	{
		$this->error = "Не выбран магазин";

		if(isset($array['shops']))
			$this->error = false;
		
		foreach ($array['shops'] as $key => $value)
		{
			//$this->order->shop_id = (int)$value;
			/*$sids = Shops::find('first', array('select'=>'shop_id, address','conditions'=>array('p=?', $value)));
			if(!$sids)
			{
				$this->error = "Магазин не найден в базе данных";
				return false;
			}
			$sid = $sids->shop_id;
			$this->shop_name[$sid] = $sids->address;*/
			
			if(!isset($this->warez[$key]))
			{
				$this->error = "Товар не найден в базе данных";
				return false;
			}
			$this->shops[$value] = $this->warez[$key];
			
		}
		return true;
	}
	
	/**
	 * формирует объект для заказа
	 * @return bool
	 */
	private function makeOrders()
	{
		foreach ($this->shops as $key => $value)
		{
			$sids = Shops::find('first', array('select'=>'shop_id, address','conditions'=>array('p=?', $key)));
			if(!$sids)
			{
				$this->error = "Магазин не найден в базе данных";
				$this->warez_id_rror = $value->warecode;
				$this->shops_id_error = $key;
				return false;
			}
			$this->shop_address[$sids->shop_id] = $sids->address;
			
			$this->order->shop_id = (string)$sids->shop_id;
			$this->order->wares[] = $value;
			$this->order_info = $this->order;
			$out = $this->pickupProxy();
			if(!$out)
			{
				if(!$this->error)
					$this->error = "Не возможно заказать в магазине";
				$this->warez_id_rror = $value->warecode;
				$this->shops_id_error = $key;
				return false;
			}
			$out->order = $this->order;
			$this->out[] = $out;
		}
		return true;
	}
	
	/**
	 * отправляет заказ на сервер Pick-up
	 * @return bool
	 */
	private function pickupProxy()
	{
		
		$client = new \SoapClient('http://192.168.1.225/ws/pickup.wsdl',array('trace' => true));
		try
		{
			$obj_soap = new \SoapVar($this->order, SOAP_ENC_OBJECT);
			$out = $client->createOrder($obj_soap);
		}
		catch (Exception $e)
		{
			$this->error = 'Ошибка при создании заказа: '.$e->faultstring;
			return false;
		}
		
		if ($out->responseCode > 0)
		{
			$this->error= 'Ошибка при создании заказа: '.$out->responseText;
			return false;
		}
		
		return $out;
	}
}