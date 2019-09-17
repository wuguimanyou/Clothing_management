<?php
 header("Content-type: text/html; charset=utf-8"); 
require('../../../config.php');
require('../../../customer_id_decrypt.php'); //导入文件,获取customer_id_en[加密的customer_id]以及customer_id[已解密]
require('../../../back_init.php');
$link = mysql_connect(DB_HOST,DB_USER, DB_PWD);
mysql_select_db(DB_NAME) or die('Could not select database');
mysql_query("SET NAMES UTF8");
require('../../../proxy_info.php');

$isOpen_callback 	= trim($configutil->splash_new($_POST["isOpen_callback"]));	//是否开启零钱提现
$isOpen_alipay 		= trim($configutil->splash_new($_POST["isOpen_alipay"]));	//是否开启支付宝提现
$isOpen_wechat 		= trim($configutil->splash_new($_POST["isOpen_wechat"]));	//是否开启微信零钱提现
$isOpen_financial 	= trim($configutil->splash_new($_POST["isOpen_financial"]));	//是否开启财付通提现
$isOpen_bank 		= trim($configutil->splash_new($_POST["isOpen_bank"]));	//是否开启银行卡提现
$isOpen_agreement 	= trim($configutil->splash_new($_POST["isOpen_agreement"]));	//是否开启提现协议
$isOpen_massage 	= trim($configutil->splash_new($_POST["isOpen_massage"]));	//是否开启提现协议
$agreement_content 	= trim($configutil->splash_new($_POST["agreement_content"]));	//提现协议
$start_time 		= trim($configutil->splash_new($_POST["start_time"]));		//每月提现开始日期
$end_time 			= trim($configutil->splash_new($_POST["end_time"]));			//每月提现结束日期
$week_time 			= trim($configutil->splash_new($_POST["week_time"]));			//提现可设置按每周几提现
$mini_callback 		= trim($configutil->splash_new($_POST["mini_callback"]));		//最低提现金额
$max_callback 		= trim($configutil->splash_new($_POST["max_callback"]));		//不可提现金额
$full_vpscore 		= trim($configutil->splash_new($_POST["full_vpscore"]));		//提现vp值限制
$is_fee 			= trim($configutil->splash_new($_POST["is_fee_val"]));		//提现手续费0：不收，大于0则收千分之几手续费
$is_currency 		= trim($configutil->splash_new($_POST["is_curr_val"]));		//提现反购物币 0：不返 大于0则反千分之几
$remark 			= mysql_real_escape_string(trim($configutil->splash_new($_POST["remark"])));		//提现反购物币 0：不返 大于0则反千分之几
$id 				= -1;
//echo $customer_id;
// echo "isOpen_callback==".$isOpen_callback."start_time==".$start_time."end_time==".$end_time."week_time==".$week_time."mini_callback==".$mini_callback."mini_callback==".$mini_callback."max_callback==".$max_callback."full_vpscore==".$full_vpscore."is_fee==".$is_fee."is_currency==".$is_currency;

$query = "SELECT id FROM moneybag_rule where isvalid=true and customer_id=".$customer_id." LIMIT 1";
//echo $query;die;
$result= mysql_query($query);
while($row=mysql_fetch_object($result)){
	$id = $row->id;
}
if($id<0){
		$sql = "INSERT INTO moneybag_rule(isvalid,
										  customer_id,
										  isOpen_callback,
										  start_time,
										  end_time,
										  week_time,
										  mini_callback,
										  max_callback,
										  callback_currency,
										  callback_fee,
										  full_vpscore,
										  createtime,
										  remark,
										  isOpen_alipay,
										  isOpen_wechat,
										  isOpen_financial,
										  isOpen_bank,
										  isOpen_agreement,
										  agreement_content,
										  isOpen_massage
										  ) 
									VALUES(true,
										   $customer_id,
										   $isOpen_callback,
										   $start_time,
										   $end_time,
										   $week_time,
										   $mini_callback,
										   $max_callback,
										   $is_currency,
										   $is_fee,
										   $full_vpscore,
										   now(),
										   '$remark',
										   $isOpen_alipay,
										   $isOpen_wechat,
										   $isOpen_financial,
										   $isOpen_bank,
										   $isOpen_agreement,
										   '$agreement_content',
										   $isOpen_massage
										   )";
		//echo $sql;
		mysql_query($sql) or die('Query failed56: ' . mysql_error());  
		echo "<script>window.history.go(-1)</script>";
}else{
		$sql = "UPDATE moneybag_rule SET isOpen_callback=$isOpen_callback,
										 start_time=$start_time,
										 end_time=$end_time,
										 week_time=$week_time,
										 mini_callback=$mini_callback,
										 max_callback=$max_callback,
										 callback_currency=$is_currency,
										 callback_fee=$is_fee,
										 full_vpscore=$full_vpscore,
										 createtime=now(),
										 remark='$remark',
										 isOpen_alipay=$isOpen_alipay,
										 isOpen_wechat=$isOpen_wechat,
										 isOpen_financial=$isOpen_financial,
										 isOpen_bank=$isOpen_bank,
										 isOpen_agreement=$isOpen_agreement,
										 agreement_content='$agreement_content',
										 isOpen_massage=$isOpen_massage
									  WHERE customer_id=$customer_id";
		mysql_query($sql)or die('Query failed67: ' . mysql_error());
		echo "<script>window.history.go(-1)</script>";
}






?>