<?php
header("Content-type: text/html; charset=utf-8"); 
require('../../../config.php');
require('../../../customer_id_decrypt.php'); //导入文件,获取customer_id_en[加密的customer_id]以及customer_id[已解密]
require('../../../common/utility_shop.php');
$link = mysql_connect(DB_HOST,DB_USER,DB_PWD);
mysql_select_db(DB_NAME) or die('Could not select database');
mysql_query("SET NAMES UTF8");
$agent_select = $configutil->splash_new($_POST["agent_select"]);
$user_id =$configutil->splash_new($_POST["user_id"]);
$parent_id =$configutil->splash_new($_GET["parent_id"]);
$balance = 0.00;

 $sql = "SELECT agent_name,agent_price,agent_discount from weixin_commonshop_applyagents where isvalid=true and user_id=".$user_id;
   $query = mysql_query($sql);
   while($row=mysql_fetch_object($query)){
   	$agent_name 	= $row->agent_name;//申请代理的级别

   	$agent_price	= $row->agent_price;//代理价格
   	$agent_discount = $row->agent_discount;//代理折扣
   }
   //查找自己的名字、微信名称、代数、推荐人编号
   $query_u = "select name,weixin_name,generation,parent_id from weixin_users where isvalid=true and id=".$user_id." limit 0,1";
  $result_u=mysql_query($query_u)or die('Query failed'.mysql_error());
  while($row=mysql_fetch_object($result_u)){
  	$u_name = $row->name;//名字
  	$u_weixin_name = $row->weixin_name;//微信名称
  	$u_generation = $row->generation;//代数
  	$u_parent_id = $row->parent_id;//推荐人编号
  }
  //查找自己上级
  $sql6 = "SELECT user_id,agent_name,agent_price,agent_discount from weixin_commonshop_applyagents where isvalid=true and user_id='".$u_parent_id."'";
  $query6 = mysql_query($sql6)or die("Query failed006: " .mysql_error());
  while($row = mysql_fetch_object($query6)){
  $s_agent_name = $row->agent_name;//上级级别
  $s_agent_price = $row->agent_price;//代理价格
  $s_agent_discount = $row->agent_discount;//代理折扣
  $s_user_id = $row->user_id;
  }
  $query = "SELECT balance FROM moneybag_t where isvalid=true AND user_id=".$s_user_id." LIMIT 1";
$result= mysql_query($query) or die('Query failed32: ' . mysql_error());
while($row=mysql_fetch_object($result)){
	$balance = $row->balance;
$query = "select id,agent_price,agent_detail,is_showdiscount from weixin_commonshop_agents where isvalid=true and customer_id=".$customer_id;
$result = mysql_query($query) or die('Query failed: ' . mysql_error());
$agent_price=""; 	
$agent_detail="";
$is_showdiscount=0;
while ($row = mysql_fetch_object($result)) {
    $agent_price=$row->agent_price;		//代理商和价格
	$agent_detail=$row->agent_detail;	//代理说明
	$is_showdiscount=$row->is_showdiscount;//'是否在代理商申请页面显示折扣',
}
$pricearr = explode(",",$agent_price);//把字符串打散为数组
//var_dump($pricearr[0]."<br />".$pricearr[1]."<br />".$pricearr[2]);exit;
$len =  count($pricearr);
$diy_num = $len;
 
   

switch ($agent_select){
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
		$agent_agio = $vlst[4];

		

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
				if($agent_name ==$s_agent_name){//查找自己级别是否等同推荐人
					$balance_add = $balance+$agent_agio;
				$sql7 = "update moneybag_t set balance ='".$balance_add."' where user_id=".$s_user_id." and isvalid=true";
				$query7 = mysql_query($sql7)or die("Query failed007 :" .mysql_error());
				}else{
					//继续查找自己的上级的上级
					$sql8 = "select name,weixin_name,generation,parent_id from weixin_users where isvalid=true and id=".$s_user_id." limit 0,1";
            $query8=mysql_query($sql8)or die('Query failed'.mysql_error());
            while($row=mysql_fetch_object($query8)){
  	        $s_u_name = $row->name;//上级名字
  	        $s_u_weixin_name = $row->weixin_name;//上级微信名称
  	        $s_u_generation = $row->generation;//上级代数
  	        $s_u_parent_id = $row->parent_id;//上级推荐人编号
  }
  //查找自己上级
  $sql9 = "SELECT user_id,agent_name,agent_price,agent_discount from weixin_commonshop_applyagents where isvalid=true and user_id='".$s_u_parent_id."'";
  $query9 = mysql_query($sql9)or die("Query failed006: " .mysql_error());
  while($row = mysql_fetch_object($query9)){
  $ss_agent_name = $row->agent_name;//上级级别
  $ss_agent_price = $row->agent_price;//代理价格
  $ss_agent_discount = $row->agent_discount;//代理折扣
  $ss_user_id = $row->user_id;
  }
  if($agent_name == $ss_agent_name){
	  $query10 = "SELECT balance FROM moneybag_t where isvalid=true AND user_id=".$ss_user_id." LIMIT 1";
$result2= mysql_query($query10) or die('Query failed32: ' . mysql_error());
while($row=mysql_fetch_object($result2)){
	$ss_balance = $row->balance;
	  $ss_balance_add = $ss_balance+$agent_agio;
				$sql11 = "update moneybag_t set balance ='".$ss_balance_add."' where user_id=".$ss_user_id." and isvalid=true";
				$query10 = mysql_query($sql11)or die("Query failed007 :" .mysql_error());

  }elseif{
	  $sql12 = "select name,weixin_name,generation,parent_id from weixin_users where isvalid=true and id=".$ss_user_id." limit 0,1";
            $query12=mysql_query($sql12)or die('Query failed'.mysql_error());
            while($row=mysql_fetch_object($query12)){
  	        $ss_u_name = $row->name;//上级名字
  	        $ss_u_weixin_name = $row->weixin_name;//上级微信名称
  	        $ss_u_generation = $row->generation;//上级代数
  	        $ss_u_parent_id = $row->parent_id;//上级推荐人编号
  }
  //查找自己上级
  $sql13 = "SELECT user_id,agent_name,agent_price,agent_discount from weixin_commonshop_applyagents where isvalid=true and user_id='".$ss_u_parent_id."'";
  $query13 = mysql_query($sql13)or die("Query failed006: " .mysql_error());
  while($row = mysql_fetch_object($query9)){
  $sss_agent_name = $row->agent_name;//上级级别
  $sss_agent_price = $row->agent_price;//代理价格
  $sss_agent_discount = $row->agent_discount;//代理折扣
  $sss_user_id = $row->user_id;
  }
 if($agent_name == $sss_agent_name){
	  $query13 = "SELECT balance FROM moneybag_t where isvalid=true AND user_id=".$ss_user_id." LIMIT 1";
$result3= mysql_query($query13) or die('Query failed32: ' . mysql_error());
while($row=mysql_fetch_object($result3)){
	$ss_balance = $row->balance;
	  $ss_balance_add = $ss_balance+$agent_agio;
				$sql13 = "update moneybag_t set balance ='".$ss_balance_add."' where user_id=".$sss_user_id." and isvalid=true";
				$query13 = mysql_query($sql13)or die("Query failed007 :" .mysql_error());
  }
 

				}
				
				/*if($agent_name==$agent_select){//判断更改后级别后补差价
					echo "<script>location.href='agent.php?customer_id=".$customer_id_en."';</script>";
				}else{
					echo "<script>alert('更改代理商级别,您需要补差价:$agent_agio 元')</script>";
					$balance_add = $balance+$agent_agio;
					$sql2 = "update moneybag_t set balance ='".$balance_add."' where user_id=".$user_id." and isvalid=true";
					//file_put_contents("wwwwww.txt",date("Y-m-d")."====sql2===".$sql2."\r\n",FILE_APPEND);
					$query3 = mysql_query($sql2)or die("failed001 Query :" .mysql_error);*/
					echo "<script>location.href='agent.php?customer_id=".$customer_id_en."';</script>";
					
					
				
		}
}
				}
		}
}


		 if(!empty($agent_price) and !empty($agent_discount)){ 	 	
			 mysql_query($sql) or die('Query failed:821 ' . mysql_error());
		 }
}
   
		
	
			
mysql_close($link);
echo "<script>location.href='agent.php?customer_id=".$customer_id_en."';</script>";
?>