<?php

echo "<pre>";
require_once __DIR__."/../vendor/autoload.php";

use cURLRequester\cURLEngine;
use cURLRequester\cURLRequester;

$option     = [];
$option1    = array("CURLOPT_RETURNTRANSFER"=>1);
$option2    = array(CURLOPT_RETURNTRANSFER =>1);

//$c 	= new cURLRequester("https://www.google.it/?gfe_rd=cr&dcr=0&ei=QqS-WdTWJsj68Ae27YaABg", true);
//$c 	= new cURLRequester("https://www.google.it/?gfe_rd=cr&dcr=0&ei=QqS-WdTWJsj68Ae27YaABg", true);
//$c 	= new cURLRequester("http://php.net/manual/en/features.commandline.options.php");
$c      = new cURLEngine("https://www.google.it/?gfe_rd=cr&dcr=0&ei=QqS-WdTWJsj68Ae27YaABg", true);
print_r($c);
/*
//$c->setUserAgent();
$c->enableCache(true);
//$c->setOpt("CURLOPT_COOKIE", true);
//$c->enableCookies(true);

//print_r($c);

$data = $c->basicRequest("http://www.whatarecookies.com/", true);
///*
unset($c->result);
print_r($c);
//* /
echo "\nCalling whatarecookies\n";
//$data = $c->basicRequest("http://www.whatarecookies.com/cookietest.asp");
//$data = $c->invoke();
echo "<textarea rows='10' cols='100'>".$data."</textarea>";
echo "\nServer is ";
//print_r($c->getServerType());

//echo "\nCalling Google\n";
//$data = $c->basicRequest("https://www.google.it/?gfe_rd=cr&dcr=0&ei=QqS-WdTWJsj68Ae27YaABg", true);
//echo "<br/><textarea row='15' cols='100'>".$data."</textarea>";
//echo "\nServer is ";
//print_r($c->getServerType());


//echo $c->getResult();
