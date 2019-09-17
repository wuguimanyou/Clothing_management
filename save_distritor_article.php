<?php
header("Content-type: text/html; charset=utf-8"); 
require('../config.php');
require('../back_init.php');
require('../common/utility.php');
$link = mysql_connect(DB_HOST,DB_USER,DB_PWD);
mysql_select_db(DB_NAME) or die('Could not select database');

$uptypes=array('image/jpg', //上传文件类型列表
'image/jpeg',
'image/png',
'image/pjpeg',
'image/gif',
'image/bmp',
'image/x-png');
$max_file_size=500000; //上传文件大小限制, 单位BYTE
$path_parts=pathinfo($_SERVER['PHP_SELF']); //取得当前路径
$destination_folder="up/distributor_article/"; //上传文件路径

$key_id =$configutil->splash_new($_POST["key_id"]);
$description =$configutil->splash_new($_POST["description"]);
$title =$configutil->splash_new($_POST["title"]);
$customer_id =$configutil->splash_new($_POST["customer_id"]);
require('../customer_id_decrypt.php'); //导入文件,获取customer_id_en[加密的customer_id]以及customer_id[已解密]
$share_description =$configutil->splash_new($_POST["share_description"]);
$p_id =$configutil->splash_new($_POST["p_id"]);
$time=time();

if (!is_uploaded_file($_FILES["upfile"]["tmp_name"]))
	//是否存在文件
	{
	  $query1="update weixin_commonshop_distributor_article set description='$description', title='$title',time=$time,share_description='$share_description',p_id=$p_id where id=$key_id";
	  $query2="insert into weixin_commonshop_distributor_article (customer_id,description,title,time,share_description,p_id) values ($customer_id,'$description','$title',$time,'$share_description',$p_id)";

	}else{
		$file = $_FILES["upfile"];
		if($max_file_size < $file["size"])
		//检查文件大小
		{
			echo "<font color='red'>文件太大！</font>";
			exit;
		}
		if(!in_array($file["type"], $uptypes))
		//检查文件类型
		{
		  echo "<font color='red'>不能上传此类型文件！</font>";
		  exit;
		}
		if(!file_exists($destination_folder)){
		   mkdir($destination_folder);
		}

		  $filename=$file["tmp_name"];

		  $image_size = getimagesize($filename);

		  $pinfo=pathinfo($file["name"]);

		  $ftype=$pinfo["extension"];
		  $destination = $destination_folder.time().mt_rand(0,1000).".".$ftype;
		  if (file_exists($destination))
		  {
			 echo "<font color='red'>同名文件已经存在了！</a>";
			 exit;
		   }
		  if(!move_uploaded_file ($filename, $destination))
		  {
			 echo "<font color='red'>移动文件出错！</a>";
			 exit;
		  }
		    $destination='/'.$destination;
		    $query1="update weixin_commonshop_distributor_article set description='$description', title='$title',time=$time,share_description='$share_description',p_id=$p_id,share_img='$destination' where id=$key_id";
			$query2="insert into weixin_commonshop_distributor_article (customer_id,description,title,time,share_description,p_id,share_img) values ($customer_id,'$description','$title',$time,'$share_description',$p_id,'$destination')";
  }	


	if($key_id){
			$query=$query1;
	}else{
		$query=$query2;
	}
	mysql_query($query)or die('Query failed1: ' . mysql_error()); 


$error =mysql_error();
mysql_close($link);
if($error){
	echo '插入数据库失败'.$error; 
}else{
 echo "<h1>操作成功,页面跳转中...</h1><script>location.href='distributor_article.php?customer_id=".$customer_id_en."';</script>" ;

}


?>