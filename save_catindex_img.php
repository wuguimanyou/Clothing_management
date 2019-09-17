<?php
//首页分类显示图
header("Content-type: text/html; charset=utf-8"); 
require('../config.php');
require('../customer_id_decrypt.php'); //导入文件,获取customer_id_en[加密的customer_id]以及customer_id[已解密]
require('../back_init.php');
require('product_type_utlity.php');
require('../common/utility_4m.php');
//$index_catnum=-1;
$name =$configutil->splash_new($_POST["name"]);
$keyid =$configutil->splash_new($_POST["keyid"]);

$parent_id = $configutil->splash_new($_POST["parent_id"]);
$sendstyle=$configutil->splash_new($_POST["sendstyle"]);
$type_imgurl=$configutil->splash_new($_POST["type_imgurl"]);
//$index_catnum=$configutil->splash_new($_POST["index_catnum"]);
$adminuser_id = $configutil->splash_new($_GET["adminuser_id"]);
$orgin_adminuser_id = $configutil->splash_new($_GET["orgin_adminuser_id"]);
$owner_general = $configutil->splash_new($_GET["owner_general"]);
//echo "+++type_imgurl==".$type_imgurl;
$f = fopen('out_savetype.txt', 'w'); 
$link = mysql_connect(DB_HOST,DB_USER,DB_PWD);
 mysql_select_db(DB_NAME) or die('Could not select database');
 $customer_ids="";

		
mysql_query("update weixin_commonshop_types set cat_index_imgurl='".$type_imgurl."'where id=".$keyid);
	
 $error =mysql_error();
 mysql_close($link);
 echo $error; 
 echo "<script>location.href='defaultset.php?default_set=1&customer_id=".$customer_id_en."';</script>"
?>