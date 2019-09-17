<?php
 header("Content-type: text/html; charset=utf-8"); 
  require('../config.php');

$uptypes=array('image/jpg', //上传文件类型列表
'image/jpeg',
'image/png',
'image/pjpeg',
'image/gif',
'image/bmp',
'image/x-png');
$max_file_size=1000000; //上传文件大小限制, 单位BYTE


$product_id = $configutil->splash_new($_GET["product_id"]);


$path_parts=pathinfo($_SERVER['PHP_SELF']); //取得当前路径
$destination_folder="../up/pro_logo/"; //上传文件路径
if(!file_exists($destination_folder))
  mkdir($destination_folder);
$destination_folder = $destination_folder.$customer_id."/";  
echo $destination_folder;
if(!file_exists($destination_folder))
  mkdir($destination_folder);

//$watermark=1; //是否附加水印(1为加水印,0为不加水印);
//$watertype=1; //水印类型(1为文字,2为图片)
//$waterposition=2; //水印位置(1为左下角,2为右下角,3为左上角,4为右上角,5为居中);
//$waterstring="www.tt365.org"; //水印字符串
//$waterimg="xplore.gif"; //水印图片
$imgpreview=1; //是否生成预览图(1为生成,0为不生成);
$imgpreviewsize=1/1; //缩略图比例
$destination = "";
 $keyid =$configutil->splash_new($_POST["keyid"]);
 $pt_id =$configutil->splash_new($_POST["pt_id"]);
if ($_SERVER['REQUEST_METHOD'] == 'POST')
{
	if (!is_uploaded_file($_FILES["upfile"]["tmp_name"]))
	//是否存在文件
	{
	   if($keyid<=0){
		   echo "<font color='red'>文件不存在！</font>";
		   exit;
	   }else{
	       $destination = $_POST["type_imgurl"];
	   }
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
		  // echo "folder=====".$destination_folder;
		   mkdir($destination_folder);
		  
		 }

		  $filename=$file["tmp_name"];

		  $image_size = getimagesize($filename);

		  $pinfo=pathinfo($file["name"]);

		  $ftype=$pinfo["extension"];
		  $destination = $destination_folder.time().".".$ftype;
		  $overwrite=true;
		  if (file_exists($destination) && $overwrite != true)
		  {
			 echo "<font color='red'>同名文件已经存在了！</a>";
			 exit;
		   }
		  if(!move_uploaded_file ($filename, $destination))
		  {
		     echo $filename."<br/>";
			 echo $destination."<br/>";
			 echo "<font color='red'>移动文件出错！</a>";
			 exit;
		  }
		  $pinfo=pathinfo($destination);
		  $fname=$pinfo["basename"];
	//   echo " <font color=red>成功上传,鼠标移动到地址栏自动复制</font><br><table width=\"348\" cellspacing=\"0\" cellpadding=\"5\" border=\"0\" class=\"table_decoration\" align=\"center\"><tr><td><input type=\"checkbox\" id=\"fmt\" onclick=\"select_format()\"/>图片UBB代码<br/><div id=\"site\"><table border=\"0\"><tr><td valign=\"top\">文件地址:</td><td><input type=\"text\" onclick=\"sendtof(this.value)\" onmouseover=\"oCopy(this)\" style=font-size=9pt;color:blue size=\"44\" value=\"http://".$_SERVER['SERVER_NAME'].$path_parts["dirname"]."/".$destination_folder.$fname."\"/>
	  //  </td></tr></table></div><div id=\"sited\" style=\"display:none\"><table border=\"0\"><tr><td valign=\"top\">文件地址:</td><td><input type=\"text\" onclick=\"sendtof(this.value)\" onmouseover=\"oCopy(this)\" style=font-size=9pt;color:blue size=\"44\" value=\"[img]http://".$_SERVER['SERVER_NAME'].$path_parts["dirname"]."/".$destination_folder.$fname."[/img]\"/></td></tr></table></div></td></tr></table>";
		  //echo " 宽度:".$image_size[0];
		 // echo " 长度:".$image_size[1];

  }
  
 $link = mysql_connect(DB_HOST,DB_USER,DB_PWD);
 mysql_select_db(DB_NAME) or die('Could not select database');

 $error =mysql_error();
 mysql_close($link);
 
 //echo $parent_id;
 //echo $destination;
 echo "<script>location.href='product_type_logo.php?customer_id=".$customer_id."&type_imgurl=".$destination."&keyid=".$keyid."';</script>";
 }
?>