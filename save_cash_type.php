<?php
header("Content-type: text/html; charset=utf-8"); 
require('../../../config.php');
require('../../../customer_id_decrypt.php'); //导入文件,获取customer_id_en[加密的customer_id]以及customer_id[已解密]
$link = mysql_connect(DB_HOST,DB_USER,DB_PWD);
mysql_select_db(DB_NAME) or die('Could not select database');
mysql_query("SET NAMES UTF8");
require('../../../common/utility_shop.php');

$shopMessage_Utlity = new shopMessage_Utlity;

$type 	= $configutil->splash_new($_POST["type"]);
$id 	= $configutil->splash_new($_POST["id"]);
$remark = $configutil->splash_new($_POST["tis"]);
$user_id = -1;
$getmoney = 0;
$query = "SELECT user_id,getmoney,batchcode,status FROM weixin_cash_being_log WHERE isvalid=true AND customer_id=$customer_id AND id=".$id." LIMIT 1";
$result= mysql_query($query);
while( $row = mysql_fetch_object($result) ){
	$user_id 	= $row->user_id;
	$getmoney 	= $row->getmoney;
	$batchcode 	= $row->batchcode;
	$status 	= $row->status;
}

if($type=='delete_type'){
	//如果该提现未被驳回，则需要退钱进钱包
	//提现不通过则返还金额给用户
	if( $status == 0 ){
		$query = "UPDATE moneybag_t SET balance = balance + $getmoney WHERE isvalid = true AND customer_id = $customer_id AND user_id = $user_id";
		mysql_query($query);
		//插入日志
		$remark = "提现申请退回";
		$query = "INSERT INTO moneybag_log(isvalid,customer_id,user_id,money,type,batchcode,pay_style,remark,createtime) VALUES(true,$customer_id,$user_id,'$getmoney',0,'$batchcode',5,'$remark',now())";
		mysql_query($query);
	}
	
	$query = "UPDATE weixin_cash_being_log SET isvalid = false WHERE isvalid=true AND customer_id=$customer_id AND id=".$id;
	mysql_query($query);
	echo json_encode(400);
	return false;
}

if($status == 0){	//当该笔提现状态为未审核情况下才能操作

	//提现不通过则返还金额给用户
	$query = "UPDATE moneybag_t SET balance = balance + $getmoney WHERE isvalid = true AND customer_id = $customer_id AND user_id = $user_id";
	mysql_query($query);

	//插入日志
	//$remark = "商家驳回您的提现，提现金额为：【".$getmoney."】元";
	$query = "INSERT INTO moneybag_log(isvalid,customer_id,user_id,money,type,batchcode,pay_style,remark,createtime) VALUES(true,$customer_id,$user_id,'$getmoney',0,'$batchcode',5,'$remark',now())";
	mysql_query($query);

	switch ($type) {
		case 'false_type'://驳回申请
			$query = "UPDATE weixin_cash_being_log SET status = 2 WHERE isvalid=true AND customer_id=$customer_id AND id=".$id;
			mysql_query($query);
			if( $user_id > 0 ){
				$weixin_fromuser = '';
				$sql = "SELECT weixin_fromuser FROM weixin_users WHERE isvalid=true AND id=".$user_id." LIMIT 1";
				$res = mysql_query($sql);
				while( $row = mysql_fetch_object($res) ){
					$weixin_fromuser = $row->weixin_fromuser;
				}
				$msg_content = 	"亲，您申请钱包提现被驳回 \n".
								"申请提现金额：【".$getmoney."元】\n".
								"驳回理由：【".$remark."】\n".
								"时间：<".date( "Y-m-d H:i:s").">";
				$shopMessage_Utlity->SendMessage($msg_content,$weixin_fromuser,$customer_id);
			}

			echo json_encode(400);

		break;

		// case 'delete_type':
			$query = "UPDATE weixin_cash_being_log SET isvalid = false WHERE isvalid=true AND customer_id=$customer_id AND id=".$id;
			mysql_query($query);
			echo json_encode(400);
		// break;	

		default:
			# code...
			break;
	}

}

?>