<?php
header("Content-type: text/html; charset=utf-8");     
require('../config.php');
require('../customer_id_decrypt.php'); //导入文件,获取customer_id_en[加密的customer_id]以及customer_id[已解密]
//require('../back_init.php'); 
$link = mysql_connect(DB_HOST,DB_USER,DB_PWD); 
mysql_select_db(DB_NAME) or die('Could not select database');
mysql_query("SET NAMES UTF8");
require('../common/jssdk.php');
require('../common/utility_shop.php');  
require('../proxy_info.php');
//头文件----start
// require('../common/common_from.php');
//头文件----end
if(!empty($_GET["user_id"])){
    $user_id=$configutil->splash_new($_GET["user_id"]);
    $user_id = passport_decrypt($user_id);
}else{
    if(!empty($_SESSION["user_id_".$customer_id])){
        $user_id=$_SESSION["user_id_".$customer_id];
    }
}
$aplay_name    = '';	//姓名
$aplay_phone   = '';	//手机号
$location_p    = '';	//省
$location_c    = '';	//市
$location_a    = '';	//区/县
$remark 	   = '';	//备注
$aplay_grate_p = '';	//0：区代；1：市代；2：省代 3：自定义
$check		   = false;	//是否满足申请条件

$aplay_name    = $configutil->splash_new($_POST['name']);
$aplay_phone   = $configutil->splash_new($_POST['phone']);
$location_p    = $configutil->splash_new($_POST['location_p']);
$location_c    = $configutil->splash_new($_POST['location_c']);
$location_a    = $configutil->splash_new($_POST['location_a']);
$remark 	   = $configutil->splash_new($_POST['remark']);
$aplay_grate_p = $configutil->splash_new($_POST['aplay_grate']);

$p_people           =  0;				
$p_order            =  0;							
$c_people           =  0;				
$c_order            =  0;								
$a_people           =  0;					
$a_order            =  0;				
$condition2         =  0;
$p_all_people       =  0;
$c_all_people       =  0;
$a_all_people       =  0;
$is_showuplevel     =  1;
$p_customer         =  '省代';
$c_customer         =  '市代';
$a_customer         =  '区代';
$is_showcustomer    =  1;	
$is_diy_area        =  0;		
$diy_people         =  0;
$diy_order          =  0;
$diy_all_people     =  0;
$diy_customer       =  '自定义区域';
$query = "select p_people,p_order,c_percent,c_people,c_order,a_percent,a_people,a_order,p_customer,c_customer,a_customer,condition2,is_showuplevel,p_all_people,c_all_people,a_all_people,is_showcustomer,is_diy_area,diy_people,diy_order,diy_all_people,diy_customer,rule from weixin_commonshop_team where isvalid = true and customer_id = ".$customer_id." limit 0,1";
$result = mysql_query($query) or die ("query failed".mysql_error());
while($row = mysql_fetch_object($result)){
		$p_people          = $row->p_people;				//省代直推人数
		$p_order           = $row->p_order;					//省代团队订单数
		$c_people          = $row->c_people;				//市代直推人数
		$c_order           = $row->c_order;					//市代团队订单数
		$a_people          = $row->a_people;				//区代直推人数
		$a_order           = $row->a_order;					//区代团队订单数
		$condition2        = $row->condition2;				//条件二选择
		$is_showuplevel    = $row->is_showuplevel;			//是否显示升级
		$p_all_people      = $row->p_all_people;			//升级省代所需推广员人数
		$c_all_people      = $row->c_all_people;			//升级市代所需推广员人数
		$a_all_people      = $row->a_all_people;			//升级区代所需推广员人数
		$is_showcustomer   = $row->is_showcustomer;	        //是否开启区域代理自定义
		$p_customer        = $row->p_customer;				//省代自定义名称
		$c_customer        = $row->c_customer;				//市代自定义名称
		$a_customer        = $row->a_customer;				//区代自定义名称
		$is_diy_area       = $row->is_diy_area;				//开启自定义区域
		$diy_people        = $row->diy_people;				//自定义级别直推人数
		$diy_order         = $row->diy_order;               //自定义级别团队订单数
		$diy_all_people    = $row->diy_all_people;          //升级自定义级别所需团队推广员人数
		$diy_customer      = $row->diy_customer;	        //自定义级别自定义名称
}
if($is_showcustomer){
	if($p_customer == ''){
		$p_customer = '省代';
	}
	if($c_customer == ''){
		$c_customer = '市代';
	}
	if($a_customer == ''){
		$a_customer = '区代';
	}
	if($diy_customer == ''){
		$diy_customer = '自定义区域';
	}
}else{
	$p_customer = '省代';
	$c_customer = '市代';
	$a_customer = '区代';
	$diy_customer = '自定义区域';
}

$isAgent 		= -1;		//5：区代，6：市代，7：省代，8:自定义区域	
$team_order 	= 0;	//团队订单数
$query2 = "select isAgent,team_order from promoters where isvalid=true and status=1 and customer_id=".$customer_id." and user_id=".$user_id;
$result2 = mysql_query($query2) or die('query failed2'.mysql_error());
while($row2 = mysql_fetch_object($result2)){
	$isAgent 		= $row2->isAgent;
	$team_order 	= $row2->team_order;
}

$t_id 		 = -1;		//区域代理申请ID
$aplay_grate = -1;		//0：区代；1：市代；2：省代 3：自定义
$status 	 = -1;		//状态：0审核，1确认
$query3 = "select id,aplay_grate,status from weixin_commonshop_team_aplay where isvalid=true and customer_id=".$customer_id." and aplay_user_id=".$user_id." limit 0,1";
$result3 = mysql_query($query3) or die('query failed3'.mysql_error());
while($row3 = mysql_fetch_object($result3)){
	$t_id 		 = $row3->id;
	$aplay_grate = $row3->aplay_grate;
	$status 	 = $row3->status;
}

$generation=1;
$parent_id=-1;
$query4 = "select generation,parent_id from weixin_users where isvalid=true and id=".$user_id;
$result4 = mysql_query($query4) or die('query failed:4'. mysql_error());
while ($row4 = mysql_fetch_object($result4)) {
	$parent_id = $row4->parent_id;    
	$generation = $row4->generation;     //当前用户的代数
}

$reward_level = 3;	//商城分佣级数
$query5 = "select reward_level from weixin_commonshops where isvalid=true and customer_id=".$customer_id;
$result5 = mysql_query($query5) or die('Query failed5' . mysql_error());  
while ($row5 = mysql_fetch_object($result5)) {
	$reward_level = $row5->reward_level;
}

//商城分佣级数之内的团队推广员人数
$query6 = "SELECT count(distinct p.user_id) as count_prom FROM promoters p INNER JOIN weixin_users w ON w.id=p.user_id  WHERE  p.isvalid=TRUE AND p.status=1  AND match(w.gflag) against (',".$user_id.",') and w.generation between ".($generation+1)." and ".($generation+$reward_level);
$result6 = mysql_query($query6) or die('query failed6' . mysql_error());
$team_promoter_count = 0;
while ($row6 = mysql_fetch_object($result6)) {	 
	$team_promoter_count = $row6->count_prom;  
}

//一级推广员人数
$query7 = "SELECT count(distinct p.user_id) as count_prom FROM promoters p INNER JOIN weixin_users w ON w.id=p.user_id  WHERE  p.isvalid=TRUE AND p.status=1 and p.customer_id=".$customer_id." and w.customer_id=".$customer_id."  AND match(w.gflag) against (',".$user_id.",') and w.generation=".($generation+1);
$result7 = mysql_query($query7) or die('query failed7' . mysql_error());
$promoter_count = 0;
while ($row7 = mysql_fetch_object($result7)) {	 
	$promoter_count = $row7->count_prom;  
}

if($is_showuplevel){
		switch($aplay_grate_p){
			case 0: //申请区代
				//判断条件	
				if($condition2==0){		
					if($promoter_count>=$a_people && $team_order>=$a_order){	
						$check = true;
					}
				}else{
					if($promoter_count>=$a_people && $team_promoter_count>=$team_a_all_people){	
						$check = true;
					}
				}
				if($t_id<0){
					if($check){
						$sql="insert into weixin_commonshop_team_aplay(aplay_user_id,aplay_grate,aplay_name,aplay_phone,isvalid,createtime,status,customer_id,location_p,location_c,location_a,remark) values(".$user_id.",0,'".$aplay_name."',".$aplay_phone.",true,now(),0,".$customer_id.",'".$location_p."','".$location_c."','".$location_a."','".$remark."')";
						mysql_query($sql)or die('sql failed'.mysql_error()); 	
						//生命周期
						$shopmessage = new shopMessage_Utlity(); 
						$shopmessage -> ChangeRelation_new($user_id,$parent_id,$parent_id,$customer_id,5,18); 
						exit('{"status": 2001, "errorMsg":"已提交申请"}');
					}else{
						exit('{"status": 2002, "errorMsg":"升级'.$a_customer.'条件不满足"}');
					}
				}else{
					switch($aplay_grate){
						case 0:
							switch($status){
								case 1:
									exit('{"status": 4003, "errorMsg":"已审核通过"}'); 
								break;
								case 0:
									exit('{"status": 4004, "errorMsg":"审核中"}'); 
								break;
								case -1:
									exit('{"status": 4005, "errorMsg":"升级'.$a_customer.'驳回申请,请联系管理员"}'); 
								break;
							}
						break;
						case 1:
							exit('{"status": 3002, "errorMsg":"已申请'.$c_customer.'"}'); 
						break;
						case 2:
							exit('{"status": 2001, "errorMsg":"已申请'.$p_customer.'"}'); 
						break;
						case 3:
							if($check){
								$sql="update weixin_commonshop_team_aplay set aplay_grate=0,status=0,aplay_name='".$aplay_name."',aplay_phone=".$aplay_phone.",createtime=now(),location_p='".$location_p."',location_c='".$location_c."',location_a='".$location_a."',remark='".$remark."' where aplay_user_id=".$user_id." and isvalid=1 and customer_id=".$customer_id;
								mysql_query($sql)or die('team_uplevel L301 Query failed: ' . mysql_error());
								
								exit('{"status": 4006, "errorMsg":"已提交申请"}'); 
							}else{
								exit('{"status": 4007, "errorMsg":"升级'.$a_customer.'条件不满足"}'); 
							}
						break;				
					}
				}				
			break;
			
			case 1:	//申请市代
				//判断条件	
				if($condition2==0){	
					if($promoter_count>=$c_people and $team_order>=$c_order){	
						$check = true;
					}
				}else{
					if($promoter_count>=$c_people and $team_promoter_count>=$team_c_all_people){	
						$check = true;
					}
				}
				if($t_id<0){
					if($check){
						$sql="insert into weixin_commonshop_team_aplay(aplay_user_id,aplay_grate,aplay_name,aplay_phone,isvalid,createtime,status,customer_id,location_p,location_c,location_a,remark)values(".$user_id.",1,'".$aplay_name."',".$aplay_phone.",true,now(),0,".$customer_id.",'".$location_p."','".$location_c."','".$location_a."','".$remark."')";
						mysql_query($sql)or die('L183 Query failed: ' . mysql_error()); 
						//生命周期
						$shopmessage = new shopMessage_Utlity(); 
						$shopmessage -> ChangeRelation_new($user_id,$parent_id,$parent_id,$customer_id,5,17); 
						exit('{"status": 3001, "errorMsg":"已提交申请"}');
					}else{
						exit('{"status": 3002, "errorMsg":"升级'.$c_customer.'条件不满足"}'); 
					}
				}else{
					switch($aplay_grate){
						case 3:
						case 0:
							if($check){
								$sql="update weixin_commonshop_team_aplay set aplay_grate=1,status=0,aplay_name='".$aplay_name."',aplay_phone=".$aplay_phone.",createtime=now(),location_p='".$location_p."',location_c='".$location_c."',location_a='".$location_a."',remark='".$remark."' where aplay_user_id=".$user_id." and isvalid=1 and customer_id=".$customer_id;
								mysql_query($sql)or die('team_uplevel L165 Query failed: ' . mysql_error());
								
								exit('{"status": 3003, "errorMsg":"已提交申请"}'); 
							}else{
								exit('{"status": 3004, "errorMsg":"升级'.$c_customer.'条件不满足"}'); 
							}
						break;
						case 1:
							switch($status){
								case 1:
									exit('{"status": 3005, "errorMsg":"已审核通过"}'); 
								break;
								case 0:
									exit('{"status": 3006, "errorMsg":"审核中"}'); 
								break;
								case -1:
									exit('{"status": 3007, "errorMsg":"升级'.$c_customer.'驳回申请,请联系管理员"}'); 
								break;
							}
						break;
						case 2:
							exit('{"status": 3008, "errorMsg":"已申请'.$p_customer.'"}'); 
						break;
					}
				}	
			break;
			
			case 2:	//申请省代
				//判断条件
				if($condition2==0){
					if($promoter_count>=$p_people and $team_order>=$p_order){	
						$check = true;
					}
				}else{
					if($promoter_count>=$p_people and $team_promoter_count>=$team_p_all_people){	
						$check = true;
					}
				}
				
				if($t_id<0){
					if($check){
						$sql="insert into weixin_commonshop_team_aplay(aplay_user_id,aplay_grate,aplay_name,aplay_phone,isvalid,createtime,status,customer_id,location_p,location_c,location_a,remark)values(".$user_id.",2,'".$aplay_name."',".$aplay_phone.",true,now(),0,".$customer_id.",'".$location_p."','".$location_c."','".$location_a."','".$remark."')";
						mysql_query($sql)or die('W-133 Query failed: ' . mysql_error()); 
						exit('{"status": 2001, "errorMsg":"已提交申请"}');
					}else{
						exit('{"status": 2002, "errorMsg":"升级'.$p_customer.'条件不满足"}'); 
					}
				}else{
					switch($aplay_grate){
						case 3:
						case 0:
						case 1:
							if($check){
								$sql="update weixin_commonshop_team_aplay set aplay_grate=2,status=0,aplay_name='".$aplay_name."',aplay_phone=".$aplay_phone.",createtime=now(),location_p='".$location_p."',location_c='".$location_c."',location_a='".$location_a."',remark='".$remark."' where aplay_user_id=".$user_id." and isvalid=1 and customer_id=".$customer_id;
								mysql_query($sql)or die('team_uplevel L103 Query failed: ' . mysql_error());
								//生命周期
								$shopmessage = new shopMessage_Utlity(); 
								$shopmessage -> ChangeRelation_new($user_id,$parent_id,$parent_id,$customer_id,5,16); 
								
								exit('{"status": 2003, "errorMsg":"已提交申请"}'); 
							}else{
								exit('{"status": 2004, "errorMsg":"升级'.$p_customer.'条件不满足"}'); 
							}
						break;
						case 2:
							switch($status){
								case 1:
									exit('{"status": 2005, "errorMsg":"已审核通过"}'); 
								break;
								case 0:
									exit('{"status": 2006, "errorMsg":"审核中"}'); 
								break;
								case -1:
									exit('{"status": 2007, "errorMsg":"升级'.$p_customer.'驳回申请,请联系管理员"}'); 
								break;
							}
						break;
					}
				}
			break;
			
			case 3:	//申请自定义区域
				//判断条件	
				if($condition2==0){			
					if($promoter_count >= $diy_people and $team_order >= $diy_order){	
						$check = true;
					}
				}else{
					if($promoter_count >= $diy_people and $team_promoter_count>=$team_diy_all_people){	
						$check = true;
					}
				}
				if($t_id<0){
					if($check){
						$sql="insert into weixin_commonshop_team_aplay(aplay_user_id,aplay_grate,aplay_name,aplay_phone,isvalid,createtime,status,customer_id,location_p,location_c,location_a,remark)values(".$user_id.",3,'".$aplay_name."',".$aplay_phone.",true,now(),0,".$customer_id.",'".$location_p."','".$location_c."','".$location_a."','".$remark."')";
						mysql_query($sql)or die('L338 Query failed: ' . mysql_error()); 
						//生命周期
						$shopmessage = new shopMessage_Utlity(); 
						$shopmessage -> ChangeRelation_new($user_id,$parent_id,$parent_id,$customer_id,5,19); 							
						exit('{"status": 4001, "errorMsg":"已提交申请"}');
					}else{
						exit('{"status": 4002, "errorMsg":"升级'.$diy_customer.'条件不满足"}'); 
					}
				}else{
					switch($aplay_grate){
						case 3:
							switch($status){
								case 1:
									exit('{"status": 4003, "errorMsg":"已审核通过"}'); 
								break;
								case 0:
									exit('{"status": 4004, "errorMsg":"审核中"}'); 
								break;
								case -1:
									exit('{"status": 4005, "errorMsg":"升级'.$diy_customer.'驳回申请,请联系管理员"}'); 
								break;
							}
						break;
						case 0:
							exit('{"status": 4002, "errorMsg":"已申请'.$a_customer.'"}'); 
						break;				
						case 1:
							exit('{"status": 3002, "errorMsg":"已申请'.$c_customer.'"}'); 
						break;
						case 2:
							exit('{"status": 2001, "errorMsg":"已申请'.$p_customer.'"}'); 
						break;
					}
				}				
			break;
		}
}else{
	exit('{"status": 4008, "errorMsg":"商家没开启区域代理申请"}');
}

?>