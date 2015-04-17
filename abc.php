<?php
 //文件下载,下载一张图片
 //$file_name="Angel.mp3";  
  $file_name=$_REQUEST['name'];  //出现中文 程序无法完成下载 提示：文件不存在 
 //对文件进行转码(PHP文件函数 比较古老 需对中文码转成 gb2312)
 //iconv — Convert string to requested character encoding
 //by www.jb51.net
 //$file_name=iconv("utf-8","gb2312",$file_name); 
  //设置文件下载路径(相对路径)
 //$file_path="./dowm/".$file_name; 
  //使用绝对路径
 $file_path=__DIR__."/download/".$file_name; 
 //echo $file_path;
  //打开文件---先判断再操作
 if(!file_exists($file_path)){

  echo "not exist";
  return ; //直接退出
 } 
  //存在--打开文件 
  $fp=fopen($file_path,"r");

 //获取文件大小
 $file_size=filesize($file_path); 
  //http 下载需要的响应头 
 header("Content-type: application/octet-stream"); //返回的文件 
 header("Accept-Ranges: bytes");   //按照字节大小返回
 header("Accept-Length: $file_size"); //返回文件大小
 header("Content-Disposition: attachment; filename=".$file_name);//这里客户端的弹出对话框，对应的文件名 

  //向客户端返回数据
 //设置大小输出
 $buffer=1024; 
  //为了下载安全，我们最好做一个文件字节读取计数器
 $file_count=0;
 //判断文件指针是否到了文件结束的位置(读取文件是否结束)
 while(!feof($fp) && ($file_size-$file_count)>0){ 
   $file_data=fread($fp,$buffer);
  //统计读取多少个字节数
  $file_count+=$buffer;
  //把部分数据返回给浏览器
  echo $file_data;
 }
 //关闭文件 
  fclose($fp);
 