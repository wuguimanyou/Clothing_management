<?php 
	header("Content-type: text/html; charset=utf-8"); 
	require('../../../config.php');
    require('../../../customer_id_decrypt.php'); //导入文件,获取customer_id_en[加密的customer_id]以及customer_id[已解密]
	require('../../../back_init.php');
	$link = mysql_connect(DB_HOST,DB_USER, DB_PWD);
	mysql_select_db(DB_NAME) or die('Could not select database');
	mysql_query("SET NAMES UTF8");
	require('../../../proxy_info.php');
	
	$charity_id = -1;

  if(!empty($_POST["charity_id"])){
    $charity_id = $configutil->splash_new($_POST["charity_id"]);
  }
	$charity_name="";
	
	$charity_name=$_POST['charity_name'];
	
	if($charity_id>0){
			
				
			$query="update charitable_charity_t set 
			charity_name='".$charity_name."' where id=".$charity_id."";
			//echo $query;
			mysql_query($query) or die('Query failed1: ' . mysql_error()); 
		
	}else{
			
			$query="insert into charitable_charity_t(customer_id,charity_name,isvalid)values(".$customer_id.",'".$charity_name."',true)";
			//echo $query;
			mysql_query($query) or die('Query failed2: ' . mysql_error()); 	
			
				
		}
		
	echo "<script>location.href='charity.php?customer_id=".$customer_id_en."';</script>";
	
?>