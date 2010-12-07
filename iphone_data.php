<?php

/**

 * ��������� ���������

 * 

 * 1 - �������

 * 2 - offers - ����������� �����

 * 3 - discounts - ����� � ������

 * 4 - gifts for him

 * 5 - gifts for her

 * 6 - gifts for family

 * 7 - gifts for children

 * 8 - wareinfo + ids

 */

list ($dev_id, $dev_name) = split(",", isset($_GET['device']) ? $_GET['device'] : ',');
if ($appdb = new PDO('sqlite:/www/sites/newmvideo/data/iphone_app.sqlite'))
	$appdb->Query("insert into app_stats values(datetime('now'), '{$_SERVER['REMOTE_ADDR']}', '{$dev_id}', '{$dev_name}')");

error_log("".date("Y/m/d H:i:s")."\t".$_SERVER['REMOTE_ADDR']."\t".$_GET['device']."\n", 3, '/tmp/iphone_data_device.log');

if (isset($_GET['token'])) {
	error_log("".date("Y/m/d H:i:s")."\t".$_SERVER['REMOTE_ADDR']."\t".$_GET['device']."\t".$_GET['token']."\n", 3, '/tmp/token.log');
	if ($appdb = new PDO('sqlite:/www/sites/newmvideo/data/iphone_app.sqlite'))
		$appdb->Query("insert into notification values(datetime('now'), '{$_SERVER['REMOTE_ADDR']}', '{$dev_id}', '{$dev_name}', '{$_GET[token]}')");
	exit;
}

// ������� , ��� ��� xml





// ����������� � �����

$db = new DB_Mvideo;



// ������ �� ��������� - 1

$GlobalConfig["RegionID"] = 1;

if (isset($_GET['region'])) {

  $GlobalConfig["RegionID"] = (int)$_GET['region'];

}



// ��������� �� ��������� - 1

$category = 1;

if (isset($_GET['category'])) {

  $category = (int)$_GET['category'];

}

if (isset($_GET['ids'])) {

  $ids = (int)$_GET['ids'];

}



// �������� �������� �� ���������

switch($category) {

  case 1:

   $tpl->assign("type","novelty");
    
    $ids = array();
     
	/**
   * �������
   */
  $query = "
	  SELECT warecode
	  FROM segment_cache
	  WHERE segment_name='novelty'
	  AND region_id=".$GlobalConfig['RegionID']."
	  ORDER BY promo_hour DESC,important DESC
  ";
  $db->query($query);
	if (mysql_num_rows($db->Query_ID) > 0) {
		while ($row = mysql_fetch_assoc($db->Query_ID)) {
          $ids[] = $row['warecode'];
        }
      }
      
    $params = array("ids" => $ids,"homeshop" => ($GlobalConfig["RegionID"]==1?1:0));

    break;

        

  case 2:

    $tpl->assign("type","offers");

    //$params = array("BestPrice" => 2, "homeshop" => ($GlobalConfig["RegionID"]==1?1:0));
    $params = array("Hit" => 1, "homeshop" => ($GlobalConfig["RegionID"]==1?1:0));

    break;

        

  case 3:

    $tpl->assign("type","discounts");

    

    if ($GlobalConfig["RegionID"] == 1) {

    	

      $ids = array();

      $sql = "

        SELECT warecode

        FROM t_buy_now

        WHERE start_date <= now()

        AND end_date >= now()

      ";

      $db->query($sql);

      if (mysql_num_rows($db->Query_ID) > 0) {

        while ($row = mysql_fetch_assoc($db->Query_ID)) {

          $ids[] = $row['warecode'];

        }

      }



      $params = array("ids" => $ids, "rows" => 10);



    } else {

    	

      $params = array("BestPrice" => 1, "rows" => 10);

        

    }

    break;

        

  case 4:

  	$tpl->assign("type","gifts_for_him");

    $params = array("top_gifts"=>1,"gift_theme"=>1,"rows"=>30,"order_b"=>4);
    $params = array("gift_theme"=>1,"rows"=>10,"order_b"=>4);

    break;

      	

  case 5:

  	$tpl->assign("type","gifts_for_her");

    $params = array("top_gifts"=>1,"gift_theme"=>2,"rows"=>30,"order_b"=>4);
    $params = array("gift_theme"=>2,"rows"=>10,"order_b"=>4);

    break;

      	

  case 6:

    $tpl->assign("type","gifts_for_family");

    $params = array("top_gifts"=>1,"gift_theme"=>3,"rows"=>30,"order_b"=>4);
    $params = array("gift_theme"=>3,"rows"=>10,"order_b"=>4);

    break;

      	

  case 7:

  	$tpl->assign("type","gifts_for_children");

    $params = array("top_gifts"=>1,"gift_theme"=>4,"rows"=>30,"order_b"=>4);
    $params = array("gift_theme"=>4,"rows"=>10,"order_b"=>4);

    break;
    
    
    case 8:
    	
    $tpl->assign("type","novelty");
    
    
    

    $params = array("ids" => (int)$ids);

    break;

    

  default:

  	header("HTTP/1.0 404 Not Found", true, 404);

	exit();

  	break;

}

		$GlobalConfig['cur_action']['descr'] = str_replace('<','',$GlobalConfig['cur_action']['descr']);
		$GlobalConfig['cur_action']['descr'] = str_replace('>','',$GlobalConfig['cur_action']['descr']);

		
		
		



$itemsList = Find(0,$params,"shortlist&props");

//print_R(Find(0,$params,"shortlist&props","stringsql"));



// ���� ���-�� ���� �������

if (is_array($itemsList) && count($itemsList)) {

	

  $warecodes = array_keys($itemsList);



  // previewText - 3 �����

  $sql = "

    SELECT *

    FROM new_options

    WHERE warecode in (".join(",",$warecodes).")

    ORDER BY sort

    LIMIT 3

  ";

  $db->query($sql);

  if (mysql_num_rows($db->Query_ID) > 0) {

    while ($row = mysql_fetch_assoc($db->Query_ID)) {

      if ($row['title']) {

        $itemsList[$row['warecode']]['options'][] = $row;

      }

    }

  }

  

  // detailText - ���-�� + �� ��������

  foreach($itemsList as $k =>$val) {

  	

    if (is_array($val['props']) && count($val['props'])) {

    	

      foreach($val['props'] as $k1 => $prop) {

        if (strpos($prop['PrVal'], '�����')) {

          unset($itemsList[$k]['props'][$k1]);

        }



        $prop['PrName'] = str_replace('&nbsp;',' ', $prop['PrName']);
        $prop['PrName'] = str_replace('&laquo;','"', $prop['PrName']);
        $prop['PrName'] = str_replace('&raquo;','"', $prop['PrName']);
        $prop['PrName'] = str_replace('&','and', $prop['PrName']);
    $prop['FullName'] = str_replace('&nbsp;', ' ',$prop['FullName']);
    $prop['FullName'] = str_replace('&laquo', '"',$prop['FullName']);
    $prop['FullName'] = str_replace('&raquo', '"',$prop['FullName']);
    $prop['FullName'] = str_replace('&', 'and',$prop['FullName']);

        $itemsList[$k]['props'][$k1]['PrName'] = strip_tags($prop['PrName']);

      }

    }



    $itemsList[$k]['FullName'] = str_replace('&nbsp;',' ', $itemsList[$k]['FullName']);
    $itemsList[$k]['FullName'] = str_replace('&laquo;','"', $itemsList[$k]['FullName']);
    $itemsList[$k]['FullName'] = str_replace('&raquo;','"', $itemsList[$k]['FullName']);
    $itemsList[$k]['FullName'] = str_replace('&','and', $itemsList[$k]['FullName']);
    if ($GlobalConfig['cur_action']['priceTextType'] && $_GET['category'] == 2) {

      $itemsList[$k]['priceTextVal'] = $val[$GlobalConfig['cur_action']['priceTextType']];

    }

//    $cat[$val['DirID'] . ':' . $val['ClassID']] += 1;
    $categories[$val['DirID']] += 1;

  }

  if ($categories) {
	$res = $db->query('SELECT * FROM dirs WHERE DirID IN ('.join(",",array_keys($categories)).')');
	while ($row = @mysql_fetch_assoc($db->Query_ID)) {
		$categories[$row['DirID']] = array( 'DirName' => $row['DirName'], 'count' => $categories[$row['DirID']]);
	}
  }



  // ����������� ������� ��������

  $sql = "

    SELECT *

    FROM warereviews_new

    WHERE warecode in (".join(",",$warecodes).")

  ";

  $res = $db->query($sql);

  while ($row=@mysql_fetch_assoc($db->Query_ID)) {



    $s1 = str_replace(array('<br>','<br/>'), '\n',$row['ReviewText']);

    $s1 = str_replace('&nbsp;', ' ',$s1);
    $s1 = str_replace('&laquo;', '"',$s1);
    $s1 = str_replace('&raquo', '"',$s1);
    $s1 = str_replace('&', 'and',$s1);
    $row['FullName'] = str_replace('&nbsp;', ' ',$row['FullName']);
    $row['FullName'] = str_replace('&laquo;', '"',$row['FullName']);
    $row['FullName'] = str_replace('&raquo;', '"',$row['FullName']);
    $row['FullName'] = str_replace('&', 'and',$row['FullName']);

    $s1 = html_entity_decode($s1);

    $itemsList[$row['warecode']]['ReviewText'] = strip_tags($s1);



  }

}


header ("Content-type: text/xml");



// ������ �����



if (!$catalog)
	$tpl->assign("itemsList",$itemsList);
else
	$tpl->assign("categories",$categories);

$tpl->assign("RegionID",$GlobalConfig["RegionID"]);

$tpl->assign("RegionHost",$GlobalConfig['RegionDomains'][$GlobalConfig["RegionID"]]);

$tpl->display("cache/iphone_data.xml.tpl");



?>
