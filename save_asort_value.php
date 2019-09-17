<?php
header("Content-type: text/html; charset=utf-8"); 
require('../../../config.php');
require('../../../customer_id_decrypt.php'); //导入文件,获取customer_id_en[加密的customer_id]以及customer_id[已解密]
require('../../../back_init.php');
$link =mysql_connect(DB_HOST,DB_USER, DB_PWD);
mysql_select_db(DB_NAME) or die('Could not select database');

$id=$configutil->splash_new($_GET['id']);
$asort_value=$configutil->splash_new($_GET['val']);
 $result=null;
$sql="update weixin_commonshop_products set asort_value=".$asort_value." where id=".$id;
mysql_query($sql);
$error=mysql_error();
if($error!=""){
 echo "{\"code\":\"40004\",\"info\":\"失败\"}";
 return;
}else{
 echo "{\"code\":\"0\",\"info\":\"成功\"}";  
 return;	
}
	


?>