<?php
header("Content-type: text/html; charset=utf-8"); 
require('../../../config.php');
require('../../../customer_id_decrypt.php'); //导入文件,获取customer_id_en[加密的customer_id]以及customer_id[已解密]
require('../../../common/utility_shop.php');
$link = mysql_connect(DB_HOST,DB_USER,DB_PWD);
mysql_select_db(DB_NAME) or die('Could not select database');


$pagenum = $configutil->splash_new($_GET["pagenum"]);
$is_consume =$configutil->splash_new($_POST["is_consume"]);
$user_id =$configutil->splash_new($_GET["user_id"]);
$parent_id =$configutil->splash_new($_GET["parent_id"]);
$ois_consume =$configutil->splash_new($_GET["ois_consume"]);


$sql="update promoters set is_consume=".$is_consume." where isvalid=true and status=1 and user_id=".$user_id;
mysql_query($sql);
switch($is_consume){
	case 0:
		switch($ois_consume){
			case 1:
				$status = 5;
				break;
			case 2:
				$status = 6;
				break;
			case 3:
				$status = 7;
				break;
			case 4:
				$status = 8;
				break;
		}
		break;
	case 1:
		$status = 1;
		break;
	case 2:
		$status = 2;
		break;
	case 3:
		$status = 3;
		break;
	case 4:
		$status = 4;
		break;
}
if( $is_consume != $ois_consume ){
	$shopmessage = new shopMessage_Utlity(); 
	$shopmessage -> ChangeRelation_new($user_id,$parent_id,$parent_id,$customer_id,4,$status);
}

mysql_close($link);

echo "<script>location.href='promoter.php?customer_id=".$customer_id_en."&pagenum=".$pagenum."';</script>";
?>