<?php
header("Content-type: text/html; charset=utf-8"); 
require('../config.php');
require('../customer_id_decrypt.php'); //导入文件,获取customer_id_en[加密的customer_id]以及customer_id[已解密]
$link = mysql_connect(DB_HOST,DB_USER,DB_PWD);
mysql_select_db(DB_NAME) or die('Could not select database');

$batchcode=0;

$batchcode =$configutil->splash_new($_POST["batchcode"]);
$merchant_remark =$configutil->splash_new($_POST["content"]);


$sql="update weixin_commonshop_orders set merchant_remark= '".$merchant_remark."' where isvalid=true and batchcode=".$batchcode." and customer_id=".$customer_id;
//echo $sql;return;
$result = mysql_query($sql) or die('Query failed: ' . mysql_error());  


 mysql_close($link);
  //echo $result;
if($result==1){
	$status=1;
}else{
	$status=0;	
}
echo $status;  

?>