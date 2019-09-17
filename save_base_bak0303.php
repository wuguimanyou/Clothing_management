<?php
header("Content-type: text/html; charset=utf-8"); 
require('../config.php');

$name =$configutil->splash_new($_POST["name"]);
$shop_id =$configutil->splash_new($_POST["shop_id"]);

//$email = $_POST["email"];
$email = "";
$need_customermessage = $configutil->splash_new($_POST["need_customermessage"]);
$need_online = $configutil->splash_new($_POST["need_online"]);
$detail_template_type = $configutil->splash_new($_POST["detail_template_type"]);
$introduce = $configutil->splash_new($_POST["introduce"]);

$nopostage_money = $configutil->splash_new($_POST["nopostage_money"]);

$member_template_type = 1;
if(!empty($_POST["member_template_type"])){
   $member_template_type=$_POST["member_template_type"];
}

$is_showbottom_menu = $configutil->splash_new($_POST["is_showbottom_menu"]);
$is_showdiscuss = $configutil->splash_new($_POST["is_showdiscuss"]);

$auto_confirmtime = 0;
if(!empty($_POST["auto_confirmtime"])){
   $auto_confirmtime = $configutil->splash_new($_POST["auto_confirmtime"]);
}

$is_showshare_info = 0;
if(!empty($_POST["is_showshare_info"])){
   $is_showshare_info = $configutil->splash_new($_POST["is_showshare_info"]);
}
$per_share_score =0;
if(!empty($_POST["per_share_score"])){
   $per_share_score = $configutil->splash_new($_POST["per_share_score"]);
}
$reward_level =3;
if(!empty($_POST["reward_level"])){
   $reward_level = $configutil->splash_new($_POST["reward_level"]);
}

$need_online = $configutil->splash_new($_POST["need_online"]);

$online_type = 1;
if(!empty($_POST["online_type"])){
   $online_type = $configutil->splash_new($_POST["online_type"]);
}

$sell_detail = "";
if(!empty($_POST["sell_detail"])){
   $sell_detail = $configutil->splash_new($_POST["sell_detail"]);
}

$exp_name = "推广员";
if(!empty($_POST["exp_name"])){
   $exp_name = $configutil->splash_new($_POST["exp_name"]);
}
$exp_mem_name = "推广员";
if(!empty($_POST["exp_mem_name"])){
   $exp_mem_name = $configutil->splash_new($_POST["exp_mem_name"]);
}
$distr_type = 1;
if(!empty($_POST["distr_type"])){
   $distr_type = $_POST["distr_type"];
}
$online_qq = $configutil->splash_new($_POST["online_qq"]);

$is_autoupgrade = 0;
if(!empty($_POST["is_autoupgrade"])){
  $is_autoupgrade = $_POST["is_autoupgrade"];
}
$auto_upgrade_money = 0;
if(!empty($_POST["auto_upgrade_money"])){
  $auto_upgrade_money = $_POST["auto_upgrade_money"];
}

$auto_upgrade_money_2 = 0;
if(!empty($_POST["auto_upgrade_money_2"])){
  $auto_upgrade_money_2 = $_POST["auto_upgrade_money_2"];
}

$shop_card_id = -1;
if(!empty($_POST["shop_card_id"])){
  $shop_card_id = $_POST["shop_card_id"];
}


$is_attent = 0;
$attent_url="";

//$need_email = $_POST["need_email"];
$need_email = 0;
if(!empty($_POST["issell"])){
	$issell = $configutil->splash_new($_POST["issell"]);

	$sell_discount = $configutil->splash_new($_POST["sell_discount"]);
	$reward_type = $configutil->splash_new($_POST["reward_type"]);
	$init_reward = $configutil->splash_new($_POST["init_reward"]);

	$init_reward_1= $configutil->splash_new($_POST["init_reward_1"]);
	if(empty($init_reward_1)){
	   $init_reward_1 =0;
	}
	$init_reward_2 = $configutil->splash_new($_POST["init_reward_2"]);
	if(empty($init_reward_2)){
	   $init_reward_2 =0;
	}
	$init_reward_3 = $configutil->splash_new($_POST["init_reward_3"]);
	if(empty($init_reward_3)){
	   $init_reward_3 =0;
	}
}else{
    $issell = 0;
	$sell_discount = 0;
	$reward_type = 1;
	$init_reward = 0;
	$init_reward_1 = 0;
	$init_reward_2 = 0;
	$init_reward_3 =0;
}
$isprint = 0;
if(!empty($_POST["isprint"])){
   $isprint = $configutil->splash_new($_POST["isprint"]);
}
if($isprint==='on'){
	$isprint=1;
}else{
	$isprint=0;
}

//点击关注链接
$gz_url = "";
if(!empty($_POST["gz_url"])){
   $gz_url = $configutil->splash_new($_POST["gz_url"]);
}

$link = mysql_connect(DB_HOST,DB_USER,DB_PWD);
 mysql_select_db(DB_NAME) or die('Could not select database');

 $f = fopen('out.txt', 'w');  

 if($shop_id>0){
    $sql="update weixin_commonshops set reward_level=".$reward_level.",exp_name='".$exp_name."',nopostage_money=".$nopostage_money.",auto_upgrade_money_2=".$auto_upgrade_money_2.", introduce='".$introduce."',per_share_score=".$per_share_score.",is_showshare_info=".$is_showshare_info.",is_showdiscuss=".$is_showdiscuss.",shop_card_id = ".$shop_card_id.",is_autoupgrade=".$is_autoupgrade.",auto_upgrade_money=".$auto_upgrade_money.",is_showbottom_menu=".$is_showbottom_menu.",distr_type=".$distr_type.",member_template_type=".$member_template_type.",is_attent=".$is_attent.",attent_url='".$attent_url."',auto_confirmtime=".$auto_confirmtime.",online_type=".$online_type.",sell_detail='".$sell_detail."',detail_template_type=".$detail_template_type.",online_qq='".$online_qq."',need_online=".$need_online.",sell_discount=".$sell_discount.",reward_type=".$reward_type.",init_reward=".$init_reward.",name='".$name."',email='".$email."',need_customermessage=".$need_customermessage.",need_email=".$need_email.",issell=".$issell.",isprint=".$isprint.",gz_url='".$gz_url."',exp_mem_name='".$exp_mem_name."' where id=".$shop_id;
	fwrite($f, "====sql===".$sql."\r\n");  
	mysql_query($sql);
 }else{
    $sql="insert into weixin_commonshops(name,email,need_email,need_customermessage,need_online,customer_id,isvalid,createtime,issell,sell_discount,reward_type,init_reward,online_type,online_qq,isprint,detail_template_type,sell_detail,auto_confirmtime,is_attent,attent_url,member_template_type,distr_type,is_showbottom_menu,is_autoupgrade,auto_upgrade_money,shop_card_id,is_showdiscuss,is_showshare_info,per_share_score,introduce,gz_url,auto_upgrade_money_2,nopostage_money,exp_name,reward_level,exp_mem_name)";
	$sql=$sql." values('".$name."','".$email."',".$need_email.",".$need_customermessage.",".$need_online.",".$customer_id.",true,now(),".$issell.",".$sell_discount.",".$reward_type.",".$init_reward.",".$online_type.",'".$online_qq."','".$isprint."',".$detail_template_type.",'".$sell_detail."',".$auto_confirmtime.",".$is_attent.",'".$attent_url."',".$member_template_type.",".$distr_type.",".$is_showbottom_menu.",".$is_autoupgrade.",".$auto_upgrade_money.",".$shop_card_id.",".$is_showdiscuss.",".$is_showshare_info.",".$per_share_score.",'".$introduce."','".$gz_url."',".$auto_upgrade_money_2.",".$nopostage_money.",'".$exp_name."',".$reward_level.",'".$exp_mem_name."')";
    mysql_query($sql);
 }
 $error = mysql_error();
 fwrite($f, "====error===".$error."\r\n"); 
fclose($f); 
 $query="select id from weixin_commonshop_commisions where isvalid=true and customer_id=".$customer_id;
 $result = mysql_query($query) or die('Query failed: ' . mysql_error());
 $commision_id=-1;
  while ($row = mysql_fetch_object($result)) {
     $commision_id = $row->id;
 }
 if($commision_id>0){
     $sql="update weixin_commonshop_commisions set init_reward_1=".$init_reward_1.",init_reward_2=".$init_reward_2.",init_reward_3=".$init_reward_3." where id=".$commision_id;
	 mysql_query($sql);
 }else{
     $sql="insert into weixin_commonshop_commisions(init_reward_1,init_reward_2,init_reward_3,customer_id,isvalid,createtime) values(".$init_reward_1.",".$init_reward_2.",".$init_reward_3.",".$customer_id.",true,now())"; 
	// echo $sql;
	 mysql_query($sql);
 }
 
 if($issell){
    //开启二维码分销
	 $query="select id from qr_baseinfos where isvalid=true and customer_id=".$customer_id;
	 $result = mysql_query($query) or die('Query failed: ' . mysql_error());
	 $qr_baseinfo_id=-1;
	  while ($row = mysql_fetch_object($result)) {
	     $qr_baseinfo_id= $row->id;
	  }
	if($qr_baseinfo_id>0){
		$sql="update qr_baseinfos set ison=1,type=1 where id=".$qr_baseinfo_id;
		mysql_query($sql);
	}else{
	    $sql="insert into qr_baseinfos(customer_id,type,ison,isvalid,createtime) values(".$customer_id.",1,1,true,now())";
		mysql_query($sql);
	}
	//echo  $sql;
 }
 $error =mysql_error();
 mysql_close($link);
//echo $error; 
  echo "<script>location.href='base.php?customer_id=".$customer_id."';</script>"
?>