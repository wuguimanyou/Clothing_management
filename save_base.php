<?php
header("Content-type: text/html; charset=utf-8"); 
require('../config.php');
require('../customer_id_decrypt.php'); //导入文件,获取customer_id_en[加密的customer_id]以及customer_id[已解密]
require('../back_init.php');

/* 参数获取 */
$name =$configutil->splash_new($_POST["name"]);
$shop_id =$configutil->splash_new($_POST["shop_id"]);
$email = "";
$need_customermessage = $configutil->splash_new($_POST["need_customermessage"]);
$need_online = $configutil->splash_new($_POST["need_online"]);
$detail_template_type = $configutil->splash_new($_POST["detail_template_type"]);
$introduce = $configutil->splash_new($_POST["introduce"]);
$introduce=addslashes($introduce);   //过滤引号，斜杠
$introduce=preg_replace("/\s/","",$introduce);  //过滤换行符



$staff_imgurl = $configutil->splash_new($_POST["staff_imgurl"]);
$promoter_bg_imgurl = $configutil->splash_new($_POST["promoter_bg_imgurl"]); //推广二维码图片的背景图

$bottom_support_imgurl = $configutil->splash_new($_POST["bottom_support_imgurl"]);//底部技术支持
$isOpenSales = $configutil->splash_new($_POST["isOpenSales"]);//是否在商城显示产品销量
$bottom_support_cont = $configutil->splash_new($_POST["bottom_support_cont"]);
$is_bottom_support =0; //是否开启底部技术支持
if(!empty($_POST["is_bottom_support"])){
   $is_bottom_support = $configutil->splash_new($_POST["is_bottom_support"]);
}

$nopostage_money = $configutil->splash_new($_POST["nopostage_money"]);


$isOpenPublicWelfare = 0;
if(!empty($_POST["isOpenPublicWelfare"])){
	$isOpenPublicWelfare = $configutil->splash_new($_POST["isOpenPublicWelfare"]); //是否开启公益基金
}

$OpenBillboard = 0;
if(!empty($_POST["OpenBillboard"])){
	$OpenBillboard = $configutil->splash_new($_POST["OpenBillboard"]); //是否开启龙虎榜
}

$valuepercent = 0;
if(!empty($_POST["valuepercent"])){
	$valuepercent = $configutil->splash_new($_POST["valuepercent"]); //公益基金分配率
}

$pro_card_level = 0;
if(!empty($_POST["pro_card_level"])){
	$pro_card_level = $configutil->splash_new($_POST["pro_card_level"]); //购买产品需要会员卡级别开关  0关闭，1开启
}

$welfare_images = "";
if(!empty($_POST["welfare_images"])){
	$welfare_images = $configutil->splash_new($_POST["welfare_images"]); //公益基金背景图
}

$member_template_type = 1;
if(!empty($_POST["member_template_type"])){
   $member_template_type=$_POST["member_template_type"];
}

$stock_remind=0; 
if(!empty($_POST["stock_remind"])){ 
   $stock_remind=$_POST["stock_remind"]; 
} 

$is_showbottom_menu = $configutil->splash_new($_POST["is_showbottom_menu"]);
$is_showdiscuss = $configutil->splash_new($_POST["is_showdiscuss"]);
$is_pic = $configutil->splash_new($_POST["is_pic"]); //是否上传图片

$auto_confirmtime = 0;
if(!empty($_POST["auto_confirmtime"])){
   $auto_confirmtime = $configutil->splash_new($_POST["auto_confirmtime"]);
}
$auto_cus_time = 0;
if(!empty($_POST["auto_cus_time"])){	//自动确认收货时间
   $auto_cus_time = $configutil->splash_new($_POST["auto_cus_time"]);
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

$shopping_status = 0;
if(!empty($_POST["shopping_status"])){
   $shopping_status = $configutil->splash_new($_POST["shopping_status"]);
}

$is_applymoney = 1;
//2015-03-11修改，插入提现时间，开始
$is_applymoney_startdate=1;
$is_applymoney_enddate=31;
if($_POST["is_applymoney_startdate"]){
$is_applymoney_startdate = $configutil->splash_new($_POST["is_applymoney_startdate"]);
}
if($_POST["is_applymoney_enddate"]){
$is_applymoney_enddate = $configutil->splash_new($_POST["is_applymoney_enddate"]);
}
if((!is_numeric($is_applymoney_startdate))||(!is_numeric($is_applymoney_enddate))){
	echo "<script>alert('提现时间格式不对');history.go(-1);</script>";
}
//2015-03-11修改，插入提现时间，结束
//2015-03-26修改，插入提现起点，开始
if(empty($_POST["is_applymoney_minmoney"])){
	$is_applymoney_minmoney=100;
}else{
$is_applymoney_minmoney = $configutil->splash_new($_POST["is_applymoney_minmoney"]);
}
//2015-03-26修改，插入提现起点，结束
$exp_name = "推广员";
if(!empty($_POST["exp_name"])){
   $exp_name = $configutil->splash_new($_POST["exp_name"]);
}
$exp_mem_name = "";
$arr_name = array();
$arr_name=$_POST['arr'];
foreach($arr_name as $k=>$v)
{
   $exp_mem_name .= $v.'_';
}
$exp_mem_name = rtrim($exp_mem_name, "_"); 
$distr_type = 1;
if(!empty($_POST["distr_type"])){
   $distr_type = $configutil->splash_new($_POST["distr_type"]);
}
$distr_type = 1;
if(!empty($_POST["distr_type"])){
   $distr_type = $configutil->splash_new($_POST["distr_type"]);
}
$online_qq = $configutil->splash_new($_POST["online_qq"]);

$is_autoupgrade = 0;
if(!empty($_POST["is_autoupgrade"])){
  $is_autoupgrade = $configutil->splash_new($_POST["is_autoupgrade"]);
}

$auto_upgrade_money = 0;
if(!empty($_POST["auto_upgrade_money"])){
  $auto_upgrade_money = $configutil->splash_new($_POST["auto_upgrade_money"]);
}

$auto_upgrade_money_2 = 0;
if(!empty($_POST["auto_upgrade_money_2"])){
  $auto_upgrade_money_2 = $configutil->splash_new($_POST["auto_upgrade_money_2"]);
}

$auto_upgrade_money_3 = 0;
if(!empty($_POST["auto_upgrade_money_3"])){
  $auto_upgrade_money_3 = $configutil->splash_new($_POST["auto_upgrade_money_3"]);
}

/*购买后同意协议*/
if($is_autoupgrade==4){
	$auto_upgrade_money = $auto_upgrade_money_3;
}
/*购买后同意协议End*/

$parent_class = -1;
$parent_pid = -1;
if(!empty($_POST["parent_types_select"])){
  $parent_class = $configutil->splash_new($_POST["parent_types_select"]);
  if($parent_class!=-1){
	  $parent_pid = $configutil->splash_new($_POST["parent_pid_select"]);
  }
}


$CouponId = -1;
if(!empty($_POST["CouponId"])){
  $CouponId = $configutil->splash_new($_POST["CouponId"]);	//代金劵
}
$is_coupon = 0;
if(!empty($_POST["is_coupon"])){
  $is_coupon = $configutil->splash_new($_POST["is_coupon"]);	//是否开启代金劵
}

$shop_card_id = -1;
if(!empty($_POST["shop_card_id"])){
  $shop_card_id = $configutil->splash_new($_POST["shop_card_id"]);
}

$exp_pic_text1 = "消费变成投资 人人都是老板";
$exp_pic_text2 = "长按此图片识别图中二维码搞定";
$exp_pic_text3 = "奖励送不停,别人消费你还有奖励";

if(!empty($_POST["exp_pic_text1"])){
   $exp_pic_text1 = $configutil->splash_new($_POST["exp_pic_text1"]);
}
if(!empty($_POST["exp_pic_text2"])){
   $exp_pic_text2 = $configutil->splash_new($_POST["exp_pic_text2"]);
}
if(!empty($_POST["exp_pic_text3"])){
   $exp_pic_text3 = $configutil->splash_new($_POST["exp_pic_text3"]);
}
$is_dis_model = $configutil->splash_new($_POST["is_dis_model"]);//是否保存过分销模式
if(empty($is_dis_model)){ 
	   $is_dis_model =-1; 
} 



$is_identity = 0;
if(!empty($_POST["is_identity"])){
   (int)$is_identity = $configutil->splash_new($_POST["is_identity"]);
}

$per_identity_num = 0;
if(!empty($_POST["per_identity_num"])){
   (float)$per_identity_num = $configutil->splash_new($_POST["per_identity_num"]);
}

$is_cost_limit = 0;
if(!empty($_POST["is_cost_limit"])){
   (int)$is_cost_limit = $configutil->splash_new($_POST["is_cost_limit"]);
}

$per_cost_limit = 0;
if(!empty($_POST["per_cost_limit"])){
   (float)$per_cost_limit = $configutil->splash_new($_POST["per_cost_limit"]);
}

$is_weight_limit = 0;
if(!empty($_POST["is_weight_limit"])){
   (int)$is_weight_limit = $configutil->splash_new($_POST["is_weight_limit"]);
}

$per_weight_limit = 0;
if(!empty($_POST["per_weight_limit"])){
   (float)$per_weight_limit = $configutil->splash_new($_POST["per_weight_limit"]);
}	

$is_number_limit = 0;
if(!empty($_POST["is_number_limit"])){
   (int)$is_number_limit = $configutil->splash_new($_POST["is_number_limit"]);
}

$per_number_limit = 0;
if(!empty($_POST["per_number_limit"])){
   (int)$per_number_limit = $configutil->splash_new($_POST["per_number_limit"]);
}	

$is_my_commission = 0;
if(!empty($_POST["is_my_commission"])){
   (int)$is_my_commission = $configutil->splash_new($_POST["is_my_commission"]);
}	

/* 参数获取End */	
	
$is_attent = 0;
$attent_url="";

//$need_email = $_POST["need_email"];
$need_email = 0;
$isOpenAgent = 0;
$isOpenSupply = 0; 
$issell_model = 1;
$isOpenInstall = 0;
$isAgreement = 0;	//是否开启购买协议
$is_team=0;
$is_shareholder=0;
$is_cardfavourable = 0;
if(!empty($_POST["issell"])){
	$issell = $configutil->splash_new($_POST["issell"]);
	if(!empty($_POST["is_cardfavourable"])){
		$is_cardfavourable = $configutil->splash_new($_POST["is_cardfavourable"]);//会员卡优惠选择开关
	}
	$isOpenAgent = $configutil->splash_new($_POST["isOpenAgent"]);
	$isOpenSupply = $configutil->splash_new($_POST["isOpenSupply"]); 
	$isOpenInstall = $configutil->splash_new($_POST["isOpenInstall"]); 
    $is_applymoney = $configutil->splash_new($_POST["is_applymoney"]);
	$isAgreement = $configutil->splash_new($_POST["isAgreement"]);
	$is_team = $configutil->splash_new($_POST["is_team"]);
	$is_shareholder = $configutil->splash_new($_POST["is_shareholder"]);
	if(empty($is_team)){
		$is_team = 0;	//是否开启团队奖励 1:开启,0:关闭
	}
	if(empty($is_shareholder)){
		$is_shareholder = 0;	//是否开启股东分红奖励
	}
	$sell_discount = $configutil->splash_new($_POST["sell_discount"]);
	$reward_type = $configutil->splash_new($_POST["reward_type"]);
	$init_reward = $configutil->splash_new($_POST["init_reward"]);
	if(empty($init_reward)){ 
	   $init_reward =0; 
	} 
	$parent_ps = $configutil->splash_new($_POST["parent_ps"]); 
	$parent_ps=preg_replace("/\s/","",$parent_ps);  
	//$parent_ps =ereg_replace('\r\n', '<br />', $parent_ps);
	$issell_model = $configutil->splash_new($_POST["issell_model"]);
	$is_godefault = $configutil->splash_new($_POST["is_godefault"]);
	if(empty($is_godefault)){ 
	   $is_godefault =0; 
	} 
	
	if(empty($issell_model)){ 
	   $issell_model =1; 
	} 
 
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
	$init_reward_4= $configutil->splash_new($_POST["init_reward_4"]);
	if(empty($init_reward_4)){
	   $init_reward_4 =0;
	}
	$init_reward_5= $configutil->splash_new($_POST["init_reward_5"]);
	if(empty($init_reward_5)){
	   $init_reward_5 =0;
	}
	$init_reward_6= $configutil->splash_new($_POST["init_reward_6"]);
	if(empty($init_reward_6)){
	   $init_reward_6 =0;
	}
	$init_reward_7= $configutil->splash_new($_POST["init_reward_7"]);
	if(empty($init_reward_7)){
	   $init_reward_7 =0;
	}
	$init_reward_8= $configutil->splash_new($_POST["init_reward_8"]);
	if(empty($init_reward_8)){
	   $init_reward_8 =0;
	}
}else{
    $issell = 0;
	$member_template_type = 1;
	$sell_discount = 0;
	$reward_type = 1; 
	$init_reward = 0; 
	$init_reward_1 = 0; 
	$init_reward_2 = 0; 
	$init_reward_3 =0; 
	$init_reward_4 =0; 
	$init_reward_5 =0; 
	$init_reward_6 =0; 
	$init_reward_7 =0; 
	$init_reward_8 =0; 
	$reward_type = $configutil->splash_new($_POST["reward_type"]); 
	if(empty($reward_type)){ 
	   $reward_type =0; 
	} 
	$init_reward = $configutil->splash_new($_POST["init_reward"]); 
	if(empty($init_reward)){ 
	   $init_reward =0; 
	} 
	$is_godefault = $configutil->splash_new($_POST["is_godefault"]);
	if(empty($is_godefault)){ 
	   $is_godefault =0; 
	} 
	$parent_ps = $configutil->splash_new($_POST["parent_ps"]);  
	$parent_ps=preg_replace("/\s/","",$parent_ps);   
	//$parent_ps =ereg_replace('\r\n', '<br />', $parent_ps); 
	$issell_model = $configutil->splash_new($_POST["issell_model"]); 
	if(empty($issell_model)){ 
	   $issell_model =1; 
	} 
 
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
	$init_reward_4= $configutil->splash_new($_POST["init_reward_4"]); 
	if(empty($init_reward_4)){ 
	   $init_reward_4 =0; 
	} 
	$init_reward_5= $configutil->splash_new($_POST["init_reward_5"]); 
	if(empty($init_reward_5)){ 
	   $init_reward_5 =0; 
	} 
	$init_reward_6= $configutil->splash_new($_POST["init_reward_6"]); 
	if(empty($init_reward_6)){ 
	   $init_reward_6 =0; 
	} 
	$init_reward_7= $configutil->splash_new($_POST["init_reward_7"]); 
	if(empty($init_reward_7)){ 
	   $init_reward_7 =0; 
	} 
	$init_reward_8= $configutil->splash_new($_POST["init_reward_8"]); 
	if(empty($init_reward_8)){ 
	   $init_reward_8 =0; 
	} 
	
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
//选择推广图片风格
$watertype = 1;
if(!empty($_POST["watertype"])){
   $watertype = $configutil->splash_new($_POST["watertype"]);
}

$template_type_bg=$configutil->splash_new($_POST["template_type_bg"]);
$template_head_bg='';

if($template_type_bg==1){
//file_put_contents('hello.txt','**********'.$_FILES['new_define_share_image']['tmp_name']);
	if(!empty($_FILES['new_template_type_bg']['name'])){
	//file_put_contents('hello2.txt','-------');
		$rand1=rand(0,9);
		$rand2=rand(0,9);
		$rand3=rand(0,9);
		$filename=date("Ymdhis").$rand1.$rand2.$rand3;
		$filetype=substr($_FILES['new_template_type_bg']['name'], strrpos($_FILES['new_template_type_bg']['name'], "."),strlen($_FILES['new_template_type_bg']['name'])-strrpos($_FILES['new_template_type_bg']['name'], "."));
		$filetype=strtolower($filetype);
		if(($filetype!='.jpg')&&($filetype!='.png')&&($filetype!='.gif')){
				echo "<script>alert('文件类型或地址错误');</script>";
				echo "<script>history.back(-1);</script>";
				exit ;
			}
		$filename=$filename.$filetype;
		$savedir='../up/common_shopbase_define/';
		//file_put_contents('hello3.txt',$davedir.'++++'.$filename);
		if(!is_dir($savedir)){
			mkdir($savedir,0777);
		}
		$savefile=$savedir.$filename;
		if (!move_uploaded_file($_FILES['new_template_type_bg']['tmp_name'], $savefile)){
			//echo "<script>文件上传成功！</script>";
			echo "<script>history.back(-1);</script>";
			exit;
		}
		$template_head_bg=$savefile;
	}else{
	$template_head_bg=$_POST['now_template_type_bg'];
	}
}


$define_share_image_flag=$configutil->splash_new($_POST["define_share_image_flag"]);
$define_share_image='';

if($define_share_image_flag==1){
//file_put_contents('hello.txt','**********'.$_FILES['new_define_share_image']['tmp_name']);
	if(!empty($_FILES['new_define_share_image']['name'])){
	//file_put_contents('hello2.txt','-------');
		$rand1=rand(0,9);
		$rand2=rand(0,9);
		$rand3=rand(0,9);
		$filename=date("Ymdhis").$rand1.$rand2.$rand3;
		$filetype=substr($_FILES['new_define_share_image']['name'], strrpos($_FILES['new_define_share_image']['name'], "."),strlen($_FILES['new_define_share_image']['name'])-strrpos($_FILES['new_define_share_image']['name'], "."));
		$filetype=strtolower($filetype);
		if(($filetype!='.jpg')&&($filetype!='.png')&&($filetype!='.gif')){
				echo "<script>alert('文件类型或地址错误');</script>";
				echo "<script>history.back(-1);</script>";
				exit ;
			}
		$filename=$filename.$filetype;
		$savedir='../up/common_shopbase_define/';
		//file_put_contents('hello3.txt',$davedir.'++++'.$filename);
		if(!is_dir($savedir)){
			mkdir($savedir,0777);
		}
		$savefile=$savedir.$filename;
		if (!move_uploaded_file($_FILES['new_define_share_image']['tmp_name'], $savefile)){
			//echo "<script>文件上传成功！</script>";
			echo "<script>history.back(-1);</script>";
			exit;
		}
		$define_share_image=$savefile;
	}else{
	$define_share_image=$configutil->splash_new($_POST['now_define_share_image']);
	}
}
$nowprice_title=$configutil->splash_new($_POST["nowprice_title"]);
$is_cashback=0;
if(!empty($_POST["is_cashback"])){
  $is_cashback = $configutil->splash_new($_POST["is_cashback"]);
}
$cashback_perday=0;
if(!empty($_POST["cashback_perday"])){
  $cashback_perday = $configutil->splash_new($_POST["cashback_perday"]);
}

$link = mysql_connect(DB_HOST,DB_USER,DB_PWD); 
 mysql_select_db(DB_NAME) or die('Could not select database');

 $f = fopen('out.txt', 'w');  
fwrite($f, "====isapplymoney===".$_POST["is_applymoney"]."\r\n");  
fwrite($f, "====is_dis_model===".$_POST["is_dis_model"]."\r\n");  
 if($shop_id>0){
	$sql="update weixin_commonshops set 
		is_godefault=".$is_godefault.",
		is_applymoney=".$is_applymoney.",
		staff_imgurl='".$staff_imgurl."',
		reward_level=".$reward_level.",
		exp_name='".$exp_name."',
		nopostage_money=".$nopostage_money.",
		auto_upgrade_money_2=".$auto_upgrade_money_2.", 
		introduce='".$introduce."',
		per_share_score=".$per_share_score.",
		is_showshare_info=".$is_showshare_info.",
		is_showdiscuss=".$is_showdiscuss.",
		shop_card_id = ".$shop_card_id.",
		is_autoupgrade=".$is_autoupgrade.",
		auto_upgrade_money=".$auto_upgrade_money.",
		is_showbottom_menu=".$is_showbottom_menu.",
		distr_type=".$distr_type.",
		member_template_type=".$member_template_type.",
		is_attent=".$is_attent.",
		attent_url='".$attent_url."',
		auto_confirmtime=".$auto_confirmtime.",
		auto_cus_time=".$auto_cus_time.",
		online_type=".$online_type.",
		sell_detail='".$sell_detail."',
		detail_template_type=".$detail_template_type.",
		online_qq='".$online_qq."',
		need_online=".$need_online.",
		sell_discount=".$sell_discount.",
		reward_type=".$reward_type.",
		init_reward=".$init_reward.",
		name='".$name."',
		email='".$email."',
		need_customermessage=".$need_customermessage.",
		need_email=".$need_email.",
		issell=".$issell.",
		isprint=".$isprint.",
		gz_url='".$gz_url."',
		exp_mem_name='".$exp_mem_name."',
		watertype=".$watertype.",
		exp_pic_text1='".$exp_pic_text1."',
		exp_pic_text2='".$exp_pic_text2."',
		exp_pic_text3='".$exp_pic_text3."',
		is_applymoney_startdate=".$is_applymoney_startdate.",
		is_applymoney_enddate=".$is_applymoney_enddate.",
		is_applymoney_minmoney='".$is_applymoney_minmoney."',
		is_pic=".$is_pic.",
		define_share_image='".$define_share_image."',
		isOpenSupply=".$isOpenSupply.",
		isOpenAgent=".$isOpenAgent.",
		promoter_bg_imgurl='".$promoter_bg_imgurl."',
		parent_ps='".$parent_ps."',
		template_head_bg='".$template_head_bg."',
		is_dis_model=".$is_dis_model.",
		issell_model=".$issell_model.",
		parent_class='".$parent_class."',
		parent_pid='".$parent_pid."',
		stock_remind=".$stock_remind.",
		isOpenInstall=".$isOpenInstall.",
		is_my_commission=".$is_my_commission.",
		is_identity=".$is_identity.",
		per_identity_num='".$per_identity_num."',
		is_cost_limit=".$is_cost_limit.",
		per_cost_limit='".$per_cost_limit."',
		is_weight_limit=".$is_weight_limit.",
		per_weight_limit='".$per_weight_limit."',
		is_number_limit=".$is_number_limit.",
		per_number_limit='".$per_number_limit."',		
		isOpenPublicWelfare=".$isOpenPublicWelfare.",
		openbillboard=".$OpenBillboard.",
		shopping_status=".$shopping_status.",
		bottom_support_imgurl='".$bottom_support_imgurl."',
		is_bottom_support=".$is_bottom_support.",
		bottom_support_cont='".$bottom_support_cont."',
		isAgreement=".$isAgreement.",
		isOpenSales=".$isOpenSales.",
		is_team=".$is_team.",
		nowprice_title='".$nowprice_title."',
		pro_card_level=".$pro_card_level.", 
		is_cashback=".$is_cashback.",
		cashback_perday=".$cashback_perday." ,
		is_shareholder=".$is_shareholder.",
		CouponId=".$CouponId.",
		is_cardfavourable=".$is_cardfavourable.",
		is_coupon=".$is_coupon."
		where id=".$shop_id;	 
	fwrite($f, "====sql===".$sql."\r\n");  
	//echo $sql; return;
	mysql_query($sql)or die('W_update Query failed: ' . mysql_error()); 
 }else{ 
  
	$sql="insert into weixin_commonshops(name,staff_imgurl,email,need_email,need_customermessage,need_online,customer_id,isvalid,createtime,issell,sell_discount,reward_type,init_reward,online_type,online_qq,isprint,detail_template_type,sell_detail,auto_confirmtime,auto_cus_time,is_attent,attent_url,member_template_type,distr_type,is_showbottom_menu,is_autoupgrade,auto_upgrade_money,shop_card_id,is_showdiscuss,is_showshare_info,per_share_score,introduce,gz_url,auto_upgrade_money_2,nopostage_money,exp_name,reward_level,exp_mem_name,watertype,exp_pic_text1,exp_pic_text2,exp_pic_text3,is_applymoney,is_applymoney_startdate,is_applymoney_enddate,is_applymoney_minmoney,is_pic,define_share_image,isOpenSupply,isOpenAgent,promoter_bg_imgurl,parent_ps,template_head_bg,is_dis_model,issell_model,parent_class,parent_pid,stock_remind,is_identity,per_identity_num,is_cost_limit,per_cost_limit,is_weight_limit,per_weight_limit,isOpenPublicWelfare,bottom_support_imgurl,is_bottom_support,bottom_support_cont,isAgreement,is_team,openbillboard,is_number_limit,per_number_limit,nowprice_title,is_my_commission,isOpenSales,pro_card_level,is_cashback,cashback_perday,is_shareholder,CouponId,is_coupon,is_cardfavourable)";
	$sql=$sql." values('".$name."','".$staff_imgurl."','".$email."',".$need_email.",".$need_customermessage.",".$need_online.",".$customer_id.",true,now(),".$issell.",".$sell_discount.",".$reward_type.",".$init_reward.",".$online_type.",'".$online_qq."','".$isprint."',".$detail_template_type.",'".$sell_detail."',".$auto_confirmtime.",".$auto_cus_time.",".$is_attent.",'".$attent_url."',".$member_template_type.",".$distr_type.",".$is_showbottom_menu.",".$is_autoupgrade.",".$auto_upgrade_money.",".$shop_card_id.",".$is_showdiscuss.",".$is_showshare_info.",".$per_share_score.",'".$introduce."','".$gz_url."',".$auto_upgrade_money_2.",".$nopostage_money.",'".$exp_name."',".$reward_level.",'".$exp_mem_name."',".$watertype.",'".$exp_pic_text1."','".$exp_pic_text2."','".$exp_pic_text3."',".$is_applymoney.",".$is_applymoney_startdate.",".$is_applymoney_enddate.",'".$is_applymoney_minmoney."',".$is_pic.",'".$define_share_image."',".$isOpenSupply.",".$isOpenAgent.",'".$promoter_bg_imgurl."','".$parent_ps."','".$template_head_bg."',".$is_dis_model.",".$issell_model.",'".$parent_class."','".$parent_pid."',".$stock_remind.",".$is_identity.",'".$per_identity_num."',".$is_cost_limit.",'".$per_cost_limit."',".$is_weight_limit.",'".$per_weight_limit."',".$isOpenPublicWelfare.",'".$bottom_support_imgurl."',".$is_bottom_support.",'".$bottom_support_cont."',".$isAgreement.",".$is_team.",".$OpenBillboard.",".$is_number_limit.",".$per_number_limit.",'".$nowprice_title."',".$is_my_commission.",".$isOpenSales.",".$pro_card_level.",".$is_cashback.",".$cashback_perday.",".$is_shareholder.",".$CouponId.",".$is_coupon.",".$is_cardfavourable.")";
     // echo $sql;
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
     $sql="update weixin_commonshop_commisions set init_reward_1=".$init_reward_1.",init_reward_2=".$init_reward_2.",init_reward_3=".$init_reward_3.",init_reward_4=".$init_reward_4.",init_reward_5=".$init_reward_5.",init_reward_6=".$init_reward_6.",init_reward_7=".$init_reward_7.",init_reward_8=".$init_reward_8." where id=".$commision_id;
	 mysql_query($sql);
 }else{
     $sql="insert into weixin_commonshop_commisions(init_reward_1,init_reward_2,init_reward_3,init_reward_4,init_reward_5,init_reward_6,init_reward_7,init_reward_8,customer_id,isvalid,createtime) values(".$init_reward_1.",".$init_reward_2.",".$init_reward_3.",".$init_reward_4.",".$init_reward_5.",".$init_reward_6.",".$init_reward_7.",".$init_reward_8.",".$customer_id.",true,now())"; 
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
  if($isOpenPublicWelfare){
	 $query="select id from weixin_commonshop_publicwelfare where isvalid=true and customer_id=".$customer_id;
	 $result = mysql_query($query) or die('Query failed: ' . mysql_error());
	 $PublicWelfare_id=-1;
	  while ($row = mysql_fetch_object($result)) {
	     $PublicWelfare_id= $row->id;
	  }
	  if($PublicWelfare_id>0){
		  $sql="update weixin_commonshop_publicwelfare set valuepercent=".$valuepercent.",backimg='".$welfare_images."' where id=".$PublicWelfare_id;
		  mysql_query($sql);
	  }else{
		  $sql="insert into weixin_commonshop_publicwelfare(customer_id,valuepercent,backimg,isvalid,createtime) values(".$customer_id.",".$valuepercent.",'".$welfare_images."',true,now())";
		  mysql_query($sql);
	  }
 }
 
 $error =mysql_error();
 mysql_close($link);
//echo $error; 
echo "<script>location.href='base.php?customer_id=".$customer_id_en."';</script>"
?>