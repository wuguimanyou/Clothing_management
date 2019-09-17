<?php
header("Content-type: text/html; charset=utf-8");     
require('../config.php');
require('../customer_id_decrypt.php'); //导入文件,获取customer_id_en[加密的customer_id]以及customer_id[已解密]
require('../common/utility_fun.php');
$link = mysql_connect(DB_HOST,DB_USER,DB_PWD); 
mysql_select_db(DB_NAME) or die('Could not select database');


$customer_id = $_SESSION['customer_id'];
$user_id = $_SESSION['user_id_'.$customer_id];


$save_type 			= $configutil->splash_new($_POST["save_type"]);			//处理类型
$type 				= $configutil->splash_new($_POST["type"]);				//处理类型 1 微信 2 支付宝	3 财付通 5 银行卡
$password 			= MD5($configutil->splash_new($_POST["pw"]));			//密码
$phone    			= trim($configutil->splash_new($_POST["phone"]));				//电话号码
$name    			= trim($configutil->splash_new($_POST["name"]));				//姓名
$account    		= trim($configutil->splash_new($_POST["account"]));			//账号
$customer_id_en 	= $configutil->splash_new($_POST["customer_id"]);		//商家
$account_bankname 	= $configutil->splash_new($_POST["account_bankname"]);	//
$account_address 	= $configutil->splash_new($_POST["account_address"]);


// if(!preg_match("/^1[34578]{1}\d{9}$/",$phone) || !preg_match("/^[1-9]d*$/",$account)){
// 	echo json_encode(40005);
// 	return false;
// }


//先判断用户是否有支付密码
$pay_password = "";
$query = "SELECT paypassword FROM user_paypassword WHERE isvalid=true AND customer_id=".$customer_id." AND user_id=".$user_id." LIMIT 1";
//echo $query;die;
$result= mysql_query($query) or die('Query failed 43: ' . mysql_error());
while( $row = mysql_fetch_object($result) ){
    $pay_password = $row->paypassword;
}
//echo $pay_password;die;

switch ($save_type) {

	case 'CheckPassword':
		if($pay_password==''){
			echo json_encode(40001);//假如密码为空，则未设置
			return fales;
		}else{
			echo json_encode(40002);//密码不为空，继续执行
			return fales;
		}
	break;
	
	case 'bind_account':

		if($pay_password==''){
			echo json_encode(40001);//假如密码为空，则未设置
			return fales;
		}

		if($pay_password!==$password){
			echo json_encode('400');
			return false;
		}
		//验证密码----------end

		//修改或新增-------star
		$id = -1;
		$query = "SELECT id FROM moneybag_account WHERE type=".$type." AND user_id=".$user_id." LIMIT 1";
		$result= mysql_query($query);
		while($row=mysql_fetch_object($result)){
			$id=$row->id;
		}
		//echo $id;die;
		if($id>0){
			$sql = "UPDATE moneybag_account SET real_name='$name',phone='$phone',bind_account='$account',bind_band='$account_bankname',bind_bang_address='$account_address' WHERE isvalid=true AND type=$type AND user_id=".$user_id;
			mysql_query($sql);
			echo json_encode('401');
		}else{
			$sql = "INSERT INTO moneybag_account(isvalid,customer_id,user_id,type,real_name,phone,bind_account,bind_band,bind_bang_address,createtime) VALUES(true,$customer_id,$user_id,$type,'$name','$phone','$account','$account_bankname','$account_address',now())";
			mysql_query($sql);
			echo json_encode('402');

		}
	break;
	default:
		# code...
		break;
}

?>