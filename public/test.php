<?php
/**
  *
  *
  * @package    personal
  * @subpackage
  * @since      01.08.2011 10:28:50
  * @author     enesterov
  * @category   controller
  *
  */
  ?>

  name=TestName&mail=test@test.ru&city=N&rating=3&title=TestTitle&text=TestTest
  <form method=POST action="/mobile/?cart=1&region_id=6">
  <input type="hidden" name="cart" value=1>
  name - <input type=text name="name" value=1><br>
  phone - <input type=text name="phone" value=1><br>
  product_count - <input type=text name="ids[11032137]" value=1><br>
  shops - <input type=text name="shops[11032137]" value=1><br>
  kupon (podves) - <input type=text name="kupon[11032137][807076]" ><br>
  cert (pdo2) - <input type=text name="cert[11032137][289935]" ><br>

  <input type=submit>

  </form>
	phpinfo();http://spb.mvideo.ru/homeshop/?p=cart&ids[11032137]=1&pickup=618&ref=select_store_product