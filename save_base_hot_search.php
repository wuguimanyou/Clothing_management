<?php
header("Content-type: text/html; charset=utf-8"); 
require('../../config.php');
require('../../customer_id_decrypt.php'); //导入文件,获取customer_id_en[加密的customer_id]以及customer_id[已解密]
require('../../back_init.php');

$link = mysql_connect(DB_HOST,DB_USER,DB_PWD);
mysql_select_db(DB_NAME) or die('Could not select database');

$op = $configutil->splash_new($_GET["op"]);
$name = $configutil->splash_new($_POST["name"]);
$asort = $configutil->splash_new($_POST["asort"]);
$relate_type_id = $configutil->splash_new($_POST["relate_type_id"]);
$keyid = $configutil->splash_new($_POST["keyid"]);

$result = array(
 'code'=>10000,
 'msg'=>''

);

switch ($op){
	case 'add':
		$max_asort = 0;
	    $query="select max(asort) as max_asort from weixin_commonshop_hot_search where isvalid=true and customer_id=".$customer_id."";
		$result=mysql_query($query)or die('Query failed'.mysql_error());
		while($row=mysql_fetch_object($result)){
			$max_asort = $row->max_asort;
		}
		$query = "insert into weixin_commonshop_hot_search(name,customer_id,relate_type_id,asort,createtime,isvalid)values('".$name."',".$customer_id.",".$relate_type_id.",".($max_asort+1).",now(),true)";
		//echo $query;
		mysql_query($query)or die('L17 Query failed'.mysql_error());
		$error = mysql_error();
		if($error == 0){
		$result = array(
			 'code'=>10001,
			 'msg'=>'添加成功！'

			);
		}else{
			$result = array(
			 'code'=>10004,
			 'msg'=>'添加失败！'
			);
			
		}	
	break;
	case 'update':
		$query = "update weixin_commonshop_hot_search set name = '".$name."',relate_type_id = ".$relate_type_id." where isvalid=true and customer_id=".$customer_id." and id=".$keyid."";
		//echo $query;
		mysql_query($query)or die('L17 Query failed'.mysql_error());
		$error = mysql_error();
		if($error == 0){
		$result = array(
			 'code'=>10002,
			 'msg'=>'更改成功！'

			);
		}else{
			$result = array(
			 'code'=>10004,
			 'msg'=>'更改失败！'
			);
			
		}
	break;
	case 'del':
		$query = "update weixin_commonshop_hot_search set isvalid=false where isvalid=true and customer_id=".$customer_id." and id=".$keyid."";
		//echo $query;
		mysql_query($query)or die('L17 Query failed'.mysql_error());
		$error = mysql_error();
		if($error == 0){
		$result = array(
			 'code'=>10003,
			 'msg'=>'删除成功'

			);
		}else{
			$result = array(
			 'code'=>10004,
			 'msg'=>'删除失败！'
			);
			
		}
	break;
	case 'detail':
		$query="select * from weixin_commonshop_hot_search where isvalid=true and customer_id=".$customer_id." and id=".$keyid."";
		$result=mysql_query($query)or die('Query failed'.mysql_error());
		$id = 0;
		$name = '';
		$relate_type_id = -1;
		$asort = 0;
		
		while($row=mysql_fetch_object($result)){
			$id 			= $row->id;
			$name 			= $row->name;
			$relate_type_id = $row->relate_type_id;
			$asort 			= $row->asort;
		}
		$result = array(
			 'code'=>10005,
			 'msg'=>'success',
			 'name'=>$name,
			 'relate_type_id'=>$relate_type_id,
			 'asort'=>$asort,

			);
	break;
	case 'save_sort':
	   $json_sortdata = $configutil->splash_new($_POST["sortdata"]);
	   //var_dump($json_sortdata);
	   $sortdata = json_decode($json_sortdata,true);
	   //var_dump($sortdata);
	   $query = 'update weixin_commonshop_hot_search set asort = case id ';
	   $id = "";
		foreach($sortdata as $k=> $values){
			//var_dump($values); 
			$query .= "When ".$values[0]." Then ".$values[1]." ";
			$id .= $values[0].",";
		}
		$id = substr($id,0,-1);
		$query .= " end where id in (".$id.")";
		
		//echo $query;
		mysql_query($query)or die('L17 Query failed'.mysql_error());
		$error = mysql_error();
		if($error == 0){
		$result = array(
			 'code'=>10006,
			 'msg'=>'保存成功'

			);
		}else{
			$result = array(
			 'code'=>10004,
			 'msg'=>'保存失败！'
			);
			
		}
	break;	
}

$out = json_encode($result);
echo $out;
mysql_close($link);
 

?>