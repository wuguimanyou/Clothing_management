<?php
header("Content-type: text/html; charset=utf-8"); 
require('../config.php');
$link = mysql_connect(DB_HOST,DB_USER,DB_PWD);
mysql_select_db(DB_NAME) or die('Could not select database');


$payother_desc_id = $configutil->splash_new($_GET["payother_desc_id"]);
$pay_username 	  = $configutil->splash_new($_GET["pay_username"]);
$batchcode 		  = $configutil->splash_new($_GET["batchcode"]);
$note 			  = $configutil->splash_new($_GET["note"]);

$callback 		  = $configutil->splash_new($_GET["callback"]);

$status      = 0;
$paystatus   = 0;
$query="select paystatus,status from weixin_commonshop_orders where batchcode=".$batchcode." limit 1";
$result = mysql_query($query) or die('Query failed: ' . mysql_error());
while ($row = mysql_fetch_object($result)) {
   $status    = $row->status;
   $paystatus = $row->paystatus;
}

if($status<0){

	echo $callback."([{status:-2}";
	echo "]);";
	echo $callback;
	return;

}


$pay_user_id = -1;
$query="select pay_user_id from weixin_commonshop_otherpay_descs where id=".$payother_desc_id." limit 1";
$result = mysql_query($query) or die('Query failed: ' . mysql_error());
while ($row = mysql_fetch_object($result)) {
   $pay_user_id = $row->pay_user_id;
}
if($pay_user_id>0 or $paystatus>0){
	echo $callback."([{status:-1}";
	echo "]);";
	echo $callback;
    return;
}

$currtime	   = time();	//当前时间
$recovery_time = '';		//支付失效时间
$query_rt = "select recovery_time from weixin_commonshop_order_prices where isvalid=true and batchcode='".$batchcode."'";
$result_rt = mysql_query($query_rt) or die('query_rt failed:'.mysql_error());
while($row_rt = mysql_fetch_object($result_rt)){
	$recovery_time = $row_rt->recovery_time;
}

if(strtotime($recovery_time)<$currtime){
	echo $callback."([{status:-3}";
	echo "]);";
	echo $callback;
    return;
}
$sql="update weixin_commonshop_otherpay_descs set pay_username='".$pay_username."',note='".$note."' where id=".$payother_desc_id; 
mysql_query($sql);
 //echo $error;
 mysql_close($link);
  
 
echo $callback."([{status:1}";
echo "]);";
echo $callback;
 

?>