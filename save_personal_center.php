<?php
header("Content-type: text/html; charset=utf-8"); 
require('../../../../config.php');
require('../../../../customer_id_decrypt.php'); //导入文件,获取customer_id_en[加密的customer_id]以及customer_id[已解密]
require('../../../../back_init.php');
$link =mysql_connect(DB_HOST,DB_USER, DB_PWD);
mysql_select_db(DB_NAME) or die('Could not select database');
/* 参数获取 */
$shop_id =$configutil->splash_new($_POST["shop_id"]);
$member_template_type = 1;
if(!empty($_POST["member_template_type"])){
   $member_template_type=$_POST["member_template_type"];	
}
$OpenBillboard = 0;
if(!empty($_POST["OpenBillboard"])){
	$OpenBillboard = $configutil->splash_new($_POST["OpenBillboard"]); //是否开启龙虎榜
}
$is_qr_code = 1;
$is_qr_code = $configutil->splash_new($_POST["is_qr_code"]); //是否开启个人中心二维码海报

$is_my_commission = 0;
if(!empty($_POST["is_my_commission"])){
   (int)$is_my_commission = $configutil->splash_new($_POST["is_my_commission"]);	//是否开启我的佣金
}
$template_type_bg=$configutil->splash_new($_POST["template_type_bg"]);
$template_head_bg='';
//echo $_FILES['new_template_type_bg']['name'];

	if($template_type_bg==1){
/* file_put_contents('hello.txt','**********'.$_FILES['new_define_share_image']['tmp_name']);
file_put_contents('hello2.txt','-------'.$_FILES['new_template_type_bg']['name']); */
	if(!empty($_FILES['new_template_type_bg']['name'])){
		$rand1=rand(0,9);
		$rand2=rand(0,9);
		$rand3=rand(0,9);	
		$filename=date("Ymdhis").$rand1.$rand2.$rand3;
		$filetype=substr($_FILES['new_template_type_bg']['name'], strrpos($_FILES['new_template_type_bg']['name'], "."),strlen($_FILES['new_template_type_bg']['name'])-strrpos($_FILES['new_template_type_bg']['name'], "."));
		$filetype=strtolower($filetype);
		if(($filetype!='.jpg')&&($filetype!='.png')&&($filetype!='.gif')){
				echo "<script>alert('文件类型或地址错误');</script>";
				//echo "<script>history.back(-1);</script>";
				exit ;
			}	
		$filename=$filename.$filetype;
		$savedir='../../../../'.Base_Upload.'Base/personalization/personal_center/';
		file_put_contents('hello3.txt',$davedir.'++++'.$filename);
		if(!is_dir($savedir)){
			mkdir($savedir,0777,true);
		}
		 $savefile=$savedir.$filename;
		if (!move_uploaded_file($_FILES['new_template_type_bg']['tmp_name'], $savefile)){
			echo "<script>文件上传成功！</script>";
			//echo "<script>history.back(-1);</script>";
			exit;
		}
		$save_destination = str_replace("../","",$savefile);
		$template_head_bg = "/weixinpl/".$save_destination;
		
	}else{
	$template_head_bg=$_POST['now_template_type_bg'];
	}
}
	
 if($shop_id>0){
	$sql="update weixin_commonshops set is_qr_code=".$is_qr_code.",member_template_type=".$member_template_type.",	is_my_commission=".$is_my_commission.",	openbillboard=".$OpenBillboard.",template_head_bg='".$template_head_bg."' where id=".$shop_id;	 
	fwrite($f, "====sql===".$sql."\r\n");  
	
	mysql_query($sql)or die(' Query failed1: ' . mysql_error()); 
 }
$error = mysql_error();	
mysql_close($link);
echo $error; 
echo "<script>location.href='personal_center.php?customer_id=".$customer_id_en."';</script>"
?>