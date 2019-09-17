<?php
header("Content-type: text/html; charset=utf-8"); 
require('../config.php');
require('../customer_id_decrypt.php'); //导入文件,获取customer_id_en[加密的customer_id]以及customer_id[已解密]
require('../back_init.php');
require('../common/utility.php');
require('../common/utility_4m.php');
$link = mysql_connect(DB_HOST,DB_USER,DB_PWD);
mysql_select_db(DB_NAME) or die('Could not select database');

//$customer_id =$configutil->splash_new($_GET["customer_id"]); // 上面的文件中已经获取了customer_id并解密
$template_id =$configutil->splash_new($_GET["template_id"]);
$name =$configutil->splash_new($_POST["name"]);
$chk_submenu =$configutil->splash_new($_POST["chk_submenu"]);
$navigation_id =$configutil->splash_new($_POST["navigation_id"]);
$subpros =$configutil->splash_new($_POST["subpro"]);
$sublinks_id =$configutil->splash_new($_POST["sublinks_id"]);
$navigation_link="";
$sublinks="";
$keyid=0;
$keyid =$configutil->splash_new($_POST["keyid"]);
$op="";
$op =$configutil->splash_new($_GET["op"]); 
if($op=="del"){
	$id =$configutil->splash_new($_GET["id"]);
	mysql_query("update weixin_commonshop_userdefined_nav set isvalid=false where id=".$id) or die('QUERY faild_del:'.mysql_error());
	mysql_close($link);
// echo $error; 
 echo "<script>location.href='defaultset.php?customer_id=".$customer_id_en."&template_id=".$template_id."';</script>";
}
$linktype=1;
$url="";
//导航链接
if($chk_submenu==0){
	if($navigation_id>0){	
		$typestrarr= explode("_",$navigation_id);
		$navigation_id = $typestrarr[0];
		$linktype=$typestrarr[1];
		if($linktype==1){				
				$query3="select name from weixin_commonshop_types where isvalid=true and id=".$navigation_id;
				$result3 = mysql_query($query3) or die('Query failed: ' . mysql_error());
				$typename="";
				while ($row3 = mysql_fetch_object($result3)) {
				   $typename = $row3->name;
				}
				$url="list.php?customer_id=".$customer_id_en."&tid=".$navigation_id."&tname=".$typename;
		}else if($linktype==2){
		   //图文
			$query = "SELECT id,website_url FROM weixin_subscribes where customer_id=".$customer_id." and  id=".$navigation_id;
			$result = mysql_query($query) or die('Query failed: ' . mysql_error());
			$website_url="";
			while ($row = mysql_fetch_object($result)) {
			   $website_url = $row->website_url;
			}
			$pos = strpos($website_url,"?"); 
			if($pos>0){
			   $website_url = $website_url."&C_id=".$customer_id_en;
			}else{
			   $website_url = $website_url."?C_id=".$customer_id_en;
			}
			$url = $website_url;
		}		 
	 }else{
	   switch($navigation_id){
			case -6:
			  $url="list.php?customer_id=".$customer_id_en;
			  break;
		   case -2:
			  $url="list.php?isnew=1&customer_id=".$customer_id_en;
			  break;
		   case -3:
			  $url="list.php?ishot=1&customer_id=".$customer_id_en;
			  break;
		   case -4:
			  $url="order_cart.php?customer_id=".$customer_id_en;
			  break;
		   case -7:
			  $url="class_page.php?customer_id=".$customer_id_en;
			  break;
		   case -8:
			  $url="order_list.php?customer_id=".$customer_id_en;
			  break;
		   case -10:
			  $url="countdown.php?customer_id=".$customer_id_en;  
			  break;
			    
	   }
	}
	$navigation_link=$url;
}else if($chk_submenu==1){
	if($navigation_id>0){
		$typestrarr= explode("_",$navigation_id);
		$navigation_id = $typestrarr[0];
	}
	//子菜单链接
	$pandun=0;
	$pandun=strpos($sublinks_id,"#");
	$type_submenu=array();
	if($pandun>0){
		$type_submenu=explode("#",$sublinks_id);
	}else{
		$type_submenu[0]=$sublinks_id;
	}
	$count=count($type_submenu);
	$submenu_links="";
	for($i=0;$i<$count;$i++){
		if($type_submenu[$i]>0){		    
			$typestrarr= explode("_",$type_submenu[$i]);
			$type_submenu[$i] = $typestrarr[0];
			$linktype=$typestrarr[1];
			if($linktype==1){				
					$query3="select name from weixin_commonshop_types where isvalid=true and id=".$type_submenu[$i];
					$result3 = mysql_query($query3) or die('Query failed: ' . mysql_error());
					$typename="";
					while ($row3 = mysql_fetch_object($result3)) {
					   $typename = $row3->name;
					}
					$url="list.php?customer_id=".$customer_id_en."&tid=".$type_submenu[$i]."&tname=".$typename;
			}else if($linktype==2){
			   //图文
				$query = "SELECT id,website_url FROM weixin_subscribes where customer_id=".$customer_id." and  id=".$type_submenu[$i];
				$result = mysql_query($query) or die('Query failed: ' . mysql_error());
				$website_url="";
				while ($row = mysql_fetch_object($result)) {
				   $website_url = $row->website_url;
				}
				$pos = strpos($website_url,"?"); 
				if($pos>0){
				   $website_url = $website_url."&C_id=".$customer_id_en;
				}else{
				   $website_url = $website_url."?C_id=".$customer_id_en;
				}
				$url = $website_url;
			}		 
		}else{
		   switch($type_submenu[$i]){
				case -6:
				  $url="list.php?customer_id=".$customer_id_en;
				  break;
			   case -2:
				  $url="list.php?isnew=1&customer_id=".$customer_id_en;
				  break;
			   case -3:
				  $url="list.php?ishot=1&customer_id=".$customer_id_en;
				  break;
			   case -4:
				  $url="order_cart.php?customer_id=".$customer_id_en;
				  break;
			   case -7:
				  $url="class_page.php?customer_id=".$customer_id_en;
				  break;
			   case -8:
				  $url="order_list.php?customer_id=".$customer_id_en;
				  break;
			   case -10:
				  $url="countdown.php?customer_id=".$customer_id_en;    
				  break;
				  
			}
		}
		$submenu_links=$submenu_links."#".$url;
	}
	$submenu_links=substr($submenu_links,1);
	$sublinks=$submenu_links;

}

if($keyid>0){
	mysql_query("update weixin_commonshop_userdefined_nav set customer_id=".$customer_id.",template_id=".$template_id.",name='".$name."',chk_submenu=".$chk_submenu.",navigation_id=".$navigation_id.",subpros='".$subpros."',sublinks='".$sublinks."',navigation_link='".$navigation_link."',sublinks_id='".$sublinks_id."' where id=".$keyid) or die('QUERY faild_edit:'.mysql_error()); 
}else{
	$query="insert into weixin_commonshop_userdefined_nav(customer_id,template_id,name,chk_submenu,navigation_id,subpros,sublinks,isvalid,navigation_link,sublinks_id) values (".$customer_id.",".$template_id.",'".$name."',".$chk_submenu.",".$navigation_id.",'".$subpros."','".$sublinks."',true,'".$navigation_link."','".$sublinks_id."')";
	mysql_query($query) or die('QUERY faild_1:'.mysql_error());
}




 mysql_close($link);
// echo $error; 
 echo "<script>location.href='defaultset.php?customer_id=".$customer_id_en."&template_id=".$template_id."';</script>";
?>