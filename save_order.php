<?php
header("Content-type: text/html; charset=utf-8");
require('../config.php');
require('../customer_id_decrypt.php'); //导入文件,获取customer_id_en[加密的customer_id]以及customer_id[已解密]
$link = mysql_connect(DB_HOST, DB_USER, DB_PWD);
mysql_select_db(DB_NAME) or die('Could not select database');
require('../proxy_info.php');

require('../common/utility_shop.php');
//require('../common/utility_msg.php'); 
//----确认订单方法
require('order_newform_function.php');




$user_id		= $_SESSION["user_id_".$customer_id];
$shopmessage 	= new shopMessage_Utlity(); 	//返佣、发信息、查找上一级
$json_data 		= $_POST["json_data"];
//file_put_contents("save_order.txt","desc===".date("Y-m-d H:i:s")."===>".$json_data."\r\n",FILE_APPEND);
$json_data 		= json_decode($json_data,true);	//json转数组
//print_r($json_data);return;
//echo $json_data;return;
//file_put_contents ( "json_data.txt", "promoter_id====".var_export ( $json_data, true ) . "\r\n", FILE_APPEND );

/*清除确认订单页面的session 开始*/
	
	$_SESSION['bug_post_data_'.$user_id] = '';			//清除购物车数据

	$_SESSION['sendtime_id_'.$user_id] = '';			//清除送货时间
	$_SESSION['rtn_sendtime_array_'.$user_id] = '';
	
	$_SESSION['a_type_'.$user_id] = -1;					//清除选择地址的session
	
	$_SESSION['diy_area_id_'.$user_id] = '';			//清除自定义区域	
	$_SESSION['rtn_diy_area_array_'.$user_id] = '';

/*清除确认订单页面的session 结束*/

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
$batchcode_time  = strtotime($stringtime);
$batchcode  = $user_id . $batchcode_time;
/*生成订单号结束*/

/*判断订单号是否正常开始*/
if( !is_numeric($batchcode) or $batchcode < 0 ){
	$json["status"] = 10003;
	$json["msg"] = "订单号不正确！";
	$jsons=json_encode($json);
	die($jsons);		
}
/*判断订单号是否正常结束*/

$identity_order		= 0;													//判断是普通订单还是身份证下单
$sendstyle			= $configutil->splash_new($_POST["sendstyle"]);			//发货方式
$pay_immed 			= $configutil->splash_new($_POST["pay_immed"]);			//1立即购买，2找人代付

$paystyle       	= $configutil->splash_new($_POST["pay_type"]); 			//支付方式
//file_put_contents ( "json_data.txt", "paystyle====".var_export ( $paystyle, true ) . "\r\n", FILE_APPEND );
$user_open_curr		= $configutil->splash_new($_POST["user_open_curr"]);	//购买者是否开启购物币加入支付开关
$user_currency		= $configutil->splash_new($_POST["user_currency"]);		//购买者使用的购物币
$is_select_card		= $configutil->splash_new($_POST["is_select_card"]);	//会员卡使用开关
//file_put_contents ( "json_data.txt", "is_select_card====".var_export ( $is_select_card, true ) . "\r\n", FILE_APPEND );
//file_put_contents ( "json_data.txt", "user_currency====".var_export ( $user_currency, true ) . "\r\n", FILE_APPEND );
$user_currency		= abs( $user_currency );
$card_member_id		= -1;
if(!empty($_POST["select_card_id"])){
	$card_member_id		= $configutil->splash_new($_POST["select_card_id"]);	//会员卡id
	$card_member_id				= passport_decrypt((string)$card_member_id);	//解密
}

$sendtime       	= $configutil->splash_new($_POST["sendtime"]); 			//送货时间
$is_payother 		= 0;
$payother_desc	 	= "";
if(!empty($_POST["is_payother"])){
	$is_payother	= $configutil->splash_new($_POST["is_payother"]);		//是否代付状态
	$payother_desc	= $configutil->splash_new($_POST["payother_desc"]);		//代付描述
	
	if(empty($payother_desc)){
		$payother_desc = '蛋蛋的忧伤，钱不够了，你能不能帮我先垫付下';
	}
}
$diy_area_id    	= $configutil->splash_new($_POST["diy_area_id"]);		//区域模式 - 自定义区域编号
$diy_area_id		= passport_decrypt((string)$diy_area_id);	//解密


$fromuser_app = ""; //app运营商的粉丝标识
if (!empty($_SESSION["fromuser_app_" . $customer_id])) {
    $fromuser_app = $_SESSION["fromuser_" . $customer_id];
}

if ($fromuser_app == "null") {
    $fromuser_app = "";
}

/*购物币查询开始*/
$currency_id = -1;
$custom 	 = "购物币";
if( $user_currency > 0 ){	
	$query ="select id,currency from weixin_commonshop_user_currency where isvalid=true and user_id=".$user_id." order by id asc limit 1 ";	
	$result = mysql_query($query) or die('购物币查询Query failed: ' . mysql_error());
	$currency = 0;//钱包有的购物币
	while ($row = mysql_fetch_object($result)) {
		$currency_id	= $row->id;
		$currency		= $row->currency;
	}
	if( $currency < $user_currency ){
		$json["status"] = 10004;
		$json["msg"] = "数据异常！";
		$jsons=json_encode($json);
		die($jsons);
	}
	$sql = "SELECT custom FROM weixin_commonshop_currency WHERE customer_id=".$customer_id;
	$res = mysql_query($sql) or die('购物币查询Query failed: ' . mysql_error());
	while ($row = mysql_fetch_object($res) ){
		$custom = $row->custom;
	}
}

/*购物币查询结束*/

/*慈善公益开始*/
$is_charitable        = 0;	//慈善开关
$charitable_propotion = 0;	//慈善公益最低分配率
$integration_price    = 1;	//捐赠多少钱得1积分
$query ="select is_charitable,charitable_propotion,integration_price from charitable_set_t where isvalid=true and customer_id=".$customer_id;
$result = mysql_query($query) or die('*慈善公益查询 Query failed: ' . mysql_error());
while ($row = mysql_fetch_object($result)) {
	$is_charitable        = $row->is_charitable;
	$charitable_propotion = $row->charitable_propotion;
	$integration_price    = $row->integration_price;
}
/*慈善公益结束*/

/*查找商城设置开始*/
$issell_model      		= 1;  		//1:关闭复购;2:开启复购
$is_identity       		= 0;  		//是否开启身份证验证
$per_identity_num  		= 999;		//每个身份证号每天可下单数量
$is_cost_limit     		= 0;		//是否开启购买限制
$per_cost_limit    		= 9999;		//每人每天不高于的总额 
$is_weight_limit   		= 0;		//是否开启重量限制
$per_weight_limit  		= 999;   	//每人每天不高于的KG 
$is_number_limit   		= 0;   		//是否开启购买数量限制
$per_number_limit  		= 999;   	//每人每天不多于多少件产品 
$init_reward       		= 0;   		//总佣金比例 
$init_reward_again       		= 0;   		//总佣金比例 
$reward_type       		= 2;   		//返佣类型 1:积分 2:金额
$issell            		= false;	//false:没开启分佣 true:开启分佣
$distr_type				= 1;   		//1:下单锁定 2:第一次关注锁定
//$SupplyCostMoney		= 0;		//供应商的总供货价
//$baseCostMoney			= 0; 		//总成本价
$exp_name          		= "推广员";	//商家推广员自定义名称
$shop_card_id      		= -1;		//商家设定的会员卡ID
$sell_discount     		= 0;		//商家设定的产品折扣率
$total_is_Pinformation	= 0;		//商城必填信息开关
$minute_recovery_time	= 30;		//没支付订单失效时间（分钟）
$recovery_time			= 30;		//没支付订单失效时间

$query = "select 
			issell,
			exp_name,
			distr_type,
			is_identity,
			init_reward,
			init_reward_again,
			reward_type,
			issell_model,
			shop_card_id,
			is_cost_limit,
			sell_discount,
			per_cost_limit,
			is_number_limit,
			is_weight_limit,
			per_weight_limit,
			per_identity_num,
			per_number_limit
			from weixin_commonshops where isvalid=true and customer_id=" . $customer_id;
$result = mysql_query($query) or die('商城设置Q1 Query failed: ' . mysql_error());
while ($row = mysql_fetch_object($result)) {
	$issell				= $row->issell;	
	$exp_name			= $row->exp_name;
	$distr_type			= $row->distr_type;	
	$is_identity		= $row->is_identity; 
	$init_reward		= $row->init_reward;
	$init_reward_again  = $row->init_reward_again;
	$reward_type		= $row->reward_type;	
	$issell_model		= $row->issell_model; 
	$shop_card_id		= $row->shop_card_id;
	$is_cost_limit		= $row->is_cost_limit;
	$sell_discount		= $row->sell_discount;	
	$per_cost_limit		= $row->per_cost_limit;
	$is_number_limit	= $row->is_number_limit;
	$is_weight_limit	= $row->is_weight_limit;
	$per_weight_limit	= $row->per_weight_limit;
	$per_number_limit	= $row->per_number_limit;	
	$per_identity_num	= $row->per_identity_num; 
}

$query = "select is_Pinformation,recovery_time from weixin_commonshops_extend where isvalid=true and customer_id=" . $customer_id;
$result = mysql_query($query) or die('商城设置Q2 Query failed: ' . mysql_error());
while ($row = mysql_fetch_object($result)) {
	$total_is_Pinformation	= $row->is_Pinformation; 
	$minute_recovery_time	= $row->recovery_time; 
}
$recovery_time = $minute_recovery_time * 60;
$recovery_time = $recovery_time + $batchcode_time;
$recovery_time = date("Y-m-d H:i:s",$recovery_time);
/*查找商城设置结束*/

/* 获取收货信息 start */
$d_id 			= -1;
$name 			= "佚名"; 	//名称
$phone 			= "";		//电话
$address 		= "";		//详细地址
$identity 		= "";		//身份证号	
$location_p 	= "";		//省
$location_c 	= "";		//市
$location_a	 	= "";		//区
$identityimgt 	= "";		//身份证正面
$identityimgf 	= '';		//身份证反面

if(!empty($_POST["aid"])){
	$aid 	= $configutil->splash_new($_POST["aid"]); //地址编号 
	$aid	= passport_decrypt((string)$aid);					//解密
	$query	= "select name,phone,address,location_p,location_c,location_a,identity,identityimgt,identityimgf from weixin_commonshop_addresses where isvalid=true and user_id=".$user_id." and id =".$aid;
	$result = mysql_query($query) or die('W163 Query failed: ' . mysql_error());
	while ($row = mysql_fetch_object($result)) {
		$name 			= mysql_real_escape_string( $row->name );
		$phone 			= mysql_real_escape_string($row->phone );
		$address 		= mysql_real_escape_string($row->address );
		$location_p		= mysql_real_escape_string($row->location_p );
		$location_c		= mysql_real_escape_string($row->location_c );
		$location_a		= mysql_real_escape_string($row->location_a );	
		$identity 		= mysql_real_escape_string($row->identity );	//身份证
		$identityimgt 	= mysql_real_escape_string($row->identityimgt );
		$identityimgf 	= mysql_real_escape_string($row->identityimgf );
	}
}
/* 获取收货信息 end */


/*当日*/
$nowtime = time();
$year = date('Y',$nowtime);
$month = date('m',$nowtime);
$day = date('d',$nowtime);	
/*当日End*/


/* 查找下单者上级 parent_id 开始*/
$parent_id 		= -1;
$weixin_name 	= "";
$query = "SELECT parent_id,name,weixin_name from weixin_users where isvalid=true and customer_id=" . $customer_id . " and id='" . $user_id . "' limit 0,1";
$result = mysql_query($query) or die('查找上级Q1 Query failed: ' . mysql_error());
while ($row = mysql_fetch_object($result)) {
	$parent_id   = $row->parent_id;
	$weixin_name = $row->name."(".$row->weixin_name.")";
	$weixin_name = mysql_real_escape_string($weixin_name);
	break;
}
/* 查找下单者上级 parent_id 结束*/
$t_user_id 		= $parent_id;
$exp_user_id 	= $parent_id;
$promoter_id 	= -1;
/*开启复购开始*/
if($issell_model == 2){		//开启复购
	$t_user_id = $user_id;	//如果没有上一级,就查自己
}
$query2 = "select id from promoters where status=1 and isvalid=true and user_id=" . $t_user_id;
 
$result2 = mysql_query($query2) or die('开启复购Q1 Query failed: ' . mysql_error());
while ($row2 = mysql_fetch_object($result2)) {
    $promoter_id = $row2->id;
}
$promoters_ie = -1;
$query2 = "select id from promoters where status=1 and isvalid=true and user_id=" . $user_id;
 
$result2 = mysql_query($query2) or die('开启复购Q1 Query failed: ' . mysql_error());
while ($row2 = mysql_fetch_object($result2)) {
    $promoters_ie = $row2->id;
}
if ($promoter_id > 0) {			//如果自己或者上级 是推广员 和 不走代理商路线,则插入exp_user_id,
    //是推广员
    $exp_user_id = $t_user_id;	//插入订单的推广员 (包括自己)user_id
}
/*开启复购结束*/


/*判断渠道是否开启代理商功能开始*/
$is_distribution 	= 0;	//渠道取消代理商功能
$is_disrcount		= 0;
$agent_id 			= -1;
$final_agent_id		= -1;
$agentcont_type		= 0;	//代理结算 0:推广员结算 1:代理结算
$AgentCostMoney		= 0;	//这次购物的总额*代理折扣得到代理成本价	
$query1="select count(1) as is_disrcount from customer_funs cf inner join columns c where c.isvalid=true and cf.isvalid=true and cf.customer_id=".$customer_id." and c.sys_name='商城代理模式' and c.id=cf.column_id";
$result1 = mysql_query($query1) or die('W228 W_is_disrcount Query failed: ' . mysql_error());  
while ($row = mysql_fetch_object($result1)) {
   $is_disrcount = $row->is_disrcount;
   break;
}
if( $is_disrcount > 0 ){
   $is_distribution = 1;
}
/*判断渠道是否开启代理商功能结束*/

/*查找代理商开始*/
if( $is_distribution > 0 ){
	$agent_id 			= $shopmessage->searchParentId($user_id,$customer_id); //查找代理商
	
	if ($agent_id > 0) {
		//是推广员
		$final_agent_id = $agent_id; //最终代理商ID
	}
}
/*查找代理商结束*/

/*查找会员卡等级、余额、折扣开始*/
$remain_consume 	= 0;	//会员卡余额
$level_id 			= -1;	//会员卡等级
$card_discount 		= 0;	//会员卡折扣
$min_num = 0;

//查询1元返多少积分
$consume_score = "";
$sql 	= "select consume_score from weixin_cards where isvalid=true and customer_id=" . $customer_id ." and id=". $shop_card_id ."  limit 0,1";
$result = mysql_query($sql) or die('W653 Query failed: ' . mysql_error());
while ($row = mysql_fetch_object($result)) {
	$consume_score = $row->consume_score;
}
if( 0 < $card_member_id and $is_select_card > 0 ){
	// 查找会员等级 
	$sql 	= "select level_id from weixin_card_members where  id=" . $card_member_id . " limit 0,1";
	$result = mysql_query($sql) or die('w308 Query failed: ' . mysql_error());
	while ($row = mysql_fetch_object($result)) {
		$level_id = $row->level_id;
		break;
	}
	//查询会员卡余额
	$sql = "select remain_consume from weixin_card_member_consumes where card_member_id=" . $card_member_id. " limit 0,1";
	$result = mysql_query($sql) or die('w315 Query failed: ' . mysql_error());
	while ($row = mysql_fetch_object($result)) {
		$remain_consume = $row->remain_consume;
		break;
	}
	//查询个人会员卡等级折扣
	$query2  = "SELECT discount,min_num from weixin_card_levels where isvalid=true and  id=".$level_id. " limit 0,1";
	$result2 = mysql_query($query2) or die('w314 Query failed: ' . mysql_error());
	while ($row2 = mysql_fetch_object($result2)) {
		$card_discount = $row2->discount;	
		$min_num = $row2->min_num;

		break;
	}
}
/*查找会员卡等级、余额、折扣结束*/

/* 代金劵的金额---start */
$C_id = -1;
$C_Money = 0;
if (!empty($_POST["select_coupon_id"]) and $paystyle != "找人代付" and $paystyle != "暂不支付" ) { //代金劵ID
	$C_id = $configutil->splash_new($_POST["select_coupon_id"]); 
	if($C_id>0){
		$query_C = "SELECT Money FROM weixin_commonshop_couponusers WHERE user_id=".$user_id." AND customer_id=".$customer_id." AND isvalid=true AND type=1 AND is_used=0 AND id= ".$C_id;	//选择的优惠劵的金额
		//echo $query_C;
		$result_C = mysql_query($query_C) or die('W561 Query failed: ' . mysql_error());
		while ($row_C = mysql_fetch_object($result_C)) {
			$C_Money =  $row_C->Money;	
		}
	} 
}	
/* 代金劵的金额---end */

/*保存订单开始*/
$sql_order = "";
$sql_orders = "INSERT INTO weixin_commonshop_orders(
			fromuser_app,
			user_id,
			pid,
			pname,
			rcount,
			totalprice,
			remark,
			isvalid,
			createtime,
			status,
			customer_id,
			paystyle,
			sendstyle,
			prvalues,
			prvalues_name,
			sendtime,
			express_id,
			batchcode,
			pay_batchcode,
			expressname,
			card_member_id,
			address_id,
			exp_user_id,
			need_score,
			supply_id,
			agent_id,
			agentcont_type,
			identity,
			weight,
			is_QR,
			is_payother,
			store_id,
			store_name,
			AgentCostMoney,
			SupplyCostMoney,
			baseCostMoney,
			identity_order,
			reward_score,
			charitable
			)VALUES" ;

//库存回收表插入语句
$sql_SR	 = "";
$sql_SRs = "insert into stockrecovery_t(batchcode,pid,pos_id,stock,recovery_time,customer_id) values";
//减库存语句
$sql_storenum = "";
$sql_storeArr = array();

//添加到邮费表语句
$sql_EP  = "";
$sql_EPs = "insert into weixin_commonshop_order_express_prices(batchcode,price,isvalid,createtime) values";

//插入必填信息语句
$sql_RI	 = "";
$sql_RIs = "insert into weixin_commonshop_orders_requiredinformation_t(
				pid,
				createtime,
				isvalid,
				batchcode,
				information_head,
				information_con,
				customer_id
				) values";
				
//添加日志记录语句
$query_log	= "";
$query_logs = "insert into weixin_commonshop_order_logs(batchcode,operation,descript,operation_user,createtime,isvalid) values";

//添加订单地址语句
$sql_address  = "";
$sql_addresss  = "insert into weixin_commonshop_order_addresses(batchcode,name,phone,address,location_p,location_c,location_a,identity,diy_area_id,diy_area_name,identityimgt,identityimgf) values";

//添加商铺店铺订单语句
$sql_OP  = "";
$sql_OPs = "insert into weixin_commonshop_order_prices(user_id,exp_user_id,paystyle,batchcode,origin_price,price,NoExpPrice,ExpressPrice,CouponPrice,reward_money,AgentCostMoney,SupplyCostMoney,baseCostMoney,isvalid,createtime,total_vpscore,total_charitable,rcount,reward_score,agent_id,supply_id,pay_batchcode,needScore,currency,cardDiscount,recovery_time) values";

//插入客户使用代金劵的价格语句
$query_coupon  = "";
$query_coupons = "insert into weixin_commonshop_order_coupons(C_id,price,batchcode,isvalid,createtime) values";

//更新代金劵使用状态为已使用语句
$update_coupon = "";
$upCouponArr   = array();

//插入购物币使用情况语句
$sql_CAC  = "";
$sql_CACs = "insert into order_currencyandcoupon_t(pay_batchcode,currency,user_id,customer_id,coupon) values";

//插入vp值日志语句
$query_vp  = "";
$query_vps = "insert into weixin_commonshop_vp_logs(customer_id,user_id,type,batchcode,vp,status,remark,isvalid,createtime) values ";

//插入慈善公益插入日志语句
$sql_Ch	 = "";
$sql_Chs = "insert into charitable_log_t(customer_id,user_id,isvalid,createtime,batchcode,reward,charitable,paytype,type,supply_id) values";	

//插入发票语句
$sql_inv	 = "";
$sql_invs = "insert into order_invoice_t(batchcode,invoice_head) values";	

/*启身份证验证查询开始*/
if( 1 == $is_identity ){
	$identity_order = 1;
	$query_order_num = "SELECT count(1) as wcount FROM weixin_commonshop_orders where isvalid=true and identity_order=1 and identity='".$identity."' and year(createtime)=".$year." and month(createtime)=".$month." and day(createtime)=".$day;
	$result_order_num = mysql_query($query_order_num) or die('W176 Query_order_num failed: ' . mysql_error()); 
	$today_num = 0;
	while ($row_num = mysql_fetch_object($result_order_num)) {
		$today_num = $row_num->wcount;
	}
}
/*启身份证验证查询结束*/

$shopLen 				= count( $json_data );	//店铺数量
//file_put_contents ( "json_data.txt", "promoter_id====".var_export ( $json_data, true ) . "\r\n", FILE_APPEND );
$allShopReward_score	= 0;		//所有产品返还的积分
$allShopTotalprice		= 0;		//所有商品总价+总运费
$allShopTotalpriceNe	= 0;		//所有商品总价不+总运费
$batchcode_arr			= array();	//店铺订单数组
$store_id 				= -1;
$store_name 			= "";
$allPerNeedScore		= 0;		//所有产品需要总积分
$all_pro_weight			= 0;		//产品所有产品总重量
$all_orderNum			= 0;		//订单数

for ($i = 0; $i < $shopLen; $i++) {
	$opi_array 			= array(); //产品运费信息数组 
	$SupplyCostMoney	= 0;		//供应商的总供货价
	$baseCostMoney		= 0; 		//总成本价
	$NoExpPrice			= 0;	//单个店铺的订单总价（不+运费）
	$shopTotalprice		= 0;	//单个店铺的订单总价（+运费）
	$shopPerNeedScore	= 0;	//单个店铺的需要的总积分
	
	$order_id 			= -1;	//订单id
	$total_charitable	= 0; 	//总捐赠金额
	$s_t_consume_score 	= 0;	//总佣金
	$t_consume_score   	= 0;	//单个产品返佣总金额
	$reward_money       = 0;	//最终返佣总金额	
	$total_vpscore	 	= 0; 	//总vp值
	$is_Pinformation 	= 0; 	//必填信息产品开关
	$total_expressprice = 0;	//总邮费
	$shopReward_score 	= 0;	//店铺返还总积分
	$shopCurrency		= 0;	//店铺返还的购物币
	
	
	$shopId 			= $json_data[$i][0];		//店铺id
	$proNum 			= $json_data[$i][1];		//产品数组
	$remark 			= $json_data[$i][2];		//购买者备注
	$identity			= $json_data[$i][3];		//身份证号码
	$store_id			= $json_data[$i][4];		//门店id
	if( empty( $store_id ) ){
		$store_id = -1;
	}
	$store_name			= $json_data[$i][5];		//门店名字
	$invoice_head		= $json_data[$i][6];		//发票题头
	$shop_str			= 0;						//店铺证明
	if( $shopId > 0 ){
		$shop_str = $shopId;
	}
	
	$order_batchcode 	= $batchcode.$shop_str;//订单号
	$batchcode_arr[$i]["batchcode"]	= $order_batchcode;
	for($j = 0; $j < count($proNum); $j++) {	
		$totalprice 		= 0;				//单件产品金额：单价*数量		
		$isvp            	= 0;				//属于vp产品 1:是,0:否
		$panme            	= 0;				//产品名
		$isout          	= 0;				//是否下架.1:下架
		$is_QR          	= 0;				//票卷订单 是否 二维码产品 0:否 1:是
		$ccount				= 1;				//总数量
		$vp_score        	= 0; 				//单个产品可得vp值
		$pro_name       	= "";				//产品名称
		$for_price       	= "";				//产品成本价
		$now_price       	= "";				//产品现价
		$cost_price      	= "";				//产品供货价		
		$pro_reward      	= 0;				//产品总返佣比例
		$is_supply_id   	= -1;				//供应商user_id
		$pro_storenum    	= 0;				//产品的单个库存
		$perNeedScore 		= 0; 				//产品所需积分		
		$product_name		= "";				//发送客服信息的所以产品拼接名
		$reward_score		= "";				//返还积分
		$pos_price_id		= -1;				//属性价钱表id
		$product_name1		= "";				//发送客服信息的单个产品名
		$donation_rate 	 	= 0; 				//单品捐赠比率
		$agent_discount 	= 0;				//产品的单个代理商折扣		
		$Pcurrency 			= 0;				//单个产品返还的购物币
		$is_free_shipping 	= 0;				//是否包邮，1是，0否
		$p_is_identity 		= 0;				//产品是否需要身份证购买
		$freight_id 		= 0;				//运费模板id
		$express_type 		= 0;				//邮费计费方式:0没有选择，1按件数，2按按重量
	//	file_put_contents ( "json_data.txt",  var_export ( $proNum, true ) . "\r\n", FILE_APPEND );
		$proId				= $proNum[$j][0];		//产品id
		$Pos				= $proNum[$j][1];		//产品属性
		$rcount				= $proNum[$j][2];		//数量
		if( $rcount < 1){
			$rcount			= 1;
		}
		$exp_arr			= $proNum[$j][3];		//邮费详情数组
	//	$expId				= passport_decrypt((string)$expId);					//解密
		//file_put_contents ( "0926.txt", "postStr====".var_export (  $exp_arr, true ) . "\r\n", FILE_APPEND );

		$Pos_name			= $proNum[$j][5];		//属性名字符串
		$expId				= -1;
		$r_information		= $proNum[$j][4];		//必填信息数组

		
		//产品信息查询
		$query_pro = 'SELECT
						id,
						name,
						is_QR,
						weight,
						storenum,
						for_price,
						isout,
						name,
						now_price,
						need_score,
						cost_price,
						pro_reward,
						is_identity,
						is_supply_id,
						pro_discount,
						donation_rate,
						isvp,vp_score,
						back_currency,
						agent_discount,
						agent_discount,
						is_Pinformation,
						is_free_shipping,
						freight_id,
						express_type
						FROM weixin_commonshop_products where id=' . $proId;
		$result_pro = mysql_query($query_pro) or die('W437 Query failed: ' . mysql_error());
		while ($row_pro = mysql_fetch_object($result_pro)) {
			$isvp            	= $row_pro->isvp;
			$panme           	= $row_pro->name;
			$isout           	= $row_pro->isout;
			$is_QR           	= $row_pro->is_QR;
			$weight        		= $row_pro->weight;
			$pro_name        	= $row_pro->name;
			$p_is_identity		= $row_pro->is_identity;
			$vp_score        	= $row_pro->vp_score;
			$now_price       	= $row_pro->now_price;
			$for_price		 	= $row_pro->for_price;
			$pro_reward      	= $row_pro->pro_reward;
			$cost_price      	= $row_pro->cost_price;			
			$pro_storenum    	= $row_pro->storenum;
			$perNeedScore    	= $row_pro->need_score;
			$is_supply_id    	= $row_pro->is_supply_id;	//产品供应商ID
			$product_name1   	= "<".$pro_name.">";			
			$sell_discount   	= $row_pro->pro_discount;
			$donation_rate   	= $row_pro->donation_rate;
			$agent_discount  	= $row_pro->agent_discount;
			$is_Pinformation 	= $row_pro->is_Pinformation;
			$Pcurrency		 	= $row_pro->back_currency;
			$is_free_shipping	= $row_pro->is_free_shipping;
			$freight_id			= $row_pro->freight_id;
			$express_type		= $row_pro->express_type;
		}			
		if( $isout == 1 ){
			$json["status"] = 10010;
			$json["msg"] 	= $panme."已下架";
			$jsons			= json_encode($json);
			die($jsons);
		}
		
		//计算返还的购物币
		$shopCurrency	= $shopCurrency + ( $Pcurrency * $rcount );
		
		$product_name = $product_name.$product_name1;//提醒消息用的产品名称
		if( !empty( $Pos ) ){
			//属性不为空查属性信息
			$query_prva="select id,now_price,cost_price,storenum,need_score,for_price,weight from weixin_commonshop_product_prices where product_id=".$proId." and proids='".$Pos."'";
			$result_prva = mysql_query($query_prva) or die('w377 Query failed: ' . mysql_error());
			while ($row_prva = mysql_fetch_object($result_prva)) {
				$pos_price_id	= $row_prva->id;
				$weight			= $row_prva->weight;
				$now_price 		= $row_prva->now_price;
				$cost_price 	= $row_prva->cost_price;
				$for_price		= $row_prva->for_price;
				$pro_storenum 	= $row_prva->storenum;
				$perNeedScore 	= $row_prva->need_score;
			}
		}
		
		if( $pro_storenum < $rcount ){
			$json["status"] = 10005;
			$json["msg"] 	= "库存不足！";
			$jsons			= json_encode($json);
			die($jsons);
		}
		$all_pro_weight 		= $all_pro_weight + ( $weight * $rcount );
		$allShopTotalpriceNe	= $allShopTotalpriceNe + ( $now_price * $rcount );
		
		/*启身份证验证开始*/
		if( 1 == $is_identity and 1 == $p_is_identity){
			if($today_num >= $per_identity_num){
				$json["status"] = 10006;
				$json["msg"] = "每个身份证号每天最多只可".$per_identity_num."单";
				$jsons=json_encode($json);
				die($jsons);
			}
		}
		/*启身份证验证结束*/
		
		/*开启重量限制开始*/
		if( $is_weight_limit ){
			//查询当天 该身份证号/用户id 下单重量
			if( 1 == $is_identity and 1 == $p_is_identity){
				$query_order_weight = "SELECT sum(weight) as aweight FROM weixin_commonshop_orders where isvalid=true and identity_order=1 and status!=-1 and identity='".$identity."' and year(createtime)=".$year." and month(createtime)=".$month." and day(createtime)=".$day;	
				$errorMsg = "每个身份证号每天最多只可购买总重:".$per_weight_limit."KG的商品";	
			}else{
				$query_order_weight = "SELECT sum(weight) as aweight FROM weixin_commonshop_orders where isvalid=true and status!=-1 and user_id='".$user_id."' and year(createtime)=".$year." and month(createtime)=".$month." and day(createtime)=".$day;		   
				$errorMsg = "每个用户每天最多只可购买总重:".$per_weight_limit."KG的商品";	
			}

			$result_order_weight = mysql_query($query_order_weight) or die('W200 Query_order_weight failed: ' . mysql_error()); 
			$today_weight = 0;//已下单的总重量
			while ($row_weight = mysql_fetch_object($result_order_weight)) {
				$today_weight = $row_weight->aweight;
			}
			$todayAll_weight = $today_weight + $all_pro_weight;
			//判断最大下单数
			if($todayAll_weight > $per_weight_limit){
				$json["status"] = 10007;
				$json["msg"] = $errorMsg;
				$jsons=json_encode($json);
				die($jsons);
			}
		}
		/*开启重量限制结束*/
		
		/*开启购买金额限制开始*/
		if($is_cost_limit){
			//查询当天 该身份证号 下单总额
			if( 1 == $is_identity and 1 == $p_is_identity){
			//查询当天 该身份证号 下单总额
				$query_order_sun = "SELECT sum(totalprice) as wtotalprice,sum(expressPrice) as wexpressPrice FROM ( SELECT sum(orders.totalprice) as totalprice ,max(express.price) as expressPrice FROM weixin_commonshop_orders as orders LEFT JOIN weixin_commonshop_order_express_prices as express on orders.batchcode=express.batchcode where orders.isvalid=true and orders.status!=-1 and orders.identity_order=1 and orders.identity='".$identity."' and year(orders.createtime)=".$year." and month(orders.createtime)=".$month." and day(orders.createtime)=".$day." GROUP BY orders.batchcode ) a";		
				$errorMsg = "每个身份证号每天最多只可购买总额:".$per_cost_limit."元的商品";	
			}else{
				$query_order_sun = "SELECT sum(totalprice) as wtotalprice,sum(expressPrice) as wexpressPrice FROM ( SELECT sum(orders.totalprice) as totalprice ,max(express.price) as expressPrice FROM weixin_commonshop_orders as orders LEFT JOIN weixin_commonshop_order_express_prices as express on orders.batchcode=express.batchcode where orders.isvalid=true and orders.status!=-1 and orders.user_id='".$user_id."' and year(orders.createtime)=".$year." and month(orders.createtime)=".$month." and day(orders.createtime)=".$day." GROUP BY orders.batchcode ) a";
				$errorMsg = "每个用户每天最多只可购买总额:".$per_cost_limit."元的商品";
			}			
			$result_order_sum = mysql_query($query_order_sun) or die('W472 Query_order_sun failed: ' . mysql_error()); 
			$today_sum		= 0;  //已经下单总额
			$wtotalprice	= 0;  //已经下单产品总额
			$wexpressPrice	= 0;  //已经下单运费总额
			while ($row_num = mysql_fetch_object($result_order_sum)) {
				$wtotalprice = $row_num->wtotalprice;
				$wexpressPrice = $row_num->wexpressPrice;
				$today_sum = $wtotalprice;
			}
			//提交订单总额+已经下单总额
			$today_totalsum = $today_sum+$allShopTotalpriceNe;
			//判断最大下单数
			if($today_totalsum > $per_cost_limit){
				$json["status"] = 10008;
				$json["msg"] = $errorMsg;
				$jsons=json_encode($json);
				die($jsons);	
			}
		}
		/*开启购买金额限制结束*/
		
		/*开启买产品数量限制开始*/
		if($is_number_limit){
			if( 1 == $is_identity and 1 == $p_is_identity){
				$query_product_sun = "SELECT sum(rcount) as msum FROM weixin_commonshop_orders where isvalid=true and identity_order=1 and status!=-1 and  identity='".$identity."' and year(createtime)=".$year." and month(createtime)=".$month." and day(createtime)=".$day;
				
				$errorMsg = "每个身份证号每天最多只可购买:".$per_number_limit."件商品";	
			}else{
				$query_product_sun = "SELECT sum(rcount) as msum FROM weixin_commonshop_orders where isvalid=true and status!=-1 and user_id='".$user_id."' and year(createtime)=".$year." and month(createtime)=".$month." and day(createtime)=".$day;		
				
				$errorMsg = "每个用户每天最多只可购买:".$per_number_limit."件商品";	
			}
			$result_product_sum = mysql_query($query_product_sun) or die('W361 Query_product_sun failed: ' . mysql_error()); 
			$today_pro_num=0;  //已经下单产品总数
			while ($row_pro_num = mysql_fetch_object($result_product_sum)) {
				$today_pro_num = $row_pro_num->msum;
			}
			//提交订单产品数量+已经下单产品数量
			$today_pro_num = $today_pro_num+$rcount;
			//判断最大下单产品数
			if($today_pro_num > $per_number_limit){
				$json["status"] = 10009;
				$json["msg"] = $errorMsg;
				$jsons=json_encode($json);
				die($jsons);
			}
		}		
		/*开启买产品数量限制结束*/
		
		//判断是否走代理商路线----start
		if($agent_id > 0){
			
			//如果产品的代理折扣没有设置,则用代理商原来的折扣
			if($agent_discount == 0){	
				$query2 = "select agent_discount from weixin_commonshop_applyagents where status=1 and isvalid=true and user_id=" . $final_agent_id; //查找代理商代理剩余库存金额
				$result2 = mysql_query($query2) or die('w288 Query failed: ' . mysql_error());
				while ($row2 = mysql_fetch_object($result2)) {
					$agent_discount = $row2->agent_discount;
				}
			}
			$agent_discount = $agent_discount / 100;
			
			$AgentCostMoney = $AgentCostMoney + $now_price * $rcount * $agent_discount;
			if ($is_distribution == 1 and $is_QR == 0 ) { //判断是否开启代理模式和代理费是否少于成本
				$agentcont_type = 1;
			}
		} 
		//判断是否走代理商路线--- end
		
		/*供应商成本价开始*/
		$final_supply_id 			= -1;
		$Supply_OnlyCostMoney 		= 0; //供应商的单个产品总成本价
		if ($is_supply_id > 0) {
			$final_supply_id 		= $is_supply_id;		//最终供应商ID
			$Supply_OnlyCostMoney 	= $cost_price * $rcount; //供应商的单个产品总供货价
			$SupplyCostMoney 		= $SupplyCostMoney + $Supply_OnlyCostMoney; //供应商的总成本价
		}
		$baseCostMoney_only 		= $for_price * $rcount;//单个产品总成本
		if($baseCostMoney_only < $Supply_OnlyCostMoney){
			$baseCostMoney_only 	= $Supply_OnlyCostMoney;
		}
		$baseCostMoney = $baseCostMoney+$baseCostMoney_only;
		/*供应商成本价结束*/
		
		/* 产品总价 (不包括运费) 未减代金券 */
		
		$totalprice			= $now_price * $rcount;			//现价*数量
		$perNeedScore		= $perNeedScore * $rcount;		//产品所需积分
		$shopPerNeedScore	= $shopPerNeedScore + $perNeedScore;
		$allPerNeedScore	= $allPerNeedScore + $perNeedScore;
		if( 0 < $vp_score && 1 == $isvp ){
			$vp_score   = $vp_score * $rcount;			//单个产品总vp值
		}
		$total_vpscore  = $total_vpscore + $vp_score; 	//总vp值
		
		
		//计算返多少积分
		$reward_score = $totalprice * $consume_score;//取整
		$shopReward_score = $shopReward_score + $reward_score;
		
		/*慈善公益算钱开始*/
		$charitable = 0;
		if($is_charitable){
			if( $donation_rate < $charitable_propotion ){
				$donation_rate = $charitable_propotion ;
			}
			$charitable = $totalprice * $donation_rate;
			$charitable = round($charitable,2);
			$total_charitable = $total_charitable + $charitable;
		}
		/*慈善公益算钱结束*/
		
		//总价*会员卡折扣
		if( $card_member_id > 0 and $is_select_card > 0 ){
			if ($card_discount == 0) {
				$card_discount = 100;
			} 
			if($totalprice< $min_num){
				$card_discount = 100;
			}
			if($card_discount>0){
				$totalprice = $totalprice * $card_discount / 100;  //会员卡折扣打折后总价
			}
		}

//file_put_contents ( "log0720.txt", "promoter_id====".var_export ( $promoter_id, true ) . "\r\n", FILE_APPEND );
//file_put_contents ( "log0720.txt", "sell_discount====".var_export ( $sell_discount, true ) . "\r\n", FILE_APPEND );
		if($promoters_ie > 0 && $sell_discount > 0){
			$totalprice = $totalprice * $sell_discount / 100;  //会员卡折扣打折后总价
		//	file_put_contents ( "log0720.txt", "totalprice1111====".var_export ( $totalprice, true ) . "\r\n", FILE_APPEND );
		}
	//	file_put_contents ( "log0720.txt", "totalprice====".var_export ( $totalprice, true ) . "\r\n", FILE_APPEND );
		$totalprice = round($totalprice,2);
		$NoExpPrice = $NoExpPrice + $totalprice;	
		/* 产品总价 (不包括运费) 未减代金券 end */			
		
		
		
		//查找快递名  2016-08-26
		/*$expressname = "没选择快递";
		if( $expId > 0 ){
			$exp = "select name from weixin_expresses where id=".$expId;
			$result_exp = mysql_query($exp) or die('w377 Query failed: ' . mysql_error());
			while ($row_exp = mysql_fetch_object($result_exp)) {
				$expressname 	= $row_exp->name;
			}
		}
		if( $store_id <0 and $expId > 0 ){
			$sendstyle = "快递";
		}else{
			$sendstyle = "自提";
		}*/
		if($store_id>0){
			$sendstyle = "自提";
		}
		
		//拼接插入语句
		$sql_order .= "(
			'" . $fromuser_app . "',
			"  . $user_id . ",
			"  . $proId . ",
			'"  . $panme . "',
			"  . $rcount . ",
			"  . $totalprice . ",
			'" . $remark . "',
			true,
			now(),
			0,
			"  . $customer_id . ",
			'" . $paystyle . "',
			'" . $sendstyle . "',
			'" . $Pos . "',
			'" . $Pos_name . "',
			'" . $sendtime . "',
			"  . $expId . ",
			'" . $order_batchcode . "',
			'" . $batchcode . "',
			'" . $expressname . "',
			"  . $card_member_id . ",
			"  . $d_id . ",
			"  . $exp_user_id . ",
			"  . $perNeedScore . ",
			"  . $final_supply_id . ",
			"  . $final_agent_id . ",
			"  . $agentcont_type . ",
			'" . $identity . "',
			'" . $weight . "',
			'" . $is_QR . "',
			"  . $is_payother . ",
			'"  . $store_id . "',
			'" . $store_name . "',
			"  . $AgentCostMoney.",
			"  . $Supply_OnlyCostMoney . ",
			"  . $baseCostMoney_only.",
			"  . $identity_order . ",
			"  . $reward_score . ",
			"  . $charitable . "),";
		
		//插入库存回收表
		$sql_SR .="('" . $order_batchcode . "'," . $proId . ",".$pos_price_id.",".$rcount.",'".$recovery_time."',".$customer_id."),";
		
		//减库存
		if( $pos_price_id == -1){
			$sql_storenum = "update weixin_commonshop_products set storenum= storenum-".$rcount." where id=".$proId.";";	
		}else{
			$sql_storenum = "update weixin_commonshop_product_prices set storenum = storenum-".$rcount." where id=".$pos_price_id.";";	
		}
		array_push($sql_storeArr,$sql_storenum);
		if($promoter_id > 0 && $sell_discount > 0){
				$init_reward = $init_reward_again;
		}
		
		//如果产品的总返佣比例有设置,则用产品里面的总分佣比例
		if( $pro_reward == -1 ){
			$pro_reward = $init_reward;
		} 
		//计算订单佣金
		if($issell){
			$allcost_price 		=  $cost_price* $rcount;									//总供货价
			$allfor_price 		=  $for_price* $rcount;                       			//总成本价
			$cost_sell_price 	= $totalprice - $allcost_price;					//最多能分的佣金， 总价减去总供货价
			$s_t_consume_score 	= $pro_reward * ($totalprice -$allfor_price);	//计算订单返佣总金：（总价-总成本价）*比例
			if($s_t_consume_score>$cost_sell_price){
				  $s_t_consume_score = $cost_sell_price;							//如果佣金大于最多能分的佣金，则以最多能分的佣金为准
			}
			$t_consume_score = $t_consume_score + $s_t_consume_score;
			$t_consume_score = round($t_consume_score,2);
		}
		$reward_money = $t_consume_score;	
		
		/*单个产品运费数组开始*/
		/*if ( $expId > 0 and $is_free_shipping == 0 and $store_id < 0) {
			$Pexp = array();
			array_push($Pexp,$weight,$rcount,$expId);
			array_push($opi_array,$Pexp);
		}*/
		
		/*单个产品运费数组结束*/	
		
		//产品必填信息---start
		if( $is_Pinformation == 1 and $total_is_Pinformation == 1 ){
			for( $p = 0; $p < count($r_information); $p++){
				$information_head = $r_information[$p][0];
				$information_con  = mysql_real_escape_string($r_information[$p][1]);
				$sql_RI .= "  (
				".$proId.",
				now(),
				true,
				".$order_batchcode.",
				'".$information_head."',
				'".$information_con."',
				".$customer_id.
				"),";
			}
		}
		//产品必填信息---end
		
		
		/*新版运费数组开始*/
						
		if($is_free_shipping == 0 and $store_id < 0){
			$pro_express_temp = pro_express_template($freight_id,$express_type,$totalprice,$customer_id,$shopId,$location_p,1);
			//var_dump($pro_express_temp);
			$tem_id 		= $pro_express_temp[0];			//运费模板
			$temp_product_express = array($tem_id,$weight,$rcount,$totalprice,$express_type);
			array_push($opi_array ,$temp_product_express);
		}
		
		/*新版运费数组结束*/
		
	}
	//产品循环结束
	
	/*计算运费开始*/
	/*if( !empty( $opi_array ) ){
		$total_expressprice = $shopmessage->New_change_freight($opi_array,$customer_id,$final_supply_id);
	}
	//用于下单发客服消息
	$expressprice_str = $total_expressprice."元";
	if($total_expressprice==0){
		$expressprice_str = "免邮";
	}*/
	/*计算运费结束*/
	
	/*新版计算运费开始*/
	
	//计算同一个供应商下的同一个运费模板下的产品，累计重量，件数，金额在筛选出快递规则
	//var_dump($opi_array);
	if( !empty( $opi_array ) ){
		$rtn_express_tem_arr = pro_express_new($opi_array,$customer_id,$location_p,$final_supply_id);	//获取供应商产品的的最优快递	
		//var_dump($rtn_express_tem_arr);
		$total_expressprice = 0;		
		if($rtn_express_tem_arr!='failed'){	
			$shop = new shopMessage_Utlity();		
			//计算单个供应商的所有运费
			$total_expressprice = $shop->New_change_freight_direct($rtn_express_tem_arr,$customer_id,$final_supply_id);		
		}
	}
	//var_dump($total_expressprice);	
	//用于下单发客服消息
	$expressprice_str = $total_expressprice."元";
	if($total_expressprice==0){
		$expressprice_str = "免邮";
	}	
	/*新版计算运费结束*/
	
	
	//添加到邮费表
	$sql_EP .= "('" . $order_batchcode . "'," . $total_expressprice . ",true,now()),";
	
	//产品总价加上运费
	//echo "total_expressprice=".$total_expressprice;
	$shopTotalprice 	= $NoExpPrice + $total_expressprice; //有邮费的订单总额
	//echo "shopTotalprice=".$shopTotalprice;
	$allShopTotalprice	= $allShopTotalprice + $shopTotalprice;
	
	//添加日志记录
	$query_log .= "('".$order_batchcode."',0,'用户下单','".$fromuser."',now(),1),";
	
	//插入订单收货地址---start
	$diy_area_areaname = "";
	if($diy_area_id>0){
		$Query_diyArea = "SELECT areaname from weixin_commonshop_team_area WHERE isvalid = true and customer_id = ".$customer_id." and grade = 3 and id=".$diy_area_id." limit 1";
		$Result_diyArea = mysql_query($Query_diyArea) or die (" W1086 : Query_diyArea failed : ".mysql_error());
		while ($row_diyArea = mysql_fetch_object($Result_diyArea)) {	
			$diy_area_areaname    =  $row_diyArea->areaname;
		}	
	}
	$sql_address .= "('" . $order_batchcode . "','" . $name . "','" . $phone . "','" . mysql_real_escape_string( $address ) . "','" . $location_p . "','" . $location_c . "','" . $location_a . "','" . $identity . "'," . $diy_area_id . ",'" . $diy_area_areaname . "','".$identityimgt."','".$identityimgf."'),";
	//插入订单收货地址---end
	
	$shopReward_score = floor( $shopReward_score );
	$batchcode_arr[$i]["reward_money"]	= $reward_money;
	$batchcode_arr[$i]["shopCurrency"]	= $shopCurrency;
	$sql_OP .= "(".$user_id.",".$exp_user_id.",'".$paystyle."','" . $order_batchcode . "'," . $shopTotalprice . "," . $shopTotalprice . "," . $NoExpPrice . "," . $total_expressprice . ",0,".$reward_money.",".$AgentCostMoney.",".$SupplyCostMoney.",".$baseCostMoney.",true,now(),".$total_vpscore.",".$total_charitable.",".$ccount.",".$shopReward_score.",".$final_agent_id.",".$final_supply_id.",".$batchcode.",".$shopPerNeedScore.",".$shopCurrency.",".$is_select_card.",'"  . $recovery_time . "'),";
	
	//插入代金劵使用信息----start
	if($C_id>0){
		//插入客户使用代金劵的价格
		$query_coupon .= "(".$C_id.",".$C_Money.",'".$batchcode."',1,now()),";
		//更新代金劵使用状态为已使用
		$update_coupon = "update weixin_commonshop_couponusers set is_used= 1 where id=" . $C_id." and user_id=".$user_id." and customer_id=".$customer_id;
		//echo $update_coupon;	
		array_push($upCouponArr,$update_coupon);
	}
	//插入代金劵使用信息----end
	
	/*购物币大于0或代金卷金额会插入订单购物币开始*/
	if( $user_currency > 0 or $C_Money > 0 ){
		$sql_CAC .= "('".$batchcode."',".$user_currency.",".$user_id.",".$customer_id.",".$C_Money."),";
	}
	/*购物币大于0或代金卷金额会插入订单购物币结束*/ 
	
	/*vp值日志开始*/
	if( 0 < $total_vpscore ){
		$remark = "来源于下单得到VP值";
		$query_vp .= "(".$customer_id.",".$user_id.",1,'".$order_batchcode."',".$total_vpscore.",0,'".$remark."',true,now()),"; 
	}
	/*vp值日志结束*/
	
	/*慈善公益插入日志开始*/
	if($is_charitable){
		$total_charitable_s = 0;
		if( 0 < $integration_price ){
			$total_charitable_s = $total_charitable/$integration_price;//慈善分
		}
		$sql_Ch .= "(".$customer_id.",".$user_id.",true,now(),".$order_batchcode.",".$total_charitable.",".$total_charitable_s.",-1,0,".$final_supply_id."),";
	}
	/*慈善公益插入日志结束*/
	
	if ( $is_supply_id > 0 ) {
		$content = "亲，您有一笔新订单，请及时发货\n\n订单：".$batchcode."\n顾客：".$weixin_name."\n商品：".$product_name."\n金额：".$shopTotalprice."元\n时间：<".$stringtime.">";
		
		$send_supply[$i]["is_supply_id"] 	= $is_supply_id;
		$send_supply[$i]["content"]			= $content;
	}
	//发票
	if( !empty( $invoice_head ) ){
		$sql_inv .= "('".$order_batchcode."','".$invoice_head."'),";
	}
}	

/*orders表语句运行开始*/
if( !empty( $sql_order ) ){
	$sql_orders .= $sql_order;
	$sql_orders  = rtrim($sql_orders,',');
//	file_put_contents ( "json_data.txt", "paystyle====".var_export ( $sql_orders, true ) . "\r\n", FILE_APPEND );
	mysql_query($sql_orders) or die('sql_orders Query failed: ' . mysql_error());
}
/*orders表语句运行结束*/

/*库存回收语句运行开始*/
if( !empty( $sql_SR ) ){
	$sql_SRs .= $sql_SR;
	$sql_SRs  = rtrim($sql_SRs,',');
	mysql_query($sql_SRs) or die('sql_SRs Query failed: ' . mysql_error());
}
/*库存回收语句运行结束*/

/*减库存语句运行开始*/
if( !empty( $sql_storeArr ) ){
	$storenLen	= count($sql_storeArr);
	for($j = 0; $j < $storenLen; $j++) {
		//echo $sql_storeArr[$j];
		mysql_query($sql_storeArr[$j]) or die('sql_storeArr Query failed: ' . mysql_error());
	}
}
/*减库存语句运行结束*/

/*邮费表语句运行开始*/
if( !empty( $sql_EP ) ){
	$sql_EPs .= $sql_EP;
	$sql_EPs  = rtrim($sql_EPs,',');
	mysql_query($sql_EPs) or die('sql_EPs Query failed: ' . mysql_error());
}
/*邮费表语句运行结束*/

/*运行必填信息语句开始*/
if( !empty( $sql_RI ) ){
	$sql_RIs .= $sql_RI;
	$sql_RIs  = rtrim($sql_RIs,',');
	mysql_query($sql_RIs) or die('sql_RI Query failed: ' . mysql_error());
}
/*运行必填信息语句结束*/

/*订单状态日志表语句运行开始*/
if( !empty( $query_log ) ){
	$query_logs .= $query_log;
	$query_logs  = rtrim($query_logs,',');
	mysql_query($query_logs) or die('query_logs Query failed: ' . mysql_error());
}
/*订单状态日志表语句运行结束*/

/*订单地址语句开始*/
if( !empty( $sql_address ) ){
	$sql_addresss .= $sql_address;
	$sql_addresss  = rtrim($sql_addresss,',');
	mysql_query($sql_addresss) or die('sql_addresss Query failed: ' . mysql_error());
}
/*订单地址语句结束*/

/*order_prices语句运行开始*/
if( !empty( $sql_OP ) ){
	$sql_OPs .= $sql_OP;
	$sql_OPs  = rtrim($sql_OPs,',');
	mysql_query($sql_OPs) or die('sql_OPs Query failed: ' . mysql_error());
}
/*order_prices语句运行结束*/

/*使用代金卷运行开始*/
//echo $query_coupon;return;
if( !empty( $query_coupon ) ){
	$query_coupons .= $query_coupon;
	$query_coupons  = rtrim($query_coupons,',');
	mysql_query($query_coupons) or die('query_coupons Query failed: ' . mysql_error());
	
	/*更改代金卷状态运行开始*/
	$couponLen	= count($upCouponArr);
	for($j = 0; $j < $couponLen; $j++) {
		mysql_query($upCouponArr[$j]) or die('upCouponArr Query failed: ' . mysql_error());
	}
	/*更改代金卷状态运行结束*/
}
/*使用代金卷运行结束*/


/*购物币使用语句运行开始*/
if( !empty( $sql_CAC ) ){
	$sql_CACs .= $sql_CAC;
	$sql_CACs  = rtrim($sql_CACs,',');
	mysql_query($sql_CACs) or die('sql_CACs Query failed: ' . mysql_error());
}
/*购物币使用语句运行结束*/

/*VP语句运行开始*/
if( !empty( $query_vp ) ){
	$query_vps .= $query_vp;
	$query_vps		= rtrim($query_vps,',');
	mysql_query($query_vps) or die('query_vps Query failed: ' . mysql_error());
}
/*VP语句运行结束*/

/*慈善公益插入日志语句开始*/
if( !empty( $sql_Ch ) ){
	$sql_Chs .= $sql_Ch;
	$sql_Chs		= rtrim($sql_Chs,',');
	mysql_query($sql_Chs) or die('sql_Chs Query failed: ' . mysql_error());
}
/*慈善公益插入日志语句结束*/


/*保存订单结束*/
//var_dump( $send_supply );return;
/*发票开始*/
if( !empty( $sql_inv ) ){
	$sql_invs .= $sql_inv;
	$sql_invs  = rtrim($sql_invs,',');
	mysql_query($sql_invs) or die('sql_invs Query failed: ' . mysql_error());

}
/*发票结束*/




/*发送客服信息开始*/
if ($exp_user_id > 0) {
	//发送客服信息给上线
	$query = "select weixin_fromuser from weixin_users where id=" . $exp_user_id." limit 0,1";
	$result = mysql_query($query) or die('W579 Query failed: ' . mysql_error());
	$parent_fromuser = "";
	while ($row = mysql_fetch_object($result)) { 
		$parent_fromuser = $row->weixin_fromuser;
		break;
	}

	$promoter_id = -1;
	$query = "select id from promoters where isvalid=true and status=1 and user_id=" . $exp_user_id;
	$result = mysql_query($query) or die('W588 Query failed: ' . mysql_error());
	while ($row = mysql_fetch_object($result)) { 
		$promoter_id = $row->id;
		break;
	}
	if($exp_user_id!=$user_id && $reward_money > 0 ){	//如果开启复购,则上级是自己,  若上级是自己,则不推送消息
		$content2 = "亲,您的好友下了一笔订单\n\n昵称：".$weixin_name."\n商品：".$product_name."\n时间：<".$stringtime.">\n\n支付完成后，您将获得：佣金！";
		if ($promoter_id < 0) {
			$content2 = "亲,您的好友下了一笔订单\n\n昵称：".$weixin_name."\n商品：".$product_name."\n时间：<".$stringtime.">\n若您成为".$exp_name.",支付完成后，您将获得：佣金！";
		}	
	}
	 
	$shopmessage->SendMessage($content2, $parent_fromuser, $customer_id);
	
}
/*发送客服信息结束*/

//添加到顾客表-------start
$query = "select id from weixin_commonshop_customers where isvalid=true and user_id=" . $user_id . " and customer_id=" . $customer_id . " limit 0,1";
$result = mysql_query($query) or die('W754 Query failed: ' . mysql_error());
$shop_customer_id = -1;
while ($row = mysql_fetch_object($result)) {
    $shop_customer_id = $row->id;
    break; 
}
if ($shop_customer_id < 0) {
    $sql = "insert into weixin_commonshop_customers(user_id,customer_id,isvalid,createtime) values(" . $user_id . "," . $customer_id . ",true,now())";
    mysql_query($sql);
}
//添加到顾客表-------end

//	下单后锁定开始---------start
if($distr_type==1){
	$sql = "update weixin_users set is_lock=1 where customer_id=".$customer_id." and id=".$user_id;
	mysql_query($sql);
}
//	下单后锁定开始---------end

//代金卷减钱
$allShopTotalprice = $allShopTotalprice - $C_Money;

//如果支付状态是 代付 则插入代付信息
if( $pay_immed == 2 && $paystyle == "找人代付" ){
	$payother_desc_id = -1;
	$sql="insert into weixin_commonshop_otherpay_descs(user_id,batchcode,pay_desc,isvalid,createtime) values(".$user_id.",'".$order_batchcode."','".$payother_desc."',true,now())";
	mysql_query($sql);
	$payother_desc_id = mysql_insert_id();
	$json["payother_desc_id"] = $payother_desc_id;
	
	$json["status"] 			= 1;
	$json["batchcode"] 			= $batchcode;
	$json["payother_desc_id"]	=  $payother_desc_id;
	$json["msg"] 				= "提交成功";
	$json["remark"]				= "找人代付";
	$jsons=json_encode($json);
	die($jsons);
		
}


$remain_score = 0;
if( $allPerNeedScore > 0 ){
	//查询剩余积分
	$query2 = "select remain_score from weixin_card_member_scores where isvalid=true and card_member_id=" . $card_member_id;
	$result2 = mysql_query($query2) or die('查询剩余积分 Query failed: ' . mysql_error());
	while ($row2 = mysql_fetch_object($result2)) {
		$remain_score = $row2->remain_score;
	}
	if( $allPerNeedScore > $remain_score ){
		$json["status"] 	= 10010;
		$json["msg"] 		= "提交成功\n会员卡积分不足请尽快支付";
		$json["batchcode"] 	= $batchcode;
		$json["remark"]		= "积分不足";
		$jsons=json_encode($json);
		die($jsons);
	}
	if( $allShopTotalprice == 0 ){
		$remark = "商2城购买消耗积分:" . $allPerNeedScore." 订单号:".$batchcode;
		$sql = "insert into weixin_card_score_records(card_member_id,new_record,before_score,score,after_score,remark,type,isvalid,createtime) select  card_member_id,1,remain_score,".-$allPerNeedScore.",remain_score=".$allPerNeedScore.",'".$remark."',11,true,now() from weixin_card_member_scores where isvalid=true and card_member_id=".$card_member_id;

		mysql_query($sql)or die('插入扣除积分日志 Query failed: ' . mysql_error());	

		$sql = "update weixin_card_member_scores set remain_score=remain_score-" . $allPerNeedScore . ",consume_score=consume_score+".$allPerNeedScore." where card_member_id=" . $card_member_id;
		mysql_query($sql) or die('扣除积分 Query failed: ' . mysql_error());
		
		
		$sql = "update weixin_commonshop_orders set paystatus=1,paystyle='积分支付',paytime=now()  where isvalid=true and pay_batchcode='" . $batchcode . "'";
		mysql_query($sql) or die('扣除积分3 Query failed: ' . mysql_error());
		
		$sql = "update weixin_commonshop_order_prices set paystatus=1,paystyle='积分支付',paytime=now()  where isvalid=true and pay_batchcode='" . $batchcode . "'";
		mysql_query($sql) or die('扣除积分7 Query failed: ' . mysql_error());
		
		
		$callBackBatchcode = $batchcode.$currency_id;//系统生成回调订单号
		$query = "insert into paycallback_t(createtime,isvalid,customer_id,pay_batchcode,callBackBatchcode,price,payClass) values(now(),true,".$customer_id.",".$batchcode.",".$callBackBatchcode.",".$allShopTotalprice.",7)";
		mysql_query($query) or die('扣除积分4 Query failed: ' . mysql_error());
		
		$shopmessage= new shopMessage_Utlity();
		//$shopmessage->set_promot_mode($customer_id,$pay_batchcode,$user_id,$exp_user_id,$fromuser);
		$content3 = "亲，您的会员卡积分支付 -".$allPerNeedScore."\r\n".
					"来源：【会员卡积分】\n"."状态：【支付成功】\n".
					"余额：".($remain_score-$allPerNeedScore)."元\n".
				"时间：<".date( "Y-m-d H:i:s").">";
		
		$shopmessage->SendMessage($content3,$fromuser,$customer_id,1,$batchcode,1);
		
		
		for($m = 0; $m < count($batchcode_arr); $m++) {	
			$query_log = "insert into weixin_commonshop_order_logs(batchcode,operation,descript,operation_user,createtime,isvalid) 
				values('".$batchcode_arr[$m]["batchcode"]."',2,'订单支付 － 积分支付','".$fromuser."',now(),1)";
			mysql_query($query_log) or die('扣除积分5 Query failed: ' . mysql_error());
			
			$shopmessage->GetMoney_Common($batchcode_arr[$m]["batchcode"],$customer_id,$batchcode_arr[$m]["reward_money"],$user_id,$exp_user_id,0,-1,0,$card_member_id,$batchcode_arr[$m]["shopCurrency"],$user_currency);//分佣
			$shopmessage->GetTicket($http_host, $batchcode_arr[$m]["batchcode"]);//小票打印机
			
			if($is_QR){	
				$GetQR = $shopmessage->GetQR($batchcode_arr[$m]["batchcode"],$fromuser,$customer_id);
				
				$query_log = "insert into weixin_commonshop_order_logs(batchcode,operation,descript,operation_user,createtime,isvalid) 
				values('".$batchcode_arr[$m]["batchcode"]."',7,'用户确认收货','".$fromuser."',now(),1)";
				mysql_query($query_log) or die('会员卡余额8 Query failed: ' . mysql_error());
				
				$sql_qr="update weixin_commonshop_orders set sendstatus = 2 where batchcode='".$batchcode_arr[$m]["batchcode"]."'"; 
				mysql_query($sql_qr) or die('扣除积分6 Query failed: ' . mysql_error());	
				
				$sql = "update weixin_commonshop_order_prices set sendstatus=2,confirm_sendtime=now() where isvalid=true and batchcode='".$batchcode_arr[$m]["batchcode"]."'"; 
				mysql_query($sql) or die('扣除积分9 Query failed: ' . mysql_error());
			}
			
			$sql = "delete FROM stockrecovery_t WHERE batchcode='".$batchcode_arr[$m]["batchcode"]."'"; 
			mysql_query($sql) or die('扣除积分7 Query failed: ' . mysql_error());	
		}
		
		$send_supplyLen	= count( $send_supply );	
		for ($i = 0; $i < $send_supplyLen; $i++) {	
			$supply_id 	= $send_supply[$i]["is_supply_id"];
			$content 	= $send_supply[$i]["content"];
			$query = "select weixin_fromuser from weixin_users where isvalid=true and customer_id=".$customer_id." and id=" . $supply_id . " limit 0,1";
			$result = mysql_query($query) or die('W603 Query failed: ' . mysql_error());
			$supply_fromuser = "";
			while ($row = mysql_fetch_object($result)) {
				$supply_fromuser = $row->weixin_fromuser;
				break; 
			}
			$shopmessage->SendMessage($content, $supply_fromuser, $customer_id);
		}
		
		$json["status"] 	= 5;
		$json["msg"] 		= "积分支付成功";
		$json["batchcode"]	= $batchcode;
		$json["remark"]		= "";
		$jsons=json_encode($json);
		die($jsons);
	}
}

//echo "allShopTotalprice2=".$allShopTotalprice;
/*购物币支付开始*/
if( $user_currency > 0 and $paystyle != "暂不支付"  ){		
	if(  $user_currency >= $allShopTotalprice ){		
		//插入购物币消费日志
		$sql = "insert into weixin_commonshop_currency_log(isvalid,customer_id,user_id,cost_money,cost_currency,after_currency,batchcode,status,type,class,remark,createtime) select true,".$customer_id.",".$user_id.",".$allShopTotalprice.",".$allShopTotalprice.",currency-".$allShopTotalprice.",".$batchcode.",1,0,1,'商城购物消费',now() from weixin_commonshop_user_currency  where isvalid=true and user_id=" . $user_id;
		mysql_query($sql) or die('购物币支付2 Query failed: ' . mysql_error());
		
		//支付币大于支付金额时扣除购物币，订单改成支付状态
		$sql = "update weixin_commonshop_user_currency set currency=currency-".$allShopTotalprice." where isvalid=true and user_id=" . $user_id;
		//echo $sql;
		mysql_query($sql) or die('购物币支付1 Query failed: ' . mysql_error());
		
		$sql = "update weixin_commonshop_orders set paystatus=1,paystyle='购物币支付',paytime=now()  where isvalid=true and pay_batchcode='" . $batchcode . "'";
		mysql_query($sql) or die('购物币支付3 Query failed: ' . mysql_error());
		
		$sql = "update weixin_commonshop_order_prices set paystatus=1,paystyle='购物币支付',paytime=now()  where isvalid=true and pay_batchcode='" . $batchcode . "'";
		mysql_query($sql) or die('购物币支付7 Query failed: ' . mysql_error());
		
		
		$callBackBatchcode = $batchcode.$currency_id;//系统生成回调订单号
		$query = "insert into paycallback_t(createtime,isvalid,customer_id,pay_batchcode,callBackBatchcode,price,payClass) values(now(),true,".$customer_id.",".$batchcode.",".$callBackBatchcode.",".$allShopTotalprice.",1)";
		mysql_query($query) or die('购物币支付4 Query failed: ' . mysql_error());
		
		//添加订单日志 － 支付
		for($m = 0; $m < count($batchcode_arr); $m++) {	
			$query_log = "insert into weixin_commonshop_order_logs(batchcode,operation,descript,operation_user,createtime,isvalid) 
				values('".$batchcode_arr[$m]["batchcode"]."',2,'订单支付 － ".$paystyle."','".$fromuser."',now(),1)";
			mysql_query($query_log) or die('购物币支付5 Query failed: ' . mysql_error());
			
			$shopmessage->GetMoney_Common($batchcode_arr[$m]["batchcode"],$customer_id,$batchcode_arr[$m]["reward_money"],$user_id,$exp_user_id,0,-1,$allPerNeedScore,$card_member_id,$batchcode_arr[$m]["shopCurrency"],$user_currency);//分佣
			$shopmessage->GetTicket($http_host, $batchcode_arr[$m]["batchcode"]);//小票打印机
			if($is_QR){	
				$GetQR = $shopmessage->GetQR($batchcode_arr[$m]["batchcode"],$fromuser,$customer_id);
				
				$query_log = "insert into weixin_commonshop_order_logs(batchcode,operation,descript,operation_user,createtime,isvalid) 
				values('".$batchcode_arr[$m]["batchcode"]."',7,'用户确认收货','".$fromuser."',now(),1)";
				mysql_query($query_log) or die('会员卡余额8 Query failed: ' . mysql_error());

				$sql_qr="update weixin_commonshop_orders set sendstatus = 2 where batchcode='".$batchcode_arr[$m]["batchcode"]."'"; 
				mysql_query($sql_qr) or die('购物币支付6 Query failed: ' . mysql_error());	
				
				$sql = "update weixin_commonshop_order_prices set sendstatus=2,confirm_sendtime=now() where isvalid=true and batchcode='".$batchcode_arr[$m]["batchcode"]."'"; 
				mysql_query($sql) or die('购物币支付9 Query failed: ' . mysql_error());
			}
			//删除库存回收信息
			$sql = "delete FROM stockrecovery_t WHERE batchcode='".$batchcode_arr[$m]["batchcode"]."'"; 
			mysql_query($sql) or die('购物币支付7 Query failed: ' . mysql_error());	
		}
		$shopmessage= new shopMessage_Utlity();
		//$shopmessage->set_promot_mode($customer_id,$pay_batchcode,$user_id,$exp_user_id,$fromuser);
		$content3 = "亲，您的".$custom."支付 -".$allShopTotalprice."元\r\n".
					"来源：【".$custom."】\n"."状态：【支付成功】\n".
					"余额：".($currency-$allShopTotalprice)."元\n".
				"时间：<".date( "Y-m-d H:i:s").">";
		
		$shopmessage->SendMessage($content3,$fromuser,$customer_id,1,$batchcode,1);
		$shopmessage->send_template_message($customer_id,$batchcode,1,2);
		
		$send_supplyLen	= count( $send_supply );	
		for ($i = 0; $i < $send_supplyLen; $i++) {	
			$supply_id 	= $send_supply[$i]["is_supply_id"];
			$content 	= $send_supply[$i]["content"];
			$query = "select weixin_fromuser from weixin_users where isvalid=true and customer_id=".$customer_id." and id=" . $supply_id . " limit 0,1";
			$result = mysql_query($query) or die('W603 Query failed: ' . mysql_error());
			$supply_fromuser = "";
			while ($row = mysql_fetch_object($result)) {
				$supply_fromuser = $row->weixin_fromuser;
				break; 
			}
			$shopmessage->SendMessage($content, $supply_fromuser, $customer_id);
		}
				
		$json["status"] 	= 2;
		$json["msg"] 		= "提交支付成功";
		$json["remark"]		= "购物币支付";
		$json["batchcode"]	= $batchcode;
		$jsons=json_encode($json);
		die($jsons);
	}else{
		$allShopTotalprice = $allShopTotalprice - $user_currency;
	}
}
/*购物币支付结束*/

/*钱包零钱支付开始*/
if( $paystyle == "零钱支付" ){
	$query 		= "select id,balance from moneybag_t where isvalid=true and user_id=" . $user_id;
	$result 	= mysql_query($query) or die('零钱支付1 Query failed: ' . mysql_error());
	$balance 		= 0;//钱包零钱
	$moneybag_id	= 0;//钱包零钱id
	while ($row = mysql_fetch_object($result)) {
		$moneybag_id	= $row->id;
		$balance 		= $row->balance;
	}
	
	if( $balance < $allShopTotalprice ){
		$json["status"] 	= 10011;
		$json["batchcode"] 	= $batchcode;
		$json["msg"] 		= "提交成功\n钱包余额不足请尽快支付";
		$json["remark"]		= "钱包零钱支付";
		$jsons=json_encode($json);
		die($jsons);
	}else{
		$sql = "update moneybag_t set balance=balance-".$allShopTotalprice." where isvalid=true and user_id=" . $user_id;
		mysql_query($sql) or die('零钱支付2 Query failed: ' . mysql_error());
		$moneybag_remark = "商城消费，消费金额为：【".$allShopTotalprice."】元)";
		$sql ="insert into moneybag_log (isvalid,customer_id,user_id,money,type,batchcode,pay_style,createtime,remark)values(true,".$customer_id.",".$user_id.",".$allShopTotalprice.",1,".$batchcode.",0,now(),'".$moneybag_remark."')";
		mysql_query($sql) or die('零钱支付22 Query failed: ' . mysql_error());
		
		if( $user_currency > 0 ){
			//插入购物币消费日志
			$sql = "insert into weixin_commonshop_currency_log(isvalid,customer_id,user_id,cost_money,cost_currency,after_currency,batchcode,status,type,class,remark,createtime) select true,".$customer_id.",".$user_id.",".$user_currency.",".$user_currency.",currency-".$user_currency.",".$batchcode.",1,0,1,'商城购物消费',now() from weixin_commonshop_user_currency  where isvalid=true and user_id=" . $user_id;
			mysql_query($sql) or die('零钱支付6 Query failed: ' . mysql_error());
			
			//扣除购物币
			$sql = "update weixin_commonshop_user_currency set currency=currency-".$user_currency." where isvalid=true and user_id=" . $user_id;
			mysql_query($sql) or die('零钱支付7 Query failed: ' . mysql_error());			
		}
		//已支付状态
		$sql = "update weixin_commonshop_orders set paystatus=1,paytime=now() where isvalid=true and pay_batchcode='" . $batchcode . "'";
		mysql_query($sql) or die('零钱支付8 Query failed: ' . mysql_error());
		
		$sql = "update weixin_commonshop_order_prices set paystatus=1,paytime=now() where isvalid=true and pay_batchcode='" . $batchcode . "'";
		mysql_query($sql) or die('零钱支付8_2 Query failed: ' . mysql_error());
		
		//添加订单日志 － 支付
		for($m = 0; $m < count($batchcode_arr); $m++) {	
			$query_log = "insert into weixin_commonshop_order_logs(batchcode,operation,descript,operation_user,createtime,isvalid) 
				values('".$batchcode_arr[$m]["batchcode"]."',2,'订单支付 － ".$paystyle."','".$fromuser."',now(),1)";
			mysql_query($query_log) or die('零钱支付4 Query failed: ' . mysql_error());
			
			$shopmessage->GetMoney_Common($batchcode_arr[$m]["batchcode"],$customer_id,$batchcode_arr[$m]["reward_money"],$user_id,$exp_user_id,0,-1,$allPerNeedScore,$card_member_id,$batchcode_arr[$m]["shopCurrency"],$user_currency);//分佣

			$shopmessage->GetTicket($http_host, $batchcode_arr[$m]["batchcode"]);//小票打印机
			if($is_QR){	
				$GetQR = $shopmessage->GetQR($batchcode_arr[$m]["batchcode"],$fromuser,$customer_id);
				
				$query_log = "insert into weixin_commonshop_order_logs(batchcode,operation,descript,operation_user,createtime,isvalid) 
				values('".$batchcode_arr[$m]["batchcode"]."',7,'用户确认收货','".$fromuser."',now(),1)";
				mysql_query($query_log) or die('会员卡余额8 Query failed: ' . mysql_error());

				$sql_qr="update weixin_commonshop_orders set sendstatus = 2 where batchcode='".$batchcode_arr[$m]["batchcode"]."'"; 
				mysql_query($sql_qr) or die('零钱支付5 Query failed: ' . mysql_error());	
				
				$sql = "update weixin_commonshop_order_prices set sendstatus=2,confirm_sendtime=now() where isvalid=true and batchcode='".$batchcode_arr[$m]["batchcode"]."'"; 
				mysql_query($sql) or die('零钱支付9 Query failed: ' . mysql_error());
			}
			//删除库存回收信息
			$sql = "delete FROM stockrecovery_t WHERE batchcode='".$batchcode_arr[$m]["batchcode"]."'"; 
			mysql_query($sql) or die('零钱支付6 Query failed: ' . mysql_error());	
		}				
		//return;
		$shopmessage= new shopMessage_Utlity();
		//$shopmessage->set_promot_mode($customer_id,$pay_batchcode,$user_id,$exp_user_id,$fromuser);
		$content3 = "亲，您的钱包零钱支付 -".$allShopTotalprice."元\r\n".
				"来源：【钱包零钱】\n".
				"状态：【支付成功】\n".
				"余额：".($balance-$allShopTotalprice)."元\n".
				"时间：<".date( "Y-m-d H:i:s").">";
		
		$shopmessage->SendMessage($content3,$fromuser,$customer_id,1,$batchcode,1);
		
		$callBackBatchcode = $batchcode.$moneybag_id;//系统生成回调订单号
		$query = "insert into paycallback_t(createtime,isvalid,customer_id,pay_batchcode,callBackBatchcode,price,payClass) values(now(),true,".$customer_id.",".$batchcode.",".$callBackBatchcode.",".$allShopTotalprice.",3)";
		mysql_query($query) or die('购物币支付4 Query failed: ' . mysql_error());
		//print_r($send_supply);
		$send_supplyLen	= count( $send_supply );	
		for ($i = 0; $i < $send_supplyLen; $i++) {
			//print_r($send_supply);
			$supply_id 	= $send_supply[$i]["is_supply_id"];
			$content 	= $send_supply[$i]["content"];
			$query = "select weixin_fromuser from weixin_users where isvalid=true and customer_id=".$customer_id." and id=" . $supply_id . " limit 0,1";
			$result = mysql_query($query) or die('W603 Query failed: ' . mysql_error());
			$supply_fromuser = "";
			while ($row = mysql_fetch_object($result)) {
				$supply_fromuser = $row->weixin_fromuser;
				break; 
			}
			$shopmessage->SendMessage($content, $supply_fromuser, $customer_id);
		}
		
		$json["status"] 	= 4;
		$json["batchcode"] 	= $batchcode;
		$json["msg"] 		= "钱包零钱支付成功";
		$json["remark"]		= "钱包零钱支付";
		$json["batchcode_arr"]		= $batchcode_arr;
		$jsons=json_encode($json);
		die($jsons);
	}
}
/*钱包零钱支付结束*/

/*会员卡余额支付开始*/
if( $paystyle == "会员卡余额支付"  and 1 == $pay_immed and $card_member_id > 0 ){
				
	//查询会员卡余额
    $query = "select remain_consume,extra_income from weixin_card_member_consumes where isvalid=true and card_member_id=" . $card_member_id ." limit 0,1";
    $result = mysql_query($query) or die('会员卡余额2 Query failed: ' . mysql_error());
    $before_money = 0;
    while ($row = mysql_fetch_object($result)) {
        $before_money = $row->remain_consume; //消费前余额
		$extra_income = $row->extra_income; //查询是否有充值卡送的钱
    }
	if($totalprice>=$extra_income){
		$after_extra_income=0;
	}else{
		$after_extra_income=$extra_income-$totalprice;
	}
	$after_money = $before_money - $allShopTotalprice;
	if( $allShopTotalprice > $before_money ){
		$json["status"] 	= 10012;
		$json["batchcode"] 	= $batchcode;
		$json["msg"] 		= "提交成功\n会员卡余额不足请尽快支付";
		$json["remark"]		= "会员卡余额支付";
		$jsons=json_encode($json);
		die($jsons);
	}else{
		// 写入消费记录
		 $remark = "会员卡余额消费" . $allShopTotalprice . ",订单编号:" . $batchcode;
		$query_record="insert into weixin_card_recharge_records(card_member_id,before_cost,cost,after_cost,remark,new_record,isvalid,createtime) select card_member_id,remain_consume,".-$allShopTotalprice.",remain_consume-".$allShopTotalprice.",'".$remark."',1,true,now() from weixin_card_member_consumes where isvalid=true and card_member_id=".$card_member_id;
		mysql_query($query_record) or die('会员卡余额3 Query failed: ' . mysql_error());
		
		//扣除会员卡余额
		$sql = "update weixin_card_member_consumes set total_consume= total_consume+" . $allShopTotalprice . ", remain_consume = remain_consume-" . $allShopTotalprice . ", extra_income=".$after_extra_income." where card_member_id=" . $card_member_id;
		mysql_query($sql) or die('会员卡余额4 Query failed: ' . mysql_error());
		
			//有使用购物币就扣除购物币
		if( $user_currency > 0 ){
			//插入购物币消费日志
			$sql = "insert into weixin_commonshop_currency_log(isvalid,customer_id,user_id,cost_money,cost_currency,after_currency,batchcode,status,type,class,remark,createtime) select true,".$customer_id.",".$user_id.",".$user_currency.",".$user_currency.",currency-".$user_currency.",".$batchcode.",1,0,1,'商城购物消费',now() from weixin_commonshop_user_currency  where isvalid=true and user_id=" . $user_id;
			mysql_query($sql) or die('会员卡余额5 Query failed: ' . mysql_error());
			
			//扣除购物币
			$sql = "update weixin_commonshop_user_currency set currency=currency-".$user_currency." where isvalid=true and user_id=" . $user_id;
			mysql_query($sql) or die('会员卡余额6 Query failed: ' . mysql_error());			
		}
		
		//已支付状态
		$sql = "update weixin_commonshop_orders set paystatus=1,paytime=now() where isvalid=true and pay_batchcode='" . $batchcode . "'";
		mysql_query($sql) or die('会员卡余额7 Query failed: ' . mysql_error());
		
		$sql = "update weixin_commonshop_order_prices set paystatus=1,paytime=now() where isvalid=true and pay_batchcode='" . $batchcode . "'";
		mysql_query($sql) or die('会员卡余额7_2 Query failed: ' . mysql_error());
		
		//添加订单日志 － 支付
		for($m = 0; $m < count($batchcode_arr); $m++) {	
			$query_log = "insert into weixin_commonshop_order_logs(batchcode,operation,descript,operation_user,createtime,isvalid) 
				values('".$batchcode_arr[$m]["batchcode"]."',2,'订单支付 － ".$paystyle."','".$fromuser."',now(),1)";
			mysql_query($query_log) or die('会员卡余额8 Query failed: ' . mysql_error());
			
			$shopmessage->GetMoney_Common($batchcode_arr[$m]["batchcode"],$customer_id,$batchcode_arr[$m]["reward_money"],$user_id,$exp_user_id,0,-1,$allPerNeedScore,$card_member_id,$batchcode_arr[$m]["shopCurrency"],$user_currency);//分佣
			$shopmessage->GetTicket($http_host, $batchcode_arr[$m]["batchcode"]);//小票打印机
			if($is_QR){	
				$GetQR = $shopmessage->GetQR($batchcode_arr[$m]["batchcode"],$fromuser,$customer_id);
				
				$query_log = "insert into weixin_commonshop_order_logs(batchcode,operation,descript,operation_user,createtime,isvalid) 
				values('".$batchcode_arr[$m]["batchcode"]."',7,'用户确认收货','".$fromuser."',now(),1)";
				mysql_query($query_log) or die('会员卡余额8 Query failed: ' . mysql_error());

				$sql_qr="update weixin_commonshop_orders set sendstatus = 2 where batchcode='".$batchcode_arr[$m]["batchcode"]."'"; 
				mysql_query($sql_qr) or die('会员卡余额9 Query failed: ' . mysql_error());	
				
				$sql = "update weixin_commonshop_order_prices set sendstatus=2,confirm_sendtime=now() where isvalid=true and batchcode='".$batchcode_arr[$m]["batchcode"]."'"; 
				mysql_query($sql) or die('会员卡余额10 Query failed: ' . mysql_error());
			}
			//删除库存回收信息
			$sql = "delete FROM stockrecovery_t WHERE batchcode='".$batchcode_arr[$m]["batchcode"]."'"; 
			mysql_query($sql) or die('会员卡余额10 Query failed: ' . mysql_error());
		}
		$shopmessage= new shopMessage_Utlity();
		//$shopmessage->set_promot_mode($customer_id,$pay_batchcode,$user_id,$exp_user_id,$fromuser);
		$content3 = "亲，您的会员卡支付 -".$allShopTotalprice."元\r\n".
				"来源：【会员卡】\n".
				"状态：【支付成功】\n".
				"余额：".$after_money."元\n".
				"时间：<".date( "Y-m-d H:i:s").">";
		
		$shopmessage->SendMessage($content3,$fromuser,$customer_id,1,$batchcode,1);
		
		$callBackBatchcode = $batchcode.$card_member_id;//系统生成回调订单号
		$query = "insert into paycallback_t(createtime,isvalid,customer_id,pay_batchcode,callBackBatchcode,price,payClass) values(now(),true,".$customer_id.",".$batchcode.",".$callBackBatchcode.",".$allShopTotalprice.",2)";
		mysql_query($query) or die('会员卡支付5 Query failed: ' . mysql_error());
		
		$send_supplyLen	= count( $send_supply );	
		for ($i = 0; $i < $send_supplyLen; $i++) {	
			$supply_id 	= $send_supply[$i]["is_supply_id"];
			$content 	= $send_supply[$i]["content"];
			$query = "select weixin_fromuser from weixin_users where isvalid=true and customer_id=".$customer_id." and id=" . $supply_id . " limit 0,1";
			$result = mysql_query($query) or die('W603 Query failed: ' . mysql_error());
			$supply_fromuser = "";
			while ($row = mysql_fetch_object($result)) {
				$supply_fromuser = $row->weixin_fromuser;
				break; 
			}
			$shopmessage->SendMessage($content, $supply_fromuser, $customer_id);
		}
		
		$json["status"] 	= 3;
		$json["batchcode"] 	= $batchcode;
		$json["msg"] 		= "会员卡支付成功";
		$json["remark"]		= "会员卡余额支付";
		$json["batchcode_arr"]		= $batchcode_arr;
		$jsons=json_encode($json);
		die($jsons);
	}
}
/*会员卡余额支付结束*/

$json["status"] 	= 1;
$json["batchcode"] 	= $batchcode;
$json["msg"] 		= "提交成功";
$json["batchcode_arr"] 		= $batchcode_arr;





mysql_close($link);
$error = mysql_error();
if(!empty($error)){
	$json["status"] = 10009;
	$json["msg"] = $error;	
}
$jsons=json_encode($json);
die($jsons);
?>