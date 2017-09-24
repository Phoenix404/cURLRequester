<?php

echo "<pre>";

require_once __DIR__."/../vendor/autoload.php";

use cURLRequester\cURLEngine;
use cURLRequester\cURLRequester;

$option     = [];
$option1    = array("CURLOPT_RETURNTRANSFER"=>1);
$option2    = array(CURLOPT_RETURNTRANSFER =>1);

//$c      = new cURLEngine("https://www.google.it/?gfe_rd=cr&dcr=0&ei=QqS-WdTWJsj68Ae27YaABg", true);
//$c 	= new cURLRequester("https://www.google.it/?gfe_rd=cr&dcr=0&ei=QqS-WdTWJsj68Ae27YaABg", true);
//$c 	= new cURLRequester("https://www.google.it/?gfe_rd=cr&dcr=0&ei=QqS-WdTWJsj68Ae27YaABg", true);
$c 	= new cURLRequester("http://www.lina24.com/go_develope/", true);
//$c 	= new cURLRequester("https://www.google.it/?gfe_rd=cr&dcr=0&ei=QqS-WdTWJsj68Ae27YaABg", true);
//$c 	= new cURLRequester("", true);

echo "\nUseragent is : ".$c->getUserAgent()."\n";
$c->setUserAgent();


echo "\nUseragent is : ".$c->getUserAgent()."\n";

//$c->enableCache(true);
$c->setCookies();
$c->enableCookies(true);

//unset($c->result);
//print_r($c);

echo "\nCalling whatarecookies\n";
//$data1 = $c->basicRequest("http://www.whatarecookies.com/cookietest.asp");
$data1 = $c->basicRequest();
echo "<textarea rows='10' cols='50'>".$data1."</textarea>";
echo "\nServer is ";
print_r($c->getServerType());
echo "\nCookies are ";
print_r($c->getCookies());

//$c->init_cURL()->reset();
//
//echo "\nCalling Google\n";
//$data2 = $c
//        ->setAutoReferer(true)
//        ->setReferer(true)
//        ->followLocation(true)
//        ->basicRequest("https://www.google.it/?gfe_rd=cr&dcr=0&ei=SEfFWcTsKrLBXoHSnagK&gws_rd=ssl");
//echo "<textarea rows='10' cols='50'>".$data2."</textarea>";
//echo "\nServer is ";
//print_r($c->getServerType());
//echo "\nCookies are ";
//print_r($c->getCookies());


//echo $c->getResult();

