<?php
//Includes
REQUIRE_ONCE("includes/db_config.php");
REQUIRE_ONCE("includes/functions.php");
REQUIRE_ONCE("includes/config.php");
REQUIRE_ONCE("scripts/scripts.php");
REQUIRE_ONCE("skins/" . $conf[skin] . "/skin_conf.php");


//Determine Page Data
//$page="index";
$query = "SELECT MAX(post_num) from posts"; //TEMPORARY: Index page is last post
if($stmt = $sql->prepare($query)){
	$stmt->execute();
    $stmt->bind_result($blog_post);
	$stmt->fetch();
	$stmt->close();
	$page="blog";
}

if(isset($_GET['admin']))
	$page="admin";
if(isset($_GET['post']))
	$page="blog";
	
if($page=="blog"){
	if(isset($_GET['post'])) //TEMPORARY: Index page is last post
		$blog_post=$sql->real_escape_string($_GET['post']);
	
	$query = "SELECT COUNT(*) FROM posts WHERE post_num=?";
	if($stmt = $sql->prepare($query)){
		$stmt->bind_param("i", $blog_post);
		$stmt->execute();
		$stmt->bind_result($blog_numrows);
		$stmt->fetch();
		$stmt->close();
	}

	$page = "404";
	if($blog_numrows){
		$page="blog";
		
		//Request Post Info From SQL
		$query = "SELECT posts.subject, posts.body, posts.time, COUNT(*) AS comments FROM comments JOIN posts ON posts.post_num=comments.post_num WHERE comments.post_num=?";
		if($stmt = $sql->prepare($query)){
			$stmt->bind_param("i", $blog_post);
			$stmt->execute();
			$stmt->bind_result($blog_subject, $blog_body, $blog_timestamp, $blog_num_comments);
			$stmt->fetch();
			$stmt->close();
			$blog_timestamp=strtotime(preg_replace('/:[0-9][0-9][0-9]/','',$blog_timestamp));
		}
	}
}

//Get Number of Blog Entries
$numblogs=mysqli_num_rows($sql->query("SELECT * FROM posts"));

//Add Comment
if(htmlentities($_POST['comment'])!=""){
	$post_comment_post=$sql->real_escape_string($_POST['post']);
	$post_comment_post_numrows=mysqli_num_rows($sql->query("SELECT * FROM posts WHERE post_num='" . $post_comment_post . "'"));
	if($post_comment_post_numrows>0){
		$post_comment=$sql->real_escape_string($_POST['comment']);
		$post_comment_name=$sql->real_escape_string($_POST['commentname']);
		$post_comment_ip=$_SERVER["REMOTE_ADDR"];
		if($post_comment_name=="") $post_comment_name="Anonymous";
		$sql->query("INSERT INTO comments (post_num, poster, comment, ip)
		VALUES ('$post_comment_post', '$post_comment_name', '$post_comment', '$post_comment_ip')");
		if($post_comment_post==$blog_post) $blog_num_comments++;
	}
	else{
		$err_mes="Invalid Post Number";
	}
}

//Start HTML
echo("<!DOCTYPE html PUBLIC \"-//W3C//DTD XHTML 1.1//EN\"
\t\"http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd\">
	
<html lang=\"en\" xml:lang=\"en\" xmlns=\"http://www.w3.org/1999/xhtml\">
\t<head>
\t\t<meta http-equiv=\"Content-Type\" content=\"text/html;charset=utf-8\" />
\t\t<meta name=\"author\" content=\"" . $conf[owner] . "\" />
\t\t<meta name=\"designer\" content=\"codewordBS\" />\n");
foreach($style_css as $x)
	echo("\t\t<link rel=\"stylesheet\" type=\"text/css\" href=\"" . $x . "\" />\n");
foreach($jscripts as $x)
	echo("\t\t<script type=\"text/javascript\" src=\"" . $x . "\"></script>\n");
if($style_fav!=null)
	echo("\t\t<link rel=\"shortcut icon\" type=\"image/ico\" href=\"" . $style_fav . "\" />\n");

/*if($page=="index")
	echo("\t\t<title>" . $conf[title] . "</title>"); //TEMPORARY: Index page is last post
else*/if($page=="blog")
	echo("\t\t<title>" . $conf[title] . " :: " . $blog_subject . "</title>");
elseif($page=="404")
	echo("\t\t<title>" . $conf[title] . " :: Error 404</title>");
elseif($page=="admin")
	echo("\t\t<title>" . $conf[title] . " :: Admin CP</title>");

echo("\n\t</head>

\t<body>\n");

echo("\t\t<div class=\"main\">
\t\t\t<div class=\"head\">
\t\t\t\t<a href=\"" . $conf[root] . "\"><img class=\"blank\" src=\"images/blank.gif\" alt=\"" . $blog_name . "\" /></a>
\t\t\t</div>
\t\t\t<div class=\"content\">\n");

//Side Menu
echo("\t\t\t\t<div class=\"side\">\n");
echo("\t\t\t\t\t<ul class=\"sidelist\">\n");
for($i=$numblogs-1;$i>-1 && $i>$numblogs-26;$i--){
	//Get Blog Post Info From SQL
	$query="SELECT post_num, subject, time FROM posts";
	if($stmt = $sql->prepare($query)){
		$stmt->execute();
		$stmt->bind_result($side_blog_num, $side_blog_subject, $side_blog_timestamp);
		$stmt->store_result();
		$stmt->data_seek($i);
		$stmt->fetch();
		$stmt->close();
		$side_blog_timestamp=date("m/d/y", strtotime(preg_replace('/:[0-9][0-9][0-9]/','',$side_blog_timestamp)));
	}
	echo("\t\t\t\t\t\t<li><a href=\"?post=" . ($side_blog_num) . "\">" . $side_blog_timestamp . " - " . $side_blog_subject . "</a></li>\n");
}
echo("\t\t\t\t</ul>\n");
echo("\t\t\t\t</div>\n");

//Main Content
if ($page=="admin"){
	//echo("Admin page");
	include("includes/admin.php");
}
elseif ($page=="blog"){
	echo("\t\t\t\t<div class=\"body\">\n");
	echo("\t\t\t\t\t<p class=\"subject\">" .$blog_subject . "</p>\n");
	echo("\t\t\t\t\t<p class=\"date\">" . date("l, F jS, Y", $blog_timestamp) . "<br />" . date("g:ia", $blog_timestamp) . " GMT</p>\n");
	echo("\t\t\t\t\t" . $blog_body . "\n\t\t\t\t</div>\n");
}
elseif ($page=="index"){
	echo("Index.");
}
elseif ($page=="404")
		echo "Error 404";
	
echo("\t\t\t</div>\n\t\t</div>\n");

if(isset($blog_num_comments)){
		echo("\t\t<div class=\"commentformblock\">\n");
		echo("\t\t\t<div class=\"commentformtext\">\n");
		echo("\t\t\t\t<form action=\"?post=" . $blog_post . "\" method=\"post\">\n");
		echo("\t\t\t\t\t<p>\n\t\t\t\t\t\t<input type=\"hidden\" name=\"post\" maxlength=\"32\" value=\"" . $blog_post . "\" />\n\t\t\t\t\t\tName: <input type=\"text\" name=\"commentname\" class=\"commentname\" /><br />\n\t\t\t\t\t\t<textarea name=\"comment\" rows=\"6\" cols=\"35\"></textarea><br />\n\t\t\t\t\t\t<input type=\"submit\" value=\"Submit\" />\n\t\t\t\t\t</p>\n");
		echo("\t\t\t\t</form>\n");
		echo("\t\t\t</div>\n");
		echo("\t\t</div>\n");
	for($i=0;$i<$blog_num_comments;$i++){
		echo("\t\t<div class=\"commentblock\">\n");
		
		//Get Comment Info From SQL
		$query="SELECT poster, comment, time, ip FROM comments WHERE post_num=? ORDER BY comment_num";
		if($stmt = $sql->prepare($query)){
			$stmt->bind_param("i", $blog_post);
			$stmt->execute();
			$stmt->bind_result($comment_author, $comment_text, $comment_time, $comment_ip);
			$stmt->store_result();
			$stmt->data_seek($i);
			$stmt->fetch();
			$stmt->close();
			$comment_author=stripslashes($comment_author);
			$comment_text=stripslashes($comment_text);
			$comment_time=date("g:ia n/j/y", strtotime(preg_replace('/:[0-9][0-9][0-9]/','',$comment_time)));
		}

		echo("\t\t\t<p class=\"commenthead\"><span class=\"commentauthor\">" . $comment_author . "</span><span class=\"commenttime\"> @" . $comment_time . " GMT</span></p>\n");
		echo("\t\t\t" . nl2br($comment_text) . "\n");
		echo("\t\t\t<p class=\"commentip\">IP Hash: " . substr(md5($comment_ip), 0, 10) . "</p>\n");
		echo("\t\t</div>\n");
	}
}

echo("\t\t<div class=\"cr\">\n\t\t\tcodewordBS<br />&copy;");
if(date("Y")==2010) echo("2010");
else echo("2010-" . date("Y"));
echo(" <em>CodedByCody</em>.\n\t\t</div>\n");

echo("\t\t<p class=\"valid\">
\t\t\t<a href=\"http://validator.w3.org/check?uri=referer\"><img src=\"http://validator.w3.org/images/valid_icons/valid-xhtml11-blue\" alt=\"Valid XHTML 1.1 Strict\" height=\"31\" width=\"88\" /></a><a href=\"http://jigsaw.w3.org/css-validator/check/referer\"><img src=\"http://jigsaw.w3.org/css-validator/images/vcss-blue\" alt=\"Valid CSS 3\" height=\"31\" width=\"88\" /></a>
\t\t</p>\n");

echo("\t</body>\n</html>");

?>