<?php
/**
 * Created by PhpStorm.
 * User: Phoenix404
 * Date: 14/10/2017
 * Time: 19:06
 *
 */

echo    "<pre>";
include "./TestsSetting.php";
$file   = $dir."GetTest.php";








// Example 1
// Get method can accept Four args. $url, $params="", $useSSL=true, $secure=true
// $url is ... lol
// $params can be array or string of query,parameters of url. Check next example 2
// $useSSL, by default it is set to true to add cacert.pem file
// $secure, by default it is set to true to verify host and peer

$curl   = new cURLRequester\cURLRequester();
$curl->get("http://mockbin.org/bin/68a5508c-90cb-42dc-9e57-8a499a3b184f/view");
file_put_contents($file, $curl->getResult());

echo '<iframe src="'.$file.'" width="100%" height="30%"></iframe>';







/*
// Example 2
//-----Send Some Parameters,data,values,query,whatever you want to call-----
// $params can be array or a string (foo=bar&foo1=bar&)
$params = array();
$params["Lorem"]    = "IPSUM";
$params["DoLOR"]    = "Sit";
$params["Param1"]   = "Param1Value";
$curl   = new cURLRequester\cURLRequester();
$curl->get("https://requestb.in/1bzoczr1", $params);
file_put_contents($file, $curl->getResult());
echo '<iframe src="'.$file.'" width="100%" height="30%"></iframe>';
*/









/*
// Example 3
//-------------------Send Some Headers-------------------
//setHeaders method accept accept three parameters.  $key, $value="", $headerVal=true
//$key can be an array( of headers with key and values) or key name("connection","keepAlive"..)
//$value is a value of $key if $key is provided as string
//$headerVal, by default is true which means headers is enabled

$headers    = array();
$headers["Connection"]  = "Keep-Alive";
$headers["Keep-Alive"]  = "300";

$curl   = new cURLRequester\cURLRequester();
$curl->setHeaders($headers)->get("https://requestb.in/1bzoczr1");
file_put_contents($file, $curl->getResult());
echo '<iframe src="'.$file.'" width="100%" height="30%"></iframe>';
*/






/*
//Example 4
//-------------------Use Cookies-------------------
// enableCookies method will enable cookies and will write in a file the cookies..
// you can get the file name of cookies by using getCookiesFileName() method after request has performed
// for example $curl->enableCookies()->get("https://requestb.in/1bzoczr1")->getCookiesFileName();
$headers    = array();
$headers["Connection"]  = "Keep-Alive";
$headers["Keep-Alive"]  = "300";

$curl   = new cURLRequester\cURLRequester();
$filename  = $curl->enableCookies()->get("https://requestb.in/1bzoczr1");
file_put_contents($file, $curl->getResult());
echo '<iframe src="'.$file.'" width="100%" height="30%"></iframe>';
echo "\nCookies are ".file_get_contents($curl->getCookiesFileName());
*/




/*
//Example 4
//-------------------Use Cache-------------------
// enableCache method will enable cache and will write cache files in cache directory.
// you can get the file name of cache by using getCacheFileName() method after request has performed
// for example $curl->enableCache()->get("https://requestb.in/1bzoczr1")->getCacheFileName();
$headers    = array();
$headers["Connection"]  = "Keep-Alive";
$headers["Keep-Alive"]  = "300";

$curl   = new cURLRequester\cURLRequester();
//https://requestb.in/1bzoczr1
$filename  = $curl->enableCache()->get("https://requestb.in/1bzoczr1");
file_put_contents($file, $curl->getResult());
echo '<iframe src="'.$file.'" width="100%" height="30%"></iframe>';
echo "\nCache Content is ".file_get_contents($curl->getCacheFileName());
*/






////////////////////////*****************************************
//close the curl instance
$curl->closeCurl();