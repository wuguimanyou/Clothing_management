<?php
header("Content-type: text/html; charset=utf-8"); 
set_time_limit(0); 
require('../../../config.php');
$link = mysql_connect(DB_HOST, DB_USER, DB_PWD);
mysql_select_db(DB_NAME) or die('Could not select database');
mysql_query("SET NAMES UTF8");
//print_r($_POST);
//echo json_encode($_POST);

//$items_params_json = json_encode($_POST['print_items_params']);
//print_r($items_params_json);die();
if(isset($_POST['re_url']) && (!empty($_POST['re_url']))){$re_url = $_POST['re_url'];}else{$re_url = 'express.php';}

if(isset($_POST['temp_id']) && (is_numeric($_POST['temp_id']))){
	$id = $_POST['temp_id'];	
	$sql_upset_print_temp = "UPDATE `weixin_print_temp` SET `print_name` = '".addslashes($_POST['print_temp_name'])."', `paper_width` = ".intval($_POST['printing_paper_width']).", `paper_height` = ".intval($_POST['printing_paper_height']).", `base_temp_img` = '".addslashes($_POST['img_url'])."', `items_params`='".addslashes(encode2json($_POST['print_items_params']))."' WHERE `id` = $id;";
	mysql_query($sql_upset_print_temp) or die('Query failed: ' . mysql_error()); 	
	echo '<script language="javascript">alert("修改完成");location.href="'.$re_url.'";</script>';
}else{
	if(isset($_GET['do']) && ($_GET['do'] == 'del')){
		$sql_print_temp = "UPDATE `weixin_print_temp` SET `isvalid` = '-1' WHERE `id` = ".$_GET['id']."";
		echo '<script language="javascript">alert("删除完成");location.href="'.$_SERVER['HTTP_REFERER'].'";</script>';
	}else{
		$sql_print_temp = "INSERT INTO `weixin_print_temp` (`id`, `print_name`, `paper_width`, `paper_height`, `base_temp_img`, `items_params`, `customer_id`) VALUES (NULL, '".addslashes($_POST['print_temp_name'])."', ".intval($_POST['printing_paper_width']).", ".intval($_POST['printing_paper_height']).", '".addslashes($_POST['img_url'])."', '".addslashes(encode2json($_POST['print_items_params']))."', ".intval($_POST['customer_id']).")";
		echo '<script language="javascript">alert("添加完成");location.href="'.$re_url.'";</script>';
	}
	mysql_query($sql_print_temp) or die('Query failed: ' . mysql_error()); 
	//print_r($sql_print_temp);
	
}

mysql_close($link);  


function encode2json($print_items_params){
    $items_params_array = $print_items_params; $items_lists = array();
    foreach ($items_params_array as $key => $value) {
        $items_lists[] = json_decode(urldecode($value));
    }
    //print_r(json_encode($items_lists));die();
    return json_encode($items_lists);
}  
?>