<?php
header("Content-type: text/html; charset=utf-8"); 
require('../config.php');
require('../customer_id_decrypt.php'); //导入文件,获取customer_id_en[加密的customer_id]以及customer_id[已解密]
require('../common/utility_shop.php');
$link = mysql_connect(DB_HOST,DB_USER,DB_PWD);
mysql_select_db(DB_NAME) or die('Could not select database');
	
$callback = $configutil->splash_new($_GET["callback"]);
$batchcode =$configutil->splash_new($_GET["batchcode"]);
$totalprice =$configutil->splash_new($_GET["totalprice"]);

$sql="update weixin_commonshop_changeprices set status=0 where isvalid=true and batchcode='".$batchcode."'";
mysql_query($sql);

$sql="insert into weixin_commonshop_changeprices(batchcode,totalprice,status,isvalid,createtime) values('".$batchcode."',".$totalprice.",1,true,now())";
mysql_query($sql);

//添加订单日志
$username = $_SESSION['username'];
$query = "insert into weixin_commonshop_order_logs(batchcode,operation,descript,operation_user,createtime,isvalid) 
	values('".$batchcode."',3,'平台修改了订单的价格为：".$totalprice."元','".$username."',now(),1)";
mysql_query($query);

$query = "select weixin_fromuser from weixin_users where id  = (select user_id from weixin_commonshop_orders where isvalid = true and batchcode = '".$batchcode."')";
$result = mysql_query($query);
$fromuser = mysql_result($result,0,0);
$content = "订单编号:".$batchcode.",商家已修改了您的订单价格为：".$totalprice."元";
$shopmessage= new shopMessage_Utlity();
$shopmessage->SendMessage($content,$fromuser,$customer_id);
$error =mysql_error();
mysql_close($link);
echo $callback."([{status:1}";
echo "]);";
echo $callback;

?>