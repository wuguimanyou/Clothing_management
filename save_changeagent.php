<?php
/*2016/9/27 start hpf*/
file_put_contents("shengji.txt","hpf(1---162)\r\n",FILE_APPEND);
header("Content-type:text/html;charset=utf-8");
require('../../../config.php');
require('../../../customer_id_decrypt.php');
require('../../../common/utility_shop.php');
$link = mysql_connect(DB_HOST,DB_USER,DB_PWD);
mysql_select_db(DB_NAME) or die('Could not select database');
mysql_query("SET NAMES UTF8");
$agent_select = $configutil->splash_new($_POST["agent_select"]);
$user_id = $configutil->splash_new($_POST["user_id"]);
$parent_id = $configutil->splash_new($_GET["parent_id"]);
$balance = 0.00;//零钱
$q_agent_name = "";
$q_agent_price = 0;
$q_agent_discount = 0;
$sql = "select agent_name,agent_price,agent_discount from weixin_commonshop_applyagents where isvalid = true and user_id ='".$user_id."'";

$query = mysql_query($sql)or die("Query failed001 :".mysql_error());
while($row = mysql_fetch_object($query)){
    $q_agent_name 	= $row->agent_name;//申请代理的级别
   	$q_agent_price	= $row->agent_price;//代理价格
   	$q_agent_discount = $row->agent_discount;//代理折扣

}
//查找自己的名字、微信名称、代数、推荐人编号
$u_name = "";
$u_weixin_name = "";
$u_generation = 0;
$u_parent_id = 0;
$sql2= "select name,weixin_name,generation,parent_id from weixin_users where isvalid = true and id='".$user_id."' limit 0,1";
$query2 = mysql_query($sql2)or die("Query failed002:".mysql_error());
while($row=mysql_fetch_object($query2)){
$u_name = $row->name;
$u_weixin_name = $row->weixin_name;
$u_generation = $row->generation;
$u_parent_id = $row->parent_id;
}
//查找自己上级
$s_user_id = 0;
$s_agent_name = "";
$s_agent_price = 0;
$s_agent_discount = 0;
$sql3 = "select user_id,agent_name,agent_price,agent_discount from weixin_commonshop_applyagents where isvalid = true and user_id ='".$u_parent_id."'";
$query3 = mysql_query($sql3)or die("Query failed003 :".mysql_error());
while($row=mysql_fetch_object($query3))
{
$s_user_id = $row->user_id;//上级编号
$s_agent_name = $row->agent_name;//上级代理级别
$s_agent_price = $row->agent_price;//上级代理价格
$s_agent_discount = $row->agent_discount;//上级代理的折扣
}
//查找上级有多少零钱
$sql4 = "select balance from moneybag_t where isvalid = true and user_id='".$u_parent_id."'";
$query4 = mysql_query($sql4)or die("Query failed004:".mysql_error());
while($row= mysql_fetch_object($query4))
{
$balance = $row->balance;//查找上级零钱
}


        $vlst = explode("_",$agent_select);
        $agent_select = $vlst[1];
		$agent_price = $vlst[2];
		$agent_discount = $vlst[3];
		$agent_agio = $vlst[4];

		
		if($user_id!='' && $q_agent_discount>$agent_discount){//现在折扣对比更改折扣
			$sql5="update weixin_commonshop_applyagents set agent_name='".$agent_select."',agent_price='".$agent_price."',agent_discount='".$agent_discount."',status=1 where user_id='".$user_id."'";
			file_put_contents("aaa.txt",date("Y-m-d")."===更改===".$sql5."\r\n",FILE_APPEND);
			$query5 = mysql_query($sql5)or die("Query failed005 :" .mysql_error());
			
		}else{echo "<script>alert('您的级别高于更改的级别或者已经是最高级代理！')</script>";}
			if($agent_select ==$s_agent_name && $u_parent_id!=-1)//当我升级后与推荐人一致，并且推荐人不等于-1则推荐人获取零钱
			{
			    $balance_add = $balance+$agent_agio;
				file_put_contents("aaa.txt",date("Y-m-d")."===零钱===".$balance_add."\r\n",FILE_APPEND);
				$sql6 = "update moneybag_t set balance ='".$balance_add."' where user_id=".$s_user_id." and isvalid=true";
				file_put_contents("aaa.txt",date("Y-m-d")."===11111===".$sql6."\r\n",FILE_APPEND);
				$query6 = mysql_query($sql6)or die("Query failed :".mysql_error());
				$sql15 ="insert into weixin_commonshop_agentdown_records(user_id,isvalid,customer_id,before_agent_name,before_agent_price,before_agent_discount,after_agent_name,after_agent_price,after_agent_discount,createtime,balance)values('".$user_id."',true,'".$customer_id."','".$q_agent_name."','".$q_agent_price."','".$q_agent_discount."','".$vlst[1]."','".$vlst[2]."','".$vlst[3]."',now(),'".$balance_add."')";
				file_put_contents("aaa.txt",date("Y-m-d")."===2222===".$sql15."\r\n",FILE_APPEND);
				$query15 = mysql_query($sql15)or die("Query failed:".mysql_error());

				
			}else{
				//查找上级的推荐人

				$sql7 = "select parent_id from weixin_users where isvalid = true and id='".$s_user_id."' limit 0,1";
				file_put_contents("aaa.txt",date("Y-m-d")."===3333===".$sql7."\r\n",FILE_APPEND);
				$query7 = mysql_query($sql7)or die("Query faied007 :".mysql_error());
				while($row =mysql_fetch_object($query7))
				{
				$ss_parent_id = $row->parent_id;
				}
				$ss_user_id = 0;
				$ss_agent_name = "";
				$ss_agent_price = 0;
				$ss_agent_discount= 0;
				$sql8 = "select user_id,agent_name,agent_price,agent_discount from weixin_commonshop_applyagents where isvalid = true and user_id ='".$ss_parent_id."'";
				file_put_contents("aaa.txt",date("Y-m-d")."===4444===".$sql8."\r\n",FILE_APPEND);
				$query8 = mysql_query($sql8)or die("Query failed008 :".mysql_error());
				while($row = mysql_fetch_object($query8))
				{
				$ss_user_id = $row->user_id;
				$ss_agent_name = $row->agent_name;
				$ss_agent_price = $row->agent_price;
				$ss_agent_discount = $row->agent_discount;
				}
				$s_balance = 0.00;
				$sql9 = "select balance from moneybag_t where isvalid = true and user_id='".$ss_parent_id."'";
				file_put_contents("aaa.txt",date("Y-m-d")."===555===".$sql9."\r\n",FILE_APPEND);
				$query9 = mysql_query($sql9)or die("Query failed009 :".mysql_error());
				while($row = mysql_fetch_object($query9))
				{
					$s_balance = $row->balance;//上上级零钱
				}

			if($agent_select == $ss_agent_name)//当我级别升级到和上上级一致,上上级加零钱
				{
			$s_balance_add = $s_balance+$agent_agio;
			$sql10 = "update moneybag_t set balance ='".$s_balance_add."' where user_id=".$ss_user_id." and isvalid=true";
			file_put_contents("aaa.txt",date("Y-m-d")."===666===".$sql10."\r\n",FILE_APPEND);
			$query10 = mysql_query($sql10)or die("Query failed010 :" .mysql_error());
			$sql16 ="insert into weixin_commonshop_agentdown_records(user_id,isvalid,customer_id,before_agent_name,before_agent_price,before_agent_discount,after_agent_name,after_agent_price,after_agent_discount,createtime,balance)values('".$user_id."',true,'".$customer_id."','".$q_agent_name."','".$q_agent_price."','".$q_agent_discount."','".$vlst[1]."','".$vlst[2]."','".$vlst[3]."',now(),'".$s_balance_add."')";
			file_put_contents("aaa.txt",date("Y-m-d")."===666===".$sql16."\r\n",FILE_APPEND);
				$query16 = mysql_query($sql16)or die("Query failed:".mysql_error());
			}else{
				$sss_parent_id = 0;
			$sql11 = "select parent_id from weixin_users where isvalid = true and id='".$ss_user_id."' limit 0,1";
			file_put_contents("aaa.txt",date("Y-m-d")."===7777===".$sql11."\r\n",FILE_APPEND);
			$query11 = mysql_query($sql11)or die("Query failed011 :" .mysql_error());
			while($row = mysql_fetch_object($query11))
				{
				$sss_parent_id = $row->parent_id;
				}
				$sss_user_id = 0;
				$sss_agent_name = "";
				$sss_agent_discount=0;
				$sql12 = "select user_id,agent_name,agent_price,agent_discount from weixin_commonshop_applyagents where isvalid = true and user_id ='".$sss_parent_id."'";
				$query12 = mysql_query($sql12)or die("Query failed12 :".mysql_error());
				while($row = mysql_fetch_object($query12))
				{
				$sss_user_id = $row ->user_id;
				$sss_agent_name = $row->agent_name;
				$sss_agent_discount = $row->agent_discount;
				}
				$sss_balance = 0.00;
				$sql13 = "select balance from moneybag_t where isvalid = true and user_id='".$sss_parent_id."'";
				$query13 = mysql_query($sql13)or die("Query failed13 :" .mysql_error());
				while($row = mysql_fetch_object($query13)){
				$sss_balance = $row->balance;
				}
				if($agent_select == $sss_agent_name)
				{
					$ss_balance_add = $sss_balance+$agent_agio;
					$sql14 = "update moneybag_t set balance ='".$ss_balance_add."' where user_id=".$sss_user_id." and isvalid=true";
					$query14 = mysql_query($sql14)or die("Query failed014 :" .mysql_error());
					$sql17 ="insert into weixin_commonshop_agentdown_records(user_id,isvalid,customer_id,before_agent_name,before_agent_price,before_agent_discount,after_agent_name,after_agent_price,after_agent_discount,createtime,balance)values('".$user_id."',true,'".$customer_id."','".$q_agent_name."','".$q_agent_price."','".$q_agent_discount."','".$vlst[1]."','".$vlst[2]."','".$vlst[3]."',now(),'".$ss_balance_add."')";
					$query17 = mysql_query($sql16)or die("Query failed:".mysql_error());
					
				}
			
			
			
			}
			
			
			
		

}


mysql_close($link);
echo "<script>location.href='agent.php?customer_id=".$customer_id_en."';</script>";


/*2016/9/27 hpf end*/
?>