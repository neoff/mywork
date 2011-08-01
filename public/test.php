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
  <form method=POST action="/mobile/?polls=feedback">
  *<input type=text name="name">
  *<input type=text name="email">
  *<input type=text name="shop_id">
  *<input type=text name="region_id">
  *<input type=text name="order_date">
  <select name=sales_rating>
  <option>1</option>
  <option>2</option>
  <option>3</option>
  </select>
  <textarea rows="" cols="" name=sales_comment></textarea>
  <select name=cashier_rating>
  <option>1</option>
  <option>2</option>
  <option>3</option>
  </select>
  <textarea rows="" cols="" name=cashier_comment></textarea>
  <select name=inspector_rating>
  <option>1</option>
  <option>2</option>
  <option>3</option>
  </select>
  <textarea rows="" cols="" name=inspector_comment></textarea>
  <select name=recommend>
  <option>1</option>
  <option>2</option>
  </select>
  <input type=submit>
  
  </form>
	phpinfo();