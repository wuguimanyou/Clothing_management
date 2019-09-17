<?php
header("Content-type: text/html; charset=utf-8");
require('../config.php');
require('../customer_id_decrypt.php'); //导入文件,获取customer_id_en[加密的customer_id]以及customer_id[已解密]
require('../common/utility.php');

$link = mysql_connect(DB_HOST, DB_USER, DB_PWD);
mysql_select_db(DB_NAME) or die('Could not select database');
require('../proxy_info.php');
require('../common/utility_shop.php');

$shopmessage = new shopMessage_Utlity(); //返佣、发信息、查找上一级

$user_id		= -1;
$parent_id		= -1;
$exp_user_id	= -1;
$package_id   	= $configutil->splash_new($_POST["package_id"]); 	//礼包id

if(!empty( $_SESSION["user_id_".$customer_id] )){
	$user_id		= $_SESSION["user_id_".$customer_id];
}elseif( !empty($_POST["user_id"]) ){
	$user_id	= $configutil->splash_new($_POST["user_id"]); 		//用户id
}

if(!empty($_POST["parent_id"])){
	$parent_id		= $configutil->splash_new($_POST["parent_id"]); //改关系上级id
	$exp_user_id	= $parent_id;									//分佣推广员id
}
$name			= $configutil->splash_new($_POST["user_name"]); 	//名称
$phone			= $configutil->splash_new($_POST["user_phone"]); 	//电话
$location_p		= $configutil->splash_new($_POST["location_p"]); 	//省
$location_c		= $configutil->splash_new($_POST["location_c"]); 	//市
$location_a		= $configutil->splash_new($_POST["location_a"]); 	//区
$address		= $configutil->splash_new($_POST["address"]); 		//详细地址
$e_id			= $configutil->splash_new($_POST["e_id"]); 			//快递id
$e_id			= passport_decrypt((string)$e_id);					//解密
$e_name			= $configutil->splash_new($_POST["e_name"]); 		//快递名字
$add_id       	= $configutil->splash_new($_POST["add_id"]);		//默认地址id
$add_id       	= passport_decrypt((string)$add_id);					//默认地址id
$paytype       	= $configutil->splash_new($_POST["paytype"]);		//支付方式




/*判断customer_id是否正常开始*/
if(!isset($customer_id)){
	$json["status"]	= 10001;
	$json["msg"]	= "登录超时，请重新登录！$customer_id";
	$jsons			= json_encode($json);
	die($jsons);	
}
/*判断customer_id是否正常结束*/

/*判断user_id是否正常开始*/
if( 1 > $user_id ){
	$json["status"] = 10002;
	$json["msg"] 	= "未知错误！没有获取到个人信息！";
	$jsons=json_encode($json);
	die($jsons);
}
/*判断user_id是否正常结束*/

/*生成订单号开始*/
$stringtime = date("Y-m-d H:i:s", time());
$batchcode  = strtotime($stringtime);
$batchcode  = $user_id . $batchcode;
/*生成订单号结束*/

/*判断订单号是否正常开始*/
if( !is_numeric($batchcode) or $batchcode < 0 ){
	$json["status"] = 10003;
	$json["msg"] = "订单号不正确！";
	$jsons=json_encode($json);
	die($jsons);		
}
/*判断订单号是否正常结束*/

/*判断快递id是否正常开始*/
if( !is_numeric($e_id) or $e_id < 0 ){
	$json["status"] = 10003;
	$json["msg"] = "快递异常！";
	$jsons=json_encode($json);
	die($jsons);		
}
/*判断快递id是否正常结束*/

/*计算快递费开始*/
$e_price = 0;//快递费
$query = "select price from weixin_expresses where id=".$e_id;
$result = mysql_query($query) or die('W107 Query failed: ' . mysql_error());
while ($row = mysql_fetch_object($result)) {	
	$e_price	= $row->price;				
}
/*计算快递费结束*/

/* 查找下单者名字 和上级 parent_id 开始*/
$query = "SELECT id,weixin_name,parent_id from weixin_users where isvalid=true and customer_id=" . $customer_id . " and id=" . $user_id . " limit 0,1";
$result = mysql_query($query) or die('W107 Query failed: ' . mysql_error());
while ($row = mysql_fetch_object($result)) {
	$user_id 		= $row->id;
	$user_parent_id = $row->parent_id;
	$weixin_name 	= $name."(".$row->weixin_name.")";
	break;
}
if( $exp_user_id < 0 ){
	$exp_user_id = $user_parent_id;
}
/*查找是否开启复购开始*/
$is_rePurchase = 0;//复购开关:0、关闭；1、开启
$query = "SELECT is_rePurchase from weixin_commonshop_pay_pack where isvalid=true and customer_id=" . $customer_id. " limit 0,1";
$result = mysql_query($query) or die('W107 Query failed: ' . mysql_error());
while ($row = mysql_fetch_object($result)) {
	$is_rePurchase = $row->is_rePurchase;
	break;
}
/*查找是否开启复购结束*/

if( $is_rePurchase ){
	$query2 = "select id,isAgent from promoters where status=1 and isvalid=true and user_id=" . $user_id; //查自己是否是推广员
	$result2 = mysql_query($query2) or die('Query failed: ' . mysql_error());
	$user_promoter_id = -1;
	
	while ($row2 = mysql_fetch_object($result2)) {
		$user_promoter_id = $row2->id;
	}
	if( $user_promoter_id > 0 ){
		$exp_user_id = $user_id;
	}
}

/* 查找下单者user_id 和上级 parent_id 结束*/

/*查询礼包信息开始*/
$package_name      = -1; //礼包名称
$package_type      = -1; //礼包类型：1 推广员礼包、2 股东礼包、3 团队奖励礼包
$package_price     = -1; //价格
$commision_mode    = -1; //分佣方式 (八级分佣_股东分红_区域团队_3*3)(0_0_0_0)
$shareholder_all   = 0; //股东分红总奖励
$team_all          = 0; //团队总奖励
$area_id           = -1; //团队区域id
$init_reward       = 0; //总佣金比例
$stock             = 0; //库存
$isout             = 0; //下架
$shareholder_level = 0; //股东等级
$three_level	   = 0; //3*3等级
$query = 'SELECT package_name,package_type,price,commision_mode,shareholder_all,team_all,area_id,stock,init_reward,shareholder_level,three_level FROM package_list_t where isvalid=TRUE and id=' . $package_id . " and customer_id=" . $customer_id;
$result = mysql_query($query) or die('w300 Query failed: ' . mysql_error());
while ($row = mysql_fetch_object($result)) {
	$package_name      = $row -> package_name;
	$package_type      = $row -> package_type;
	$package_price     = $row -> price;
	$commision_mode    = $row -> commision_mode;
	$shareholder_all   = $row -> shareholder_all;
	$team_all          = $row -> team_all;
	$area_id           = $row -> area_id;
	$init_reward       = $row -> init_reward;
	$stock             = $row -> stock;
	$isout             = $row -> isout;
	$shareholder_level = $row -> shareholder_level;
	$three_level	   = $row -> three_level;
}
$reward_money = $init_reward * $package_price;//返佣金额
if( $package_type == 2 ){
	$user_status = 0;
	$query = "select status from promoters where isvalid=true and user_id=".$user_id;
	$result = mysql_query($query) or die('Query failed5: ' . mysql_error());   
	while ($row = mysql_fetch_object($result)) {
		$user_status = $row->status;
	}
	if( $user_status != 1 ){
		$json["status"] = 10007;
		$json["msg"] 	= "等级不够！";
		$jsons			= json_encode($json);
		die($jsons);
	}
}
//判断库存
if( 1 > $stock ){
	$json["status"] = 10004;
	$json["msg"] = "库存不足！";
	$jsons=json_encode($json);
	die($jsons);
}
//判断是否下架
if( $isout == 1 ){
	$json["status"] = 10005;
	$json["msg"] = $package_name."已下架！";
	$jsons=json_encode($json);
	die($jsons);
}
/*查询礼包信息结束*/

$total = $package_price + $e_price;

/*生成订单开始*/
$query = "insert into package_order_t (customer_id,user_id,before_parent_id,exp_user_id,parent_id,isvalid,p_id,createtime,batchcode,rcount,totalprice,reward_money,status,package_name,package_type,package_price,commision_mode,area_id,shareholder_level,three_level,exp_price,paytype) values (" .$customer_id. "," .$user_id. ",".$user_parent_id.",".$exp_user_id."," .$parent_id. ",true," .$package_id. ",now(),'" .$batchcode. "',1," .$total. ",".$reward_money.",2,'" .$package_name. "'," .$package_type. "," .$package_price. ",'" .$commision_mode. "'," .$area_id. "," .$shareholder_level. ",".$three_level.",".$e_price.",'".$paytype."')";
//echo $query;
mysql_query($query) or die('W388 Query failed: ' . mysql_error());
$order_id = mysql_insert_id();

$query = "insert into package_order_express_t (isvalid,customer_id,batchcode,name,phone,location_p,location_c,location_a,address,express_id,fan_expressname) values (true," .$customer_id. ",'" .$batchcode. "','" .$name. "'," .$phone. ",'" .$location_p. "','" .$location_c. "','" .$location_a. "','" .$address. "',".$e_id.",'".$e_name."');";
mysql_query($query) or die('W389 Query failed: ' . mysql_error());
/*生成订单结束*/

/*生成默认地址开始*/
if( 1 > $add_id ){
	$query = "insert into weixin_commonshop_addresses (isvalid,user_id,address,name,phone,location_p,location_c,location_a,identity,is_default) values (true," .$user_id. ",'" .$address. "','" .$name. "'," .$phone. ",'" .$location_p. "','" .$location_c. "','" .$location_a. "','',1);";
	mysql_query($query) or die('W389 Query failed: ' . mysql_error());
}
/*生成默认地址结束*/

$json["status"] 	= 1;
$json["msg"] 		= "提交成功";
$json["order_id"]	= $order_id;
$json["batchcode"] 	= $batchcode;
	
$error = mysql_error();
if(!empty($error)){
	$json["status"] = 10006;
	$json["msg"] = $error;	
}
$jsons=json_encode($json);
die($jsons);
?>
