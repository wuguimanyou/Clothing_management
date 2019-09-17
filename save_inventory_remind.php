<?php
header("Content-type: text/html; charset=utf-8"); 
require('../../config.php');
require('../../customer_id_decrypt.php'); //导入文件,获取customer_id_en[加密的customer_id]以及customer_id[已解密]
require('../../back_init.php');
$link = mysql_connect(DB_HOST,DB_USER,DB_PWD); 
mysql_select_db(DB_NAME) or die('Could not select database');
/* 参数获取 */
$shop_id =$configutil->splash_new($_POST["shop_id"]);
$is_promoter =$configutil->splash_new($_POST["is_promoter"]);
$pinformation_id = -1;
if(!empty($_POST["pinformation_id"])){
	$pinformation_id = $configutil->splash_new($_POST["pinformation_id"]);
}
$is_stockOut = 0;
if(!empty($_POST["is_stockOut"])){
	$is_stockOut     = $configutil->splash_new($_POST["is_stockOut"]);
}

$stock_remind=0; 
if(!empty($_POST["stock_remind"])){ 
   $stock_remind = $configutil->splash_new($_POST["stock_remind"]);
} 
$is_Pinformation=0; 
if(!empty($_POST["is_Pinformation"])){ 
   $is_Pinformation = $configutil->splash_new($_POST["is_Pinformation"]);
} 
$is_division=0; 
if(!empty($_POST["is_division"])){ 
   $is_division=$_POST["is_division"]; 
} 

$now = date("Y-m-d h:i:s",time());

if($shop_id>0){
	$sql="update weixin_commonshops set 
		stock_remind=".$stock_remind."
		where id=".$shop_id;	   
	//echo $sql; return;
	mysql_query($sql)or die('W_update Query failed: '.mysql_error()); 
 }else{
	$sql="insert into weixin_commonshops(stock_remind)";
	$sql=$sql." values(".$stock_remind.")";
    //echo $sql;
	mysql_query($sql);	
} 
if($pinformation_id>0){
	$query="update weixin_commonshops_extend set 
		is_stockOut=".$is_stockOut.",
		is_Pinformation=".$is_Pinformation.",
		is_promoter=".$is_promoter.",
		is_division=".$is_division."
		where shop_id=".$shop_id."
		and customer_id=".$customer_id;	   
	mysql_query($query)or die('E_update Query failed: '.mysql_error()); 
 }else{
	$query="insert into weixin_commonshops_extend(is_Pinformation,shop_id,customer_id,createtime,isvalid,is_stockOut,is_division,is_promoter)";
	$query=$query." values(".$is_Pinformation.",".$shop_id.",".$customer_id.",'".$now."',true,".$is_stockOut.",".$is_division.",".$is_promoter.")";
	mysql_query($query);
} 

mysql_close($link);
echo "<script>location.href='base_inventory_remind.php?customer_id=".$customer_id_en."';</script>"
?>