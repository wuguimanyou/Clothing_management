<?php

//V8.0
//exit();   
//停止V8.0之前的代码

header("Content-type: text/html; charset=utf-8"); 
require('../config.php');
require('../customer_id_decrypt.php'); //导入文件,获取customer_id_en[加密的customer_id]以及customer_id[已解密]

$link = mysql_connect(DB_HOST,DB_USER,DB_PWD);
mysql_select_db(DB_NAME) or die('Could not select database');
mysql_query("SET NAMES UTF8");
require('../proxy_info.php');

$batchcode = $configutil -> splash_new($_POST["batchcode"]);
$query_stat = "select sendstatus from package_order_t where  isvalid=true and customer_id= ".$customer_id." and batchcode='".$batchcode."'";
$result_stat = mysql_query($query_stat) or die("Query_stat error : ".mysql_error());
$sendstatus = 0;//收货状态 0:未发货；1：已发货;2:已收货;
while ($row_stat = mysql_fetch_object($result_stat)) {
	$sendstatus = $row_stat -> sendstatus;
}
if($sendstatus != 2 ){
	//更新支付状态
	$query_pay= "update package_order_t set sendstatus=2,confirm_receivetime=now() where isvalid=true and batchcode='".$batchcode."' and customer_id=".$customer_id;
	mysql_query($query_pay);
	$json["status"] = 0;		
	$json["msg"]    = "订单编号：".$batchcode."，确认收货成功";	
}else{
	$json["status"] = 0;		
	$json["msg"]    = "订单编号：".$batchcode."，已确认收货，请勿重复";						
}
$error =mysql_error();
if(!empty($error)){
	$json["status"] = 10002;
	$json["msg"] = $error;	
}

mysql_close($link);

$jsons=json_encode($json);
die($jsons);	
?>