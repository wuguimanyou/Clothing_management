<?php
header("Content-type: text/html; charset=utf-8");     
require('../config.php');
require('../customer_id_decrypt.php'); //导入文件,获取customer_id_en[加密的customer_id]以及customer_id[已解密]
require('../common/utility_shop.php');
require('../common/utility_fun.php');
$link = mysql_connect(DB_HOST,DB_USER,DB_PWD); 
mysql_select_db(DB_NAME) or die('Could not select database');

$customer_id = $_SESSION['customer_id'];
$user_id = $_SESSION['user_id_'.$customer_id];

$shopMessage_Utlity = new shopMessage_Utlity;
$from_user_id 	= $user_id;															//转赠者的id
$to_user_id	  	= $configutil->splash_new($_POST["to_user_id"]);					//受益者的id
$send_currency	= mysql_real_escape_string(abs(trim($configutil->splash_new($_POST["send_currency"]))));		//需要转赠的购物币
$password 	  	= mysql_real_escape_string(MD5($configutil->splash_new($_POST["password"])));
$batchcode 		= $from_user_id.time();
$describe 		= mysql_real_escape_string($configutil->splash_new($_POST["describe"]));						//祝福语
$type 			= $configutil->splash_new($_POST["type"]);							//处理类型 ：send_currency:转赠购物币  Check_pw:检测用户

$arr = array();


$query 	= "SELECT paypassword FROM user_paypassword WHERE isvalid=true AND customer_id=$customer_id AND user_id=".$from_user_id." limit 1";
$pay_password = '';

if( $type == 'Check_pw' ){
	$result = mysql_query( $query ) or die('Query failed 24: ' . mysql_error());
	while( $row = mysql_fetch_object( $result ) ){
		$pay_password = $row->paypassword;
	}
	//echo $pay_password;die;
	if( $pay_password == '' ){
		$arr['msg'] 	= 400;//未设置支付密码
		$arr["remark"]	= "未设置支付密码";
		echo json_encode($arr);
		return false;
	}else{
		$arr['msg'] = 40004;	//密码不为空
		echo json_encode( $arr );
		return false;
	}
	if( $send_currency <= 0 ){
		$arr["msg"] 	= 100211;
		$arr["remark"]	= "转赠不能为零或负数！";
		$arr = json_encode( $arr );
		die($arr);
	}
}elseif($type=='send_currency'){
//验证发过来的转赠购物币是否为数字
	if(!preg_match("/^([1-9][\d]{0,7}|0)(\.[\d]{1,2})?$/",$send_currency) && !preg_match("/^([1-9][\d]{0,7}|0)(\.[\d]{1,2})?$/",$password)){
		$arr["msg"] 	= 10020;
		$arr["remark"]	= "请输入正确的数字";
		$arr = json_encode( $arr );
		die($arr);
	}
	if( $to_user_id == -1 ){
		$arr["msg"] 	= 11023;
		$arr["remark"]	= "请输入您想转赠到的用户";
		$arr = json_encode( $arr );
		die($arr);
	}
	if( $send_currency <= 0 ){
		$arr["msg"] 	= 100211;
		$arr["remark"]	= "转赠不能为零或负数！";
		$arr = json_encode( $arr );
		die($arr);
	}
	$result = mysql_query( $query ) or die('Query failed 36: ' . mysql_error());
	while( $row = mysql_fetch_object( $result ) ){
		$pay_password = $row->paypassword;
	}
	if( $pay_password == '' ){
		$arr['msg'] = 400;//未设置支付密码
		$arr["remark"]	= "未设置支付密码";
		echo json_encode( $arr );
		die( $arr );
	}

	if( $pay_password != $password ){
		$arr["msg"] 	= 40001;
		$arr["remark"]	= "支付密码错误";
		$arr = json_encode($arr);
		die( $arr );

	}else{
		$from_weixin_name 	= '';//转赠者的微信名
		$custom 			= '';//购物币自定名
		$from_user_currency =  0;//转赠者的购物币
		$mini_limit			=  0;//购物币转赠后余额最低限制
		$max_currency 		=  0;//最少转赠多少购物币

		//查询自己最多可转赠、余额
		$query  = "SELECT 	u.currency,
							c.mini_limit,
							c.custom,
							w.weixin_name,
							c.custom 
				   FROM weixin_commonshop_user_currency u 
				   LEFT JOIN weixin_commonshop_currency c ON u.customer_id=c.customer_id 
				   LEFT JOIN weixin_users w ON w.id=u.user_id 
				   WHERE u.isvalid=TRUE AND u.user_id=".$from_user_id." LIMIT 1";

		$result = mysql_query( $query );
		while( $row = mysql_fetch_object( $result )){
			$from_weixin_name 	= $row->weixin_name;					//转赠者的微信名
			$custom 			= $row->custom;							//购物币自定义
			$from_user_currency = cut_num($row->currency,2);
			$mini_limit			= cut_num($row->mini_limit,2);
			$max_currency 		= cut_num($from_user_currency-$mini_limit,2);		//可转赠的购物币数额 = 自己账户余额-转赠后最低余额
			
			if( $max_currency < $send_currency ){			
				$arr['msg'] 	= 40002;			//余额不足；
				$arr['remark']	= "您的账户余额不足！";
				echo json_encode( $arr );
				return false;

			}else{

				//验证受益者的购物币是否有数据，没有则添加一条
				$to_curr_id = -1;
				$query = "SELECT id FROM weixin_commonshop_user_currency WHERE isvalid=true AND customer_id=$customer_id AND user_id = ".$to_user_id." LIMIT 1";
				$result= mysql_query( $query ) or die('Query failed 112: ' . mysql_error());
				while( $row = mysql_fetch_object( $result )){
					$to_curr_id = $row->id;
				}
				if( $to_curr_id < 0 ){
					$shopMessage_Utlity->CheckCurrency($to_user_id,$customer_id);
				}

				$after_currency 	= $from_user_currency-$send_currency;		//转赠后余额
				$remark1 = "（支出）".$custom." 转赠到用户【".$to_user_id."】 | 金额：".$send_currency;
				$up_sql1 = "UPDATE weixin_commonshop_user_currency SET currency=currency-".$send_currency." WHERE isvalid=true AND user_id=".$from_user_id;
				mysql_query($up_sql1) or die('Query failed 79: ' . mysql_error());
				$log_sql = "INSERT INTO 
							weixin_commonshop_currency_log(
							isvalid,
							user_id,
							customer_id,
							cost_money,
							cost_currency,
							after_currency,
							batchcode,
							status,
							type,
							class,
							remark,
							createtime) VALUES(
							true,"
							.$from_user_id.","
							.$customer_id.","
							.$send_currency.","
							.$send_currency.","
							.$after_currency.","
							.$batchcode.",1,0,2,'"
							.$remark1."'
							,now())";

				mysql_query($log_sql) or die('Query failed 104: ' . mysql_error());
				$ins_id = mysql_insert_id();
				$query 	= "SELECT c.currency,u.weixin_fromuser,u.weixin_name FROM weixin_commonshop_user_currency c LEFT JOIN weixin_users u ON c.user_id=u.id WHERE u.isvalid=TRUE AND c.customer_id=".$customer_id." AND c.user_id=".$to_user_id." LIMIT 1";
				$result = mysql_query($query);
				while($row2 = mysql_fetch_object($result)){
					$weixin_name 		= $row2->weixin_name;
					$weixin_fromuser 	= $row2->weixin_fromuser;
					$currency 			= cut_num($row2->currency,2);
					$after_currency 	= cut_num($currency+$send_currency,2);
				}

				$remark2 = "（收入）".$custom." 转赠来源【".$from_user_id."】 | 金额：".$send_currency;
				$up_sql2 = "UPDATE weixin_commonshop_user_currency SET currency=currency+".$send_currency." WHERE isvalid=true AND user_id=".$to_user_id;
				mysql_query($up_sql2) or die('Query failed 117: ' . mysql_error());
				$log_sql = "INSERT INTO weixin_commonshop_currency_log(
							isvalid,
							user_id,
							customer_id,
							cost_money,
							cost_currency,
							after_currency,
							batchcode,
							status,
							type,
							class,
							remark,
							createtime) VALUES(
							true,"
							.$to_user_id.","
							.$customer_id.","
							.$send_currency.","
							.$send_currency.","
							.$after_currency.","
							.$batchcode.",1,1,2,'"
							.$remark2."'
							,now())";
				mysql_query($log_sql)or die('Query failed 140: ' . mysql_error());

				$arr['msg'] 			= 401;				//转赠成功；
				$arr['ins_id']			= $ins_id;			//支出的id；
				$arr['send_currency']	= cut_num($send_currency,2);	//转赠金额
				$arr['from_user_id']	= $from_user_id;	//出账人
				$arr['to_user_id']		= $to_user_id;		//入账人
				$arr['batchcode']		= $batchcode;		//出入账订单号

				$msg_content = 	"亲，您获得了".$send_currency."个".$custom."\r\n".
								"来源于：【转赠】\n".
								"转赠者：【".$from_user_id."】\n".
								"留言：【".$describe."】\n".
								"时间：<".date( "Y-m-d H:i:s").">";

				$shopMessage_Utlity->SendMessage($msg_content,$weixin_fromuser,$customer_id);

				echo json_encode($arr);
				//return false;
			}
		}
	}
}











?>