<?php
header("Content-type: text/html; charset=utf-8"); 
require('../config.php');
require('../customer_id_decrypt.php'); //导入文件,获取customer_id_en[加密的customer_id]以及customer_id[已解密]
$link = mysql_connect(DB_HOST,DB_USER,DB_PWD);
mysql_select_db(DB_NAME) or die('Could not select database');
require('../common/utility.php');
$customer_id = $_SESSION['customer_id'];
$user_id = $_SESSION['user_id_'.$customer_id];


$save_type 	= $configutil->splash_new($_POST["save_type"]);
$pw_cur 	= trim(MD5($configutil->splash_new($_POST["pw_cur"])));
$pw_new 	= trim(MD5($configutil->splash_new($_POST["pw_new"])));
$id 		= -1;
$query 		= "SELECT id,paypassword FROM user_paypassword WHERE isvalid=true AND customer_id=".$customer_id." AND user_id=".$user_id." LIMIT 1";
$result 	= mysql_query($query) or die('Query failed25: ' . mysql_error());



if($save_type == 'modify'){//修改密码
	while( $row = mysql_fetch_object($result) ){
		$pay_password = $row->paypassword;
		$arr = array();
		if( $pw_cur != $pay_password ){
			$arr['msg']	 = 40001;
			echo json_encode($arr);
			return false;
		}
		if( $pw_cur == $pay_password ){
			$query = "UPDATE user_paypassword SET paypassword='".$pw_new."' WHERE isvalid=true AND customer_id=".$customer_id." AND user_id=".$user_id." LIMIT 1";
			$result= mysql_query($query) or die('Query failed40: ' . mysql_error());	
			$arr['msg']	 = 401;
			echo json_encode($arr);
			return false;
		}
	}
}elseif($save_type == 'set_up'){//设置密码
		//$pay_password = '';
		$arr 		  = array();
		while( $row = mysql_fetch_object($result) ){
			$pay_password = $row->paypassword;
			$id 		  = $row->id;
		}
		//var_dump($pay_password);
		if( $pay_password != '' or $pay_password  != NULL ){
			$arr['msg'] = 40001; //错误提示。当前已拥有支付密码
			echo json_encode($arr);
			return false;
		}
		if( $pw_cur == '' || $pw_new  == '' ){
			$arr['msg'] = 40006; //密码为空
			echo json_encode($arr);
			return false;
		}

		if( $pw_cur !== $pw_new ){
			$arr['msg']	 = 40002; //错误提示。两次密码不一致
			echo json_encode($arr);
			return false;
		}else{
			if( $id < 0 ){
				$query = "INSERT INTO user_paypassword(isvalid,customer_id,user_id,paypassword,createtime) VALUES(true,$customer_id,$user_id,'$pw_new',now())";
				//echo $query;die;
				mysql_query($query) or die('Query failed62: ' . mysql_error());
				$arr['msg']	 = 401;
				echo json_encode($arr);
				return false;
			}else{
				$query = "UPDATE user_paypassword SET paypassword='".$pw_new."' WHERE isvalid=true AND customer_id=".$customer_id." AND user_id=".$user_id." LIMIT 1";
				//echo $query;die;
				$result= mysql_query($query) or die('Query failed40: ' . mysql_error());	
				$arr['msg']	 = 401;
				echo json_encode($arr);
				return false;
			}
				
		}
}








?>