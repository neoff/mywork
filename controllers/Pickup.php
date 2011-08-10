<?php
/**
 * выбор магазина для пикапа
 * 
 * @package     Pickup
 * @subpackage  Controller
 * @since       20.06.2011 11:55:53
 * @author      enesterov
 * @category    controller
 *
 */

	namespace Controllers;
	use Models;
	use Template;

class ControllerPickup extends InterfaceTemplate{
	
	
	public function index($get)
	{
		global $GlobalConfig;
		
		//$rfile = MVIDEO_PATH;
		//require_once $rfile . '/lib/ware.class.php';
		$this->setVar();
		$this->product_id = get_key('pickup_product_id');
		//$this->region_id = get_key('region_id', 1);
		
		$options = array("_warecode"=>$this->product_id);
		list($where, $array) = Models\Warez::SetParam($this->region_id, $options);
		$productes = Models\Warez::sql($this->region_id, $where, $array);
		$pickup = false;
		
		if($productes)
		{
			$productes = $productes[0];
			$pickup = Models\Shops::getPickupShops($this->region_id, $productes);
		}
		
		if($pickup)
		{
			$this->xml->addChild("product_id", $this->product_id);
			$this->pickup_shops="";
			foreach ($pickup as $val)
			{
				//$shop = $this->pickup_shops->addChild("shop");
				$shop = $this->displayShops($this->pickup_shops, $val);
				//var_dump($shop->shop_id);
				$shop->shop_id = $this->shopFormula($shop->shop_id);
			}
		}
	}
	private function shopFormula($id)
	{
		return 1000 - ($id * 2);
	}
}