<?php
header("Content-type: text/html; charset=utf-8"); 
require('../config.php');
require('../customer_id_decrypt.php'); //导入文件,获取customer_id_en[加密的customer_id]以及customer_id[已解密]
$link = mysql_connect(DB_HOST,DB_USER,DB_PWD);
mysql_select_db(DB_NAME) or die('Could not select database');

require('../common/utility_shop.php');
	
$callback = $configutil->splash_new($_GET["callback"]);
$customer_id = $configutil->splash_new($_GET["customer_id"]);

$op =$configutil->splash_new($_GET["op"]);
if($op==1){
	$order_remind =$configutil->splash_new($_GET["order_remind"]);
		$query="update weixin_commonshop_orderremind set order_remind=".$order_remind." where customer_id=".$customer_id;
		$result = mysql_query($query) or die('Query failed1: ' . mysql_error());
	
	$error =mysql_error();
	mysql_close($link);
	if($order_remind==1){
		echo $callback."([{status:1}";
	}else{
		echo $callback."([{status:0}";
	}
	
	echo "]);";
	echo $callback;
	
}else if($op==2){
	
	$order_remind=-1;
	$query="select order_remind from weixin_commonshop_orderremind where isvalid=true and customer_id=".$customer_id;
	$result = mysql_query($query) or die('Query failed2: ' . mysql_error());
	while ($row = mysql_fetch_object($result)) {
		$order_remind = $row->order_remind;
	}
	
	$error =mysql_error();
	if($order_remind>0){
		mysql_close($link);
		echo $callback."([{status:1}";
		echo "]);";
		echo $callback;
	}else{
		mysql_close($link);
		echo $callback."([{status:0}";
		echo "]);";
		echo $callback;
	}
}else if($op==3){
	$query="select count(1) as ordercount from weixin_commonshop_orders where customer_id=".$customer_id." and isvalid=true";
	$result=mysql_query($query) or die('Query failed3: '.mysql_error());
	while ($row = mysql_fetch_object($result)) {
		$ordercount= $row->ordercount;
		break;
	}
	
	$query="select last_record from weixin_commonshop_orderremind where isvalid=true and customer_id=".$customer_id;
	$result = mysql_query($query) or die('Query failed4: ' . mysql_error());
	while ($row = mysql_fetch_object($result)) {
		$last_record = $row->last_record;
	}
	$count=$ordercount-$last_record;
	mysql_close($link);
	echo $callback."([{status:1,count:".$count."}";
	echo "]);";
	echo $callback;
}




?>