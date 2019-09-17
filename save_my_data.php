<?php
header("Content-type: text/html; charset=utf-8"); 
require('../config.php');
require('../customer_id_decrypt.php'); //导入文件,获取customer_id_en[加密的customer_id]以及customer_id[已解密]
$link = mysql_connect(DB_HOST,DB_USER,DB_PWD);
mysql_select_db(DB_NAME) or die('Could not select database');
require('../common/utility.php');

//头文件----start
require('../common/common_from.php');
//头文件----end

$id 				='';
$name 				='';
$wechat_id 			='';
$sex				='';
$phone				='';
$qq 				='';
$birthday			='';
$job 				='';
$identityimgf 		= $configutil->splash_new($_POST['wechat_code']);	//我二维码
$identityimgt 		= $configutil->splash_new($_POST['weixin_headimgurl']);	//头像



if(!empty($_FILES['Filedata_']['name'][0]) || !empty($_FILES['Filedata_']['name'][1])){
	
	$img_url = '../up/'.$customer_id."/".$user_id;
	if(!is_dir($img_url))
	{
		mkdir($img_url, 0755, true);
	}

	foreach ($_FILES["Filedata_"]["name"] as $key => $val) {
	        
    	$exten = strtolower( pathinfo($_FILES['Filedata_']['name'][$key], PATHINFO_EXTENSION) ); //后缀
        $tmp_name = $_FILES["Filedata_"]["tmp_name"][$key];//旧文件名
        $newname = $user_id.time().$key.'.'.$exten;
        $new_url = $img_url.'/'.$newname;
        move_uploaded_file($tmp_name, $new_url);

        if($key == 0 && !empty($_FILES['Filedata_']['name'][0]) ){
        	@unlink ($identityimgt); 
        	$identityimgt = $new_url;//头像

        }
        if($key == 1 && !empty($_FILES['Filedata_']['name'][1]) ){
        	@unlink ($identityimgf);
        	$identityimgf = $new_url;//二维码
        }
	}
}
//echo "====二维码===".$identityimgf."===头像===".$identityimgt;die;



$name 			= $configutil->splash_new($_POST['name']);
$wechat_id 		= $configutil->splash_new($_POST['wechat_id']);
$sex 			= $configutil->splash_new($_POST['sex']);
$phone 			= $configutil->splash_new($_POST['phone']);
$qq 			= $configutil->splash_new($_POST['qq']);
$birthday 		= $configutil->splash_new($_POST['birthday']);
$job 			= $configutil->splash_new($_POST['job']);

$query = "UPDATE weixin_users SET name='$name',sex=$sex,phone='$phone',qq='$qq',birthday='$birthday',weixin_headimgurl='$identityimgt' WHERE id=".$user_id;
mysql_query($query)or die('Query failed68: ' . mysql_error());

$wechat_code = '';
$sql = "SELECT wechat_code FROM weixin_users_extends WHERE user_id = $user_id LIMIT 1";
$res = mysql_query($sql)or die('Query failed86: ' . mysql_error());
while( $row = mysql_fetch_object($res) ){
	$wechat_code = $row->wechat_code;
}
if( $wechat_code != '' ){
	unlink($old_code);
}
$query = "UPDATE weixin_users_extends SET occupation='$job',wechat_id='$wechat_id',wechat_code='$identityimgf' WHERE user_id=".$user_id;
//echo $query;die;
mysql_query($query);


	echo '<script>window.history.go(-2)</script>';










?>