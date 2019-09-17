<?php
header("Content-type: text/html; charset=utf-8"); 
require('../config.php'); //配置
require('../customer_id_decrypt.php'); //导入文件,获取customer_id_en[加密的customer_id]以及customer_id[已解密]
$link = mysql_connect(DB_HOST,DB_USER,DB_PWD);
mysql_select_db(DB_NAME) or die('Could not select database');
mysql_query("SET NAMES UTF8");

$user_id   = -1;
$batchcode = '';
$pay_desc  = '';
if(!empty($_POST['user_id'])){
	$user_id = $configutil->splash_new($_POST['user_id']);
}
if(!empty($_POST['batchcode'])){
	$batchcode = $configutil->splash_new($_POST['batchcode']);
}
if(!empty($_POST['pay_desc'])){
	$pay_desc = $configutil->splash_new($_POST['pay_desc']);
}

$sql = "insert into weixin_commonshop_otherpay_descs(user_id,batchcode,pay_desc,isvalid,createtime) values(".$user_id.",'".$batchcode."','".$pay_desc."',true,now())";
mysql_query($sql) or die('otherpay sql failed'.mysql_error());
$payother_desc_id = mysql_insert_id();

echo json_encode($payother_desc_id);
?>