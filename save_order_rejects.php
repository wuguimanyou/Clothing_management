<?php
header("Content-type: text/html; charset=utf-8"); 
require('../config.php');
require('../customer_id_decrypt.php'); //导入文件,获取customer_id_en[加密的customer_id]以及customer_id[已解密]
	$link = mysql_connect(DB_HOST,DB_USER,DB_PWD);
mysql_select_db(DB_NAME) or die('Could not select database');
	
$callback = $configutil->splash_new($_GET["callback"]);
$batchcode =$configutil->splash_new($_GET["batchcode"]);
$reject =$configutil->splash_new($_GET["reject"]);

$sql="insert into weixin_commonshop_order_rejects(batchcode,remark,createtime) values('".$batchcode."',".$reject.",now())";
mysql_query($sql);

$error =mysql_error();
mysql_close($link);
echo $callback."([{status:1}";
echo "]);";
echo $callback;

?>