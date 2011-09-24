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
	private $warez = array();
	private $warez_id_rror;
	private $shops_id_error;
	private $order;
	private $order_info;
	private $tmp_warez;
	private $error;
	private $shops;
	private $shop_name = array();
	private $order_id;
	private $out = array();
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
			//var_dump($this->order_info);
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
				$order->addChild("order_shop_address", StripTags($this->shop_name[$value->order->shop_id]));
			}
		}

	}


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
			$this->shop_name[$sids->shop_id] = $sids->address;

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
		//$out = $client->createOrder(new SoapVar($this->order, SOAP_ENC_OBJECT));
	}
}