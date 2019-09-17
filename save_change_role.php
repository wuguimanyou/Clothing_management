<?php
header("Content-type: text/html; charset=utf-8"); 
require('../../../config.php');
require('../../../customer_id_decrypt.php'); //导入文件,获取customer_id_en[加密的customer_id]以及customer_id[已解密]
require('../../../common/utility_shop.php');
$link = mysql_connect(DB_HOST,DB_USER,DB_PWD);
mysql_select_db(DB_NAME) or die('Could not select database');

$new_role =$configutil->splash_new($_POST["new_role"]); //更改角色 1代理商 2顶级推广员 3供应商
$user_id =$configutil->splash_new($_POST["user_id"]);
$fromw =$configutil->splash_new($_POST["fromw"]);	//来源：1主动关注 2朋友圈 3二维码
$pagenum =$configutil->splash_new($_GET["pagenum"]);
$parent_id =$configutil->splash_new($_GET["parent_id"]);

switch ($new_role){
	case 1:		//代理商
	
		$type = 2;
		$Cstatus = 1;
		$agent_select =$_POST["agent_select"];
		$vlst = explode("_",$agent_select);
		$name = "";
		$value = 0;
		$discount = 1;
		$agent_select = $vlst[1];
		$agent_price = $vlst[2];
		$agent_discount = $vlst[3];

		

		$query = "SELECT id,status FROM weixin_commonshop_applyagents where isvalid=true and user_id=".$user_id;
		$res = mysql_query($query);
		while($row=mysql_fetch_object($res)){
			
			$id  = $row->id;
			$status = $row->status;
		}


		if($id==''){
			$sql="insert into weixin_commonshop_applyagents(user_id,agent_name,agent_price,agent_discount,status,isvalid,createtime) values(".$user_id.",'".$agent_select."',".$agent_price.",'".$agent_discount."',1,true,now())";
	
		
		}else{			
				$sql="update weixin_commonshop_applyagents set agent_name='".$agent_select."',agent_price='".$agent_price."',agent_discount='".$agent_discount."',status=1 where user_id=".$user_id." and isvalid=true and id=".$id;
		}


		 if(!empty($agent_price) and !empty($agent_discount)){ 	 	
			 mysql_query($sql) or die('Query failed:821 ' . mysql_error());
		 }


		$where1 = "  foreign_id=".$user_id.' and customer_id='.$customer_id;
		$rcount1 = is_exist('weixin_qr_infos',$where1);
		if($rcount1>0){
			 $query_qr_infos="select id from weixin_qr_infos where customer_id=".$customer_id." and foreign_id=".$user_id."";
			$result=mysql_query($query_qr_infos)or die('Query_qr_infos failed1:'.mysql_error());
			while($row=mysql_fetch_object($result)){
				$qr_info_id = $row->id;
			}
		}else{
			//weixin_qr_infos    
			 $query_qr_infos="insert into weixin_qr_infos (foreign_id,customer_id,isvalid,type,scene_id,user_type,obj_id)values('".$user_id."',".$customer_id.",1,1,1,1,1)";
			mysql_query($query_qr_infos) or die('Query_qr_infos failed2: ' . mysql_error()); 
				
				$qr_info_id = mysql_insert_id();
					
		}	
		$where2 = "  qr_info_id=".$qr_info_id.' and customer_id='.$customer_id;
		$rcount2 = is_exist('weixin_qrs',$where2);
		if($rcount2>0){
			 $query_qrs="update weixin_qrs set status=1,reason='' where qr_info_id=".$qr_info_id.' and customer_id='.$customer_id;
			mysql_query($query_qrs) or die('Query_qrs failed1: ' . mysql_error()); 
		}else{
			//weixin_qrs     
			 $query_qrs="insert into weixin_qrs (expire_seconds,action_name,qr_info_id,isvalid,createtime,customer_id,type,imgurl_qr,reward_score,reward_money,status,apptype)values(-1,'QR_LIMIT_SCENE',".$qr_info_id.",true,now(),".$customer_id.",1,'',0,0,1,0)";
			//echo $query_qrs."<br>";		
			mysql_query($query_qrs) or die('Query_qrs failed2: ' . mysql_error()); 	
		}
				        
		$where3 = "  user_id=".$user_id;
		$rcount3 = is_exist('weixin_commonshop_applyagents',$where3);  
		if($rcount3>0){
			 $sql="update weixin_commonshop_applyagents set status=1 where isvalid=true and user_id=".$user_id;
			mysql_query($sql)or die('Query_applyagents failed1: ' . mysql_error()); 
		}else{
			 $sql="insert into weixin_commonshop_applyagents(status,user_id,isvalid,createtime )values(1,".$user_id.",true,now())";
			mysql_query($sql)or die('Query_applyagents failed2: ' . mysql_error()); ;
		}	
			//插入申请代理金额日志 start
		
			$query="select id from weixin_commonshop_agentfee_records where isvalid=true and type=3 and user_id=".$user_id;
			$result = mysql_query($query) or die('Query_agentfee_records failed1: ' . mysql_error());
			$record_id=-1;
			while ($row = mysql_fetch_object($result)) {
				$record_id = $row->id;
				break;
			}
		$where4 = "  user_id=".$user_id." and customer_id=".$customer_id;
		$rcount4 = is_exist('promoters',$where4); 
		if($rcount4>0){
			
			$sql="update promoters set parent_id=-1,status=1,isAgent=1,agent_inventory=".$agent_price." where user_id=".$user_id." and isvalid=true and customer_id=".$customer_id;
			mysql_query($sql)or die('Query_promoters failed1:'.mysql_error());
		}else{
			
			$sql="insert into promoters(parent_id,status,isAgent,isvalid,createtime,customer_id,user_id,pwd,agent_inventory)values(-1,1,1,true,now(),".$customer_id.",".$user_id.",888888,".$agent_price.")";
			mysql_query($sql)or die('Query_promoters failed2:'.mysql_error());
		}	
			$sql="select parent_id,createtime,isAgent,agent_inventory,agent_getmoney from promoters where  status=1 and isvalid=true and user_id=".$user_id;
			$result=mysql_query($sql)or die('Query failed22'.mysql_error());
			while($row=mysql_fetch_object($result)){
				$parent_id = $row->parent_id;
			}
			if($parent_id>0){
				//取消上下级关系 代理不需要上级
				 $sql="update weixin_qr_scans set isvalid=false where  user_id=".$user_id." and customer_id=".$customer_id." and scene_id=".$parent_id;
				mysql_query($sql);
				 $sql="update weixin_users set parent_id=-1 where id=".$user_id;
				mysql_query($sql);
				//取消上下级关系 代理不需要上级
				//插入申请代理金额日志 end
				
				//减少上级的粉丝数和推广员数
				 $sql="update promoters set fans_count= fans_count-1,promoter_count=promoter_count-1 where isvalid=true and user_id=".$parent_id;
				mysql_query($sql);
			}
			//取消供应商资格
			
			$query_cancel_supply="update weixin_commonshop_applysupplys set isvalid=false where user_id=".$user_id."";
			mysql_query($query_cancel_supply)or die('Query_aapplysupplys failed'.mysql_error());
			
	break;
	case 2:		//成为推广员	
			$type 	 = 1;
			$Cstatus = 1;
			$query="select count(1) as rcount  from weixin_qr_infos where foreign_id=".$user_id." and isvalid=true and customer_id=".$customer_id;
			$result=mysql_query($query)or die('Query failed'.mysql_error());
			while($row=mysql_fetch_object($result)){
				$rcount1 = $row->rcount;
			}
			if($rcount1>0){
				$sql="select id from weixin_qr_infos where customer_id=".$customer_id." and foreign_id=".$user_id."";
				$result=mysql_query($sql)or die('Query_qr_infos failed1:'.mysql_error());
				while($row=mysql_fetch_object($result)){
					$qr_info_id = $row->id;
				}
			}else{
				$sql="insert into weixin_qr_infos(customer_id,foreign_id,isvalid)values(".$customer_id.",".$user_id.",true)";
				mysql_query($sql)or die ('Query falied_insert2'.mysql_error());
					$qr_info_id = mysql_insert_id();
				
			}
			$query="select count(1) as rcount  from weixin_qrs where  qr_info_id=".$qr_info_id;
			$result=mysql_query($query)or die('Query failed21'.mysql_error());
			while($row=mysql_fetch_object($result)){
				$rcount2 = $row->rcount;
			}
			if($rcount2>0){
				$sql="update weixin_qrs set status=1,reason='' where qr_info_id=".$qr_info_id;
				mysql_query($sql)or die('Query failed22'.mysql_error());;
			}else{
			 	$sql="insert into weixin_qrs(type,status,reason,qr_info_id,createtime,isvalid,customer_id)values(1,1,'',".$qr_info_id.",now(),true,".$customer_id.")";
				mysql_query($sql)or die('Query failed23'.mysql_error());
			}
			
			$query="select count(1) as rcount  from promoters where user_id=".$user_id." and isvalid=true and customer_id=".$customer_id;
			$result=mysql_query($query)or die('Query failed'.mysql_error());
			while($row=mysql_fetch_object($result)){
				$rcount3 = $row->rcount;
			}
			
			if($rcount3>0){
				 $sql="update promoters set status=1,isAgent=0 where user_id=".$user_id." and isvalid=true and customer_id=".$customer_id;	
				mysql_query($sql);

				//增加上级的推广员数
				$sql="update promoters set promoter_count=promoter_count+1,createtime=now() where isvalid=true and user_id=".$parent_id;
				mysql_query($sql);
			}else{
				 $sql="insert into promoters(status,isAgent,user_id,isvalid,customer_id,createtime,pwd)values(1,0,".$user_id.",true,".$customer_id.",now(),'888888')";
				 //echo $sql."=205";die;
				mysql_query($sql)or die ('Query falied_insert'.mysql_error());
			}
			
			//return;

	break;	
	case 3:		//供应商
		$type 	 = 3;
		$Cstatus = 1;
		$where1 = "  user_id=".$user_id.' and customer_id='.$customer_id;
		$round1 = is_exist('promoters',$where1);		
		if($round1>0){	
			 $query_promoter="update promoters set isAgent=3 where customer_id=".$customer_id." and user_id=".$user_id."";
			mysql_query($query_promoter) or die('Query_promoter failed1: ' . mysql_error()); 
		}else{
		
			//promoters
			 $query_promoter="insert into promoters (user_id,createtime,customer_id,parent_id,status,isAgent,isvalid,pwd)values('".$user_id."',now(),".$customer_id.",-1,1,3,true,888888)";
			mysql_query($query_promoter) or die('Query_promoter failed2: ' . mysql_error()); 
			//echo $query_promoter."<br>";								
		}
		
		$where2 = "  user_id=".$user_id;
		$round2 = is_exist('weixin_commonshop_applysupplys',$where2);
		if($round2>0){
			 $query_applysupply="update weixin_commonshop_applysupplys set status=1 where isvalid=true and user_id=".$user_id;
			mysql_query($query_applysupply) or die('Query_applysupply failed1: ' . mysql_error()); 
		}else{
			//weixin_commonshop_applysupplys
			 $query_applysupply="insert into weixin_commonshop_applysupplys (user_id,createtime,isvalid,supply_money,deposit,status)values('".$user_id."',now(),1,0,0,1)";
			mysql_query($query_applysupply) or die('Query_applysupply failed2: ' . mysql_error()); 
			//echo $query_applysupply."<br>";						
		}
		
		$where3 = "  foreign_id=".$user_id.' and customer_id='.$customer_id;
		$round3 = is_exist('weixin_qr_infos',$where3);
		if($round3>0){
			 $query_qr_infos="select id from weixin_qr_infos where customer_id=".$customer_id." and foreign_id=".$user_id."";
			$result=mysql_query($query_qr_infos)or die('Query_qr_infos failed1:'.mysql_error());
			while($row=mysql_fetch_object($result)){
				$qr_info_id = $row->id;
			}
		}else{
			//weixin_qr_infos    scene_id???
			 $query_qr_infos="insert into weixin_qr_infos (foreign_id,customer_id,isvalid,type,scene_id,user_type,obj_id)values('".$user_id."',".$customer_id.",1,1,1,1,1)";
			mysql_query($query_qr_infos) or die('Query_qr_infos failed2: ' . mysql_error()); 
				//echo $query_qr_infos."<br>";	
				$qr_info_id = mysql_insert_id();
				//echo $qr_info_id."<br>";	
		}	
		$where4 = "  qr_info_id=".$qr_info_id.' and customer_id='.$customer_id;
		$round4 = is_exist('weixin_qrs',$where4);
		if($round4>0){
			 $query_qrs="update weixin_qrs set status=1,reason='' where qr_info_id=".$qr_info_id;
			mysql_query($query_qrs) or die('Query_qrs failed: ' . mysql_error()); 
		}else{
			//weixin_qrs     
			 $query_qrs="insert into weixin_qrs (expire_seconds,action_name,qr_info_id,isvalid,createtime,customer_id,type,imgurl_qr,reward_score,reward_money,status,apptype)values(-1,'QR_LIMIT_SCENE',".$qr_info_id.",true,now(),".$customer_id.",1,'',0,0,1,0)";
			//echo $query_qrs."<br>";		
			mysql_query($query_qrs) or die('Query_qrs failed: ' . mysql_error()); 	
		}
		//查询 微商城 会员卡卡号
			$card_id=-1;
			$query_card="SELECT shop_card_id from weixin_commonshops where isvalid=true and customer_id=".$customer_id." limit 1";
			$result_card = mysql_query($query_card) or die('Query_card failed: ' . mysql_error());
			while ($row_card = mysql_fetch_object($result_card)) {
			   $card_id = $row_card->shop_card_id;
			}	

		$where5 = "  user_id=".$user_id.' and card_id='.$card_id;
		$round5 = is_exist('weixin_card_members',$where5);
		if($round5>0){
			
		}else{
			//weixin_card_members 
			 $query_card_members="insert into weixin_card_members (card_id,isvalid,createtime,user_id)values('".$card_id."',true,now(),".$user_id.")";
			
			mysql_query($query_card_members) or die('Query_card_members failed: ' . mysql_error()); 
			//echo $query_card_members."<br>";					
		}

		//取消代理商资格
			$query_cancel_supply="update weixin_commonshop_applyagents set isvalid=false where user_id=".$user_id."";
			mysql_query($query_cancel_supply)or die('Query_applyagents failed'.mysql_error());
	break;
	
}
$shopmessage= new shopMessage_Utlity(); 
$shopmessage->ChangeRelation_new($user_id,$parent_id,$parent_id,$customer_id,$type,$Cstatus);

function is_exist ($table,$where){
	$rcount = 0;
	 $query="select count(1) as rcount from ".$table." where isvalid=true and ".$where;
	$result=mysql_query($query)or die('Query failed_is_exist'.mysql_error());
	while($row=mysql_fetch_object($result)){
		$rcount = $row->rcount;
	}
	return $rcount;
}
	
			
mysql_close($link);
echo "<script>location.href='fans.php?customer_id=".$customer_id_en."&pagenum=".$pagenum."';</script>"
?>