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
//echo $pt_id;
//$pt_id =$configutil->splash_new($_GET["cid"]);

//V7.0分类新排序
$sort_str="";
$type_sort="select sort_str from weixin_commonshop_type_sort where customer_id=".$customer_id."";
$result_type=mysql_query($type_sort) or die ('type_sort faild' .mysql_error());
while($row=mysql_fetch_object($result_type)){
   $sort_str=$row->sort_str;									   
}


if($pt_id1>0){
	//$pt_id1>0是分类
	$query= "select id,name,imgurl,type_adimg,type_adurl from weixin_commonshop_types where isvalid=true and id=".$pt_id1." and parent_id=-1 and is_shelves=1 and customer_id=".$customer_id;
}else{
	//全部分类
	$query= "select id,name,imgurl from weixin_commonshop_types where isvalid=true and parent_id=-1 and is_shelves=1 and customer_id=".$customer_id;
}
if(!empty($owner_typeids)){
	$query = $query." and id in(".$owner_typeids.")";
}
	if($sort_str){
				 $query =$query.' order by field(id'.$sort_str.')';  
				 } 
$result = mysql_query($query) or die('Query failed: ' . mysql_error());
$data=array();
$data1=array();
$j=0;
while ($row = mysql_fetch_object($result)) {
	$pt_id = $row->id;
	$pt_name = $row->name;
	$pt_imgurl = $row->imgurl;
	$pt_type_adimg = $row->type_adimg;
	$pt_type_adurl = $row->type_adurl;
	if(!$pt_type_adurl){
		$pt_type_adurl="javascript:";
	}
	if(!$pt_imgurl){//图片缺失就默认一张图片
		$pt_imgurl="/weixinpl/mshop/images/imgmissing.png";
	}
	$i=0;
	$num=0;
	$query1= "select id,count(1) as num from weixin_commonshop_types where isvalid=true and parent_id=".$pt_id." and is_shelves=1 and customer_id=".$customer_id;
	if(!empty($owner_typeids)){
		$query1 = $query1." and id in(".$owner_typeids.")";
	} 
	$result1 = mysql_query($query1) or die('Query failed: ' . mysql_error());
	while ($row1 = mysql_fetch_object($result1)) {
		$num=$row1->num;	
	}
	/* $count1 = mysql_num_rows($result1); */
	//判断是否有2级分类
	if($num>0){
		$data1[$j]['gc_id'] = $pt_id;//1级分类id
		$data1[$j]['gc_name'] = $pt_name;//1级分类名字
		$data1[$i]['type_adimg'] = $pt_type_adimg;
		$data1[$i]['type_adurl'] = $pt_type_adurl;
		$data2=array();
		$sql= "select id,name,imgurl from weixin_commonshop_types where isvalid=true and parent_id=".$pt_id." and is_shelves=1 and customer_id=".$customer_id;
		if(!empty($owner_typeids)){
			$sql = $sql." and id in(".$owner_typeids.")";
		}
	
		$result2 = mysql_query($sql) or die('Query failed: ' . mysql_error());
		while ($row2 = mysql_fetch_object($result2)) {
			$pt_id2 = $row2->id;
			$pt_imgurl2 = $row2->imgurl;
			if(!$pt_imgurl2){//图片缺失就默认一张图片
				$pt_imgurl2="/weixinpl/mshop/images/imgmissing.png";
			}
			$pt_name2 = $row2->name;
			$data2[$i]['gb_id'] = $pt_id2; 
			//$data2[$i]['gb_logo'] = BaseURL."common_shop/".$pt_imgurl2;
			$data2[$i]['gb_logo'] = $new_baseurl.$pt_imgurl2;
			$data2[$i]['gb_name'] = $pt_name2;
			$i++;
			if(!empty($pt_id2)){
				//查找3级分类
				$sql2= "select id,name,imgurl from weixin_commonshop_types where isvalid=true and parent_id=".$pt_id2." and is_shelves=1 and customer_id=".$customer_id;
				if(!empty($owner_typeids)){
					$sql2 = $sql2." and id in(".$owner_typeids.")";
				}
				
				$result3 = mysql_query($sql2) or die('Query failedsql2: ' . mysql_error());
				while ($row3 = mysql_fetch_object($result3)) {
					$pt_id3 = $row3->id;
					$pt_imgurl3 = $row3->imgurl;
					if(!$pt_imgurl3){//图片缺失就默认一张图片
						$pt_imgurl3="/weixinpl/mshop/images/imgmissing.png";
					}
					$pt_name3 = $row3->name;
					$data2[$i]['gb_id'] = $pt_id3;
					//$data2[$i]['gb_logo'] = BaseURL."common_shop/".$pt_imgurl3;
					$data2[$i]['gb_logo'] = $new_baseurl.$pt_imgurl3;
					$data2[$i]['gb_name'] = $pt_name3;
					$i++;
				}
			}
		}
		$data1[$j]['brandinfo'] = $data2;
		$j++;	
	}else{
		//没有2级分类就显示1级分类
		$data1[$j]['gc_id'] = $pt_id;
		$data1[$j]['gc_name'] = $pt_name;
		$data2=array();
		$data2[$i]['gb_id'] = $pt_id;
		//$data2[$i]['gb_logo'] = BaseURL."common_shop/".$pt_imgurl;
		$data2[$i]['gb_logo'] = $new_baseurl.$pt_imgurl;
		$data1[$i]['type_adimg'] = $pt_type_adimg;
		$data1[$i]['type_adurl'] = $pt_type_adurl;
		$data2[$i]['gb_name'] = $pt_name;
		$data1[$j]['brandinfo'] = $data2;
		$j++;
	}
}

$data=json_encode($data1);
//$data="[".$data."]";
echo $data;

?>