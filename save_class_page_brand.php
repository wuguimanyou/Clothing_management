<?php
header("Content-type: text/html; charset=utf-8"); 
require('../config.php');
$link = mysql_connect(DB_HOST,DB_USER,DB_PWD);
mysql_select_db(DB_NAME) or die('Could not select database');
$pt_id1 =$configutil->splash_new($_POST["pt_id"]);
$customer_id =$configutil->splash_new($_POST["customer_id"]);
require('../customer_id_decrypt.php'); //导入文件,获取customer_id_en[加密的customer_id]以及customer_id[已解密]
require('../proxy_info.php'); //解决fenxiao无法获取正常路径
$owner_typeids =$configutil->splash_new($_POST["owner_typeids"]);
$new_baseurl = "http://".$http_host; //新商城图片显示

$i=0;
$data=array();
$brand="select user_id,customer_id,brand_logo from weixin_commonshop_brand_supplys where isvalid=true and brand_status=1 and customer_id=".$customer_id." order by brand_opentime asc";//搜索暂时先按开店时间来排序
$brand_result=mysql_query($brand) or die ('brand faild '.mysql_error());
while($row=mysql_fetch_object($brand_result)){
	$data[$i]['user_id']=$row->user_id;
	$data[$i]['brand_logo']=$row->brand_logo;
	$data[$i]['customer_id']=$row->customer_id;
	$i++;
}




$data=json_encode($data);
//$data="[".$data."]";
echo $data;

?>