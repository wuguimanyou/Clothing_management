<?php
header("Content-type:application/json; charset=utf-8"); 
require('../../../config.php');
require('../../../customer_id_decrypt.php'); //导入文件,获取customer_id_en[加密的customer_id]以及customer_id[已解密]
require('../../../common/utility_shop.php');
$link = mysql_connect(DB_HOST,DB_USER,DB_PWD);
mysql_select_db(DB_NAME) or die('Could not select database');

$op =$configutil->splash_new($_GET["op"]); 
$fpage = "";
if(!empty($_GET["fpage"])){
	$fpage = $configutil->splash_new($_GET["fpage"]);
}
if($op == "search"){
	$return_array = array();
	$key = $configutil->splash_new($_GET["searchkey"]);
	if(empty($key)){
		$return_array["state"] = 1;
		$return_array["msg"] = "请先输入用户编号！";
		echo json_encode($return_array);
		return;
	}
	$user_id = $configutil->splash_new($_GET["user_id"]);
	if(empty($user_id)){
		$return_array["state"] = 1;
		$return_array["msg"] = "当前用户的编号为空！";
		echo json_encode($return_array);
		return;
	}
	if($user_id == $key){
		$return_array["state"] = 1;
		$return_array["msg"] = "上级不能为自己";
		echo json_encode($return_array);
		return;
	}
	$query_user  = "select gflag from weixin_users where customer_id = ".$customer_id." and has_change = false and id = ".$user_id." limit 0,1";
	$result_user = mysql_query($query_user) or die("L15 query error : ".mysql_error());
	$gflag = "";
	if($row_user = mysql_fetch_object($result_user)){
		$gflag = $row_user->gflag;
	}
	if(empty($gflag)){
		$return_array["state"] = 1;
		$return_array["msg"] = "当前用户已绑定！";
		echo json_encode($return_array);
		return;
	}
	$gflag = $gflag.$user_id.",";
	$query = "select id , weixin_name ,weixin_headimgurl from weixin_users  where customer_id = ".$customer_id;
	if(!empty($key)){
			$query  = $query." and id = '".$key."' and gflag not like '%".$gflag."%' ";
	}
	//echo "query : ".$query;
	$result = mysql_query($query) or die("L31 query error : ".mysql_error());
	if($row = mysql_fetch_object($result)){
		$return_array["state"] = 0;
		$return_array["user_id"] = $row->id;
		$return_array["weixin_name"] = $row->weixin_name;
		$return_array["headimg"] = $row->weixin_headimgurl;
	}else{
		$return_array["state"] = 2;
		$return_array["msg"] = "未找到相对应用户。请检查输入的用户编号是否正确,并确定不是当前修改用户的下级。";
	}
	echo json_encode($return_array);
	return;
}else{
	$parent = $configutil->splash_new($_POST["re_parent"]);
	$user_id = $configutil->splash_new($_POST["user_id"]);
	$pagenum = $configutil->splash_new($_GET["pagenum"]);
	$cide = $configutil->splash_new($_GET["cide"]);
	$fromw = $configutil->splash_new($_POST["fromw"]);
	$old_parent_id = $configutil->splash_new($_POST["old_parent_id"]);
	if(!empty($parent) && !empty($user_id)){
		$query_user  = "select gflag,generation from weixin_users where customer_id = ".$customer_id." and id = ".$user_id;
		$result_user = mysql_query($query_user) or die("L15 query error : ".mysql_error());
		$gflag = "";
		$old_generation = 1;//自己的代数
		if($row_user = mysql_fetch_object($result_user)){
			$gflag 		= $row_user->gflag;
			$old_generation = $row_user->generation;
		}
		$gflag = $gflag.$user_id.",";
		$query = "select id , gflag , generation from weixin_users where customer_id = ".$customer_id ." and id = ".$parent." and gflag not like '%".$gflag."%'";
		$result = mysql_query($query) or die("L31 query error : ".mysql_error());
		$parent_id = 0;
		$parent_gflag = "";
		$parent_gene = 1;
		if($row = mysql_fetch_object($result)){
			$parent_id = $row->id;
			$parent_gflag = $row->gflag;
			$parent_gene = $row->generation;
		}
		
		if($parent_id > 0){
			
			$new_generation = $parent_gene+1;
			
			$parent_gflag = $parent_gflag.$parent_id.",";
			
			$query_update  = "update weixin_users set parent_id = ".$parent_id." , gflag = '".$parent_gflag."' , generation = ".$new_generation." 
					, is_lock = 1 , has_change = 1 
				where customer_id = ".$customer_id." and id = ".$user_id;
			mysql_query($query_update) or die("L72 query error : ".mysql_error());
			
			$new_gflag = $parent_gflag.$user_id.","; //更新所有的子级
			$add_generation = $my_generation-$old_generation;//更改关系增加的代数 
			$query_update_c = "update weixin_users set gflag=replace(gflag,'".$gflag."','".$new_gflag."') ,generation=generation+".$add_generation."
				where match(gflag) against (',".$user_id.",') and customer_id = ".$customer_id;
			mysql_query($query_update_c) or die("L84 query error : ".mysql_error());
			
			//更新推广员中的
			$query_update_p = "update promoters set parent_id = ".$parent_id." where customer_id = ".$customer_id." and user_id = ".$user_id;
			mysql_query($query_update_p) or die("L881 query error : ".mysql_error());
			
			//插入关系更改表
			/* $query_insert_p = "insert into weixin_commonshop_promoter_changes set 
			user_id = '".$user_id."',
			orgin_user_id=".$old_parent_id.",
			change_user_id=".$parent.",
			isvalid=true,
			createtime=now(),
			customer_id=".$customer_id.",
			remark='粉丝管理更改上级',
			type=".$fromw; */
			
			$query_insert_p = "insert into weixin_commonshop_promoter_changes (user_id,orgin_user_id,change_user_id,isvalid,createtime,customer_id,remark,`type`) values ('" .$user_id. "','" .$old_parent_id. "','".$parent."',true,now(),".$customer_id.",'粉丝管理更改上级','".$fromw."')";
			
			mysql_query($query_insert_p) or die("1L88 query error : ".mysql_error());
			
		}
	}
	
}
		
mysql_close($link);
if(empty($fpage)){
	header("Location:fans.php?customer_id=".$customer_id_en."&pagenum=".$pagenum);
}else{
	header("Location:../../../common_shop/jiushop/order_list_link.php?customer_id=".$customer_id_en);
}
exit();
//echo "<script>location.href='fans.php?customer_id=".$customer_id_en."&pagenum=".$pagenum."';</script>";
?>