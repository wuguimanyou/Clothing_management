<?php
header("Content-type: text/html; charset=utf-8"); 
require('../config.php');

$name =$configutil->splash_new($_POST["name"]);
$shop_id =$configutil->splash_new($_POST["shop_id"]);

//$email = $_POST["email"];
$email = "";
$need_customermessage = $configutil->splash_new($_POST["need_customermessage"]);
//$need_email = $_POST["need_email"];
$need_email = 0;
$issell = $configutil->splash_new($_POST["issell"]);

$sell_discount = $configutil->splash_new($_POST["sell_discount"]);
$reward_type = $configutil->splash_new($_POST["reward_type"]);
$init_reward = $configutil->splash_new($_POST["init_reward"]);

//echo $imgids;

$link = mysql_connect(DB_HOST,DB_USER,DB_PWD);
 mysql_select_db(DB_NAME) or die('Could not select database');

 if($shop_id>0){
    $sql="update weixin_commonshops set sell_discount=".$sell_discount.",reward_type=".$reward_type.",init_reward=".$init_reward.",name='".$name."',email='".$email."',need_customermessage=".$need_customermessage.",need_email=".$need_email.",issell=".$issell." where id=".$shop_id;
	//echo $sql;
	mysql_query($sql);
 }else{
    $sql="insert into weixin_commonshops(name,email,need_email,need_customermessage,customer_id,isvalid,createtime,issell,sell_discount,reward_type,init_reward)";
	$sql=$sql." values('".$name."','".$email."',".$need_email.",".$need_customermessage.",".$customer_id.",true,now(),".$issell.",".$sell_discount.",".$reward_type.",".$init_reward.")";
    mysql_query($sql);
 }
 
 $error =mysql_error();
 mysql_close($link);
 echo $error; 
 //echo "<script>location.href='base.php?customer_id=".$customer_id."';</script>"
?>