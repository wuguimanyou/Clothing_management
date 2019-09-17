<?php
header("Content-type: text/html; charset=utf-8"); 
require('../../../config.php');
require('../../../customer_id_decrypt.php'); //导入文件,获取customer_id_en[加密的customer_id]以及customer_id[已解密]
require('../../../back_init.php');
date_default_timezone_set(PRC);
$link = mysql_connect(DB_HOST,DB_USER,DB_PWD);
mysql_select_db(DB_NAME) or die('Could not select database');

require('../../../common/utility_shop.php');
	
$callback = $configutil->splash_new($_GET["callback"]);
$keyid =$configutil->splash_new($_GET["keyid"]);
$status =$configutil->splash_new($_GET["status"]);
$pagenum =$configutil->splash_new($_GET["pagenum"]);
$user_id =$configutil->splash_new($_GET["user_id"]);
$reason = "";
if(!empty($_GET["reason"])){
	$reason =$configutil->splash_new($_GET["reason"]);
}
$money = 0;
if(!empty($_GET["money"])){
	$money =$configutil->splash_new($_GET["money"]);
}
$serial_number = "";
if(!empty($_GET["serial_number"])){
	$serial_number =$configutil->splash_new($_GET["serial_number"]);
}
$batchcode = "";
if(!empty($_GET["batchcode"])){
	$batchcode =$configutil->splash_new($_GET["batchcode"]);
}

if($status==2){		//确认提现
	  $sql="update weixin_commonshop_withdrawals set status=".$status.",remark='',serial_number='".$serial_number."',confirmtime='".date( "Y-m-d H:i:s")."' where id=".$keyid;
	  mysql_query($sql);
}else if($status==3){	//驳回提现
	$sql="update weixin_commonshop_withdrawals set status=".$status.",remark='".$reason."',confirmtime='".date( "Y-m-d H:i:s")."' where id=".$keyid;
	mysql_query($sql); 

	//购买支付后,扣除提现金额 start
	$query2="select agent_getmoney from promoters where status=1 and isvalid=true and user_id=".$user_id;	//查找代理商代理入账总金额
	$agent_getmoney = 0;
	$result2 = mysql_query($query2) or die('Query failed: ' . mysql_error());
	while ($row2 = mysql_fetch_object($result2)) {
	$agent_getmoney = $row2->agent_getmoney;
	}
	$agent_getmoney = $agent_getmoney + $money;	//驳回后加回扣除的金额
	$sql1 = "insert into weixin_commonshop_agentfee_records(user_id,batchcode,price,detail,type,isvalid,createtime,after_getmoney,withdrawal_id) values(".$user_id.",'".$batchcode."',".$money.",'提现申请驳回',2,true,now(),".$agent_getmoney.",".$keyid.")";
	mysql_query($sql1);		//插入加回扣除成本价
	$sql = "update promoters set agent_getmoney=".$agent_getmoney." where user_id=".$user_id;
	mysql_query($sql);
	//购买支付后,扣除提现金额 end
				
}

$error =mysql_error();
mysql_close($link);
echo $callback."([{keyid:".$keyid.",serial_number:'".$serial_number."',remark:'".$reason."',confirmtime:'".date( "Y-m-d H:i:s")."'}";
echo "]);";
echo $callback;
?>