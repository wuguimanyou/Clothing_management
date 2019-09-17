<?php
header("Content-type: text/html; charset=utf-8"); 
require('../config.php');
require('../customer_id_decrypt.php'); //导入文件,获取customer_id_en[加密的customer_id]以及customer_id[已解密]
$link = mysql_connect(DB_HOST,DB_USER,DB_PWD);
mysql_select_db(DB_NAME) or die('Could not select database');
mysql_query("SET NAMES UTF8");
	
$callback = $configutil->splash_new($_GET["callback"]);
$batchcode =$configutil->splash_new($_GET["batchcode"]);
$address_id =$configutil->splash_new($_GET["address_id"]);
$order_username =$configutil->splash_new($_GET["order_username"]);
$order_userphone =$configutil->splash_new($_GET["order_userphone"]);
$order_address =$configutil->splash_new($_GET["order_address"]);
$location_p =$configutil->splash_new($_GET["location_p"]);
$location_c =$configutil->splash_new($_GET["location_c"]);
$location_a =$configutil->splash_new($_GET["location_a"]);

$query="select * from weixin_commonshop_order_addresses where batchcode=".$batchcode;
$result2 = mysql_query($query) or die('Query failed: ' . mysql_error());
$rcount_q2 = mysql_num_rows($result2);
if($rcount_q2==0){
	$sql_address = "insert into weixin_commonshop_order_addresses(batchcode,name,phone,address,location_p,location_c,location_a)values ('".$batchcode."','".$order_username."','".$order_userphone."','".$order_address."','".$location_p."','".$location_c."','".$location_a."')";
	mysql_query($sql_address);
}else{
	$sql="update weixin_commonshop_order_addresses set name='".$order_username."',phone='".$order_userphone."',address='".$order_address."',location_p='".$location_p."',location_c='".$location_c."',location_a='".$location_a."' where batchcode=".$batchcode;
	mysql_query($sql);	
}




$error =mysql_error();
mysql_close($link);
echo $callback."([{status:1}";
echo "]);";
echo $callback;

?>