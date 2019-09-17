<?php
header("Content-type: text/html; charset=utf-8"); 
require('../../../config.php');
require('../../../customer_id_decrypt.php'); //导入文件,获取customer_id_en[加密的customer_id]以及customer_id[已解密]
require('../../../back_init.php');
$link = mysql_connect(DB_HOST,DB_USER,DB_PWD);
mysql_select_db(DB_NAME) or die('Could not select database');
require('../../../proxy_info.php');
require('../../../common/utility_shop.php');
mysql_query("SET NAMES UTF8");

$shop = new shopMessage_Utlity();

$user_id 		= -1;
$customer_id 	= $configutil->splash_new($_POST["customer_id"]);
$customer_id 	= passport_decrypt((string)$customer_id);
$batchcode  	= $configutil->splash_new($_POST["batchcode"]);
$getmoney 		= 0;
$percentage		= 0;
$surplus_type	= 0;

$query = "SELECT getmoney,user_id,percentage,surplus_type FROM weixin_cash_being_log WHERE isvalid=true AND status = 0 AND batchcode = $batchcode LIMIT 1";
$result= mysql_query($query) or die('Query failed 18: ' . mysql_error());
while( $row = mysql_fetch_object($result) ){
	$getmoney 	= $row->getmoney;
	$user_id 	= $row->user_id;
	$surplus_type = $row->surplus_type;
	$percentage	= $row->percentage;
}

if($percentage > 0 && $surplus_type > 0){
	$sub_money = $getmoney*$percentage/1000;
	$sub_money = round($sub_money);				//计算后返的购物币
	//查是否推广员，是才会返购物币
	$pid   = -1;
	$query = "SELECT id FROM promoters WHERE isvalid=true AND status = 1 AND user_id = $user_id LIMIT 1";
	$result= mysql_query($query) or die('Query failed 32: ' . mysql_error());
	while( $row = mysql_fetch_object($result) ){
		$pid = $row->id;
	}
	$weixin_fromuser = '';
	$query = "SELECT weixin_fromuser FROM weixin_users WHERE isvalid=true AND id=$user_id LIMIT 1";
	$result= mysql_query($query) or die('Query failed 42: ' . mysql_error());
	while( $row = mysql_fetch_object($result) ){
		$weixin_fromuser = $row->weixin_fromuser;
	}
	$custom = "购物币";
	$query = "SELECT custom FROM weixin_commonshop_currency WHERE isvalid = true AND customer_id = $customer_id LIMIT 1";
	//echo $query;
	$result= mysql_query($query) or die('Query failed 48: ' . mysql_error());
	while( $row = mysql_fetch_object($result) ){
		$custom = $row->custom;
	}
	if($pid > 0){

		$id = -1;
		$currency = 0;
		$query = "SELECT id,currency FROM weixin_commonshop_user_currency WHERE isvalid=true AND user_id=$user_id LIMIT 1";
		$result= mysql_query($query) or die('Query failed 57: ' . mysql_error());
		while( $row = mysql_fetch_object($result)){
			$id 		= $row->id;
			$currency 	= $row->currency;
		}
		$after_currency = $currency+$sub_money;
		//如果无购物币则创建购物币
		if( $id < 0 ){
			$sql = "INSERT INTO weixin_commonshop_user_currency(isvalid,customer_id,user_id,currency,createtime) VALUES(true,$customer_id,$user_id,$sub_money,now())";

		}else{
			$sql = "UPDATE weixin_commonshop_user_currency SET currency = currency+$sub_money WHERE isvalid=true AND user_id=$user_id";
		}
		$remark = "提现返【".$sub_money."】".$custom;
		$sql2 = "INSERT INTO weixin_commonshop_currency_log(isvalid,customer_id,user_id,cost_money,cost_currency,after_currency,batchcode,status,type,class,remark,createtime) VALUES(true,$customer_id,$user_id,$sub_money,$sub_money,$after_currency,'$batchcode',2,1,3,'$remark',now())";

		mysql_query($sql) or die('Query failed 61: ' . mysql_error());
		mysql_query($sql2) or die('Query failed 62: ' . mysql_error());
		$msg_content =  "亲，您获得了".$sub_money."$custom\r\n".
						"来源：【零钱提现】\n".
						"时间：<".date( "Y-m-d H:i:s").">\n";
		$shop->SendMessage($msg_content,$weixin_fromuser,$customer_id);

	}

	
	
}


$query = "UPDATE weixin_cash_being_log SET status = 1 WHERE isvalid = true AND user_id = $user_id AND batchcode = $batchcode";
mysql_query($query) or die('Query failed 29: ' . mysql_error());


$json = array();
$json['status'] = 401;
$json['msg']	= "打款状态更改成功";
echo json_encode($json);



?> 