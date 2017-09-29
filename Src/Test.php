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


//$c->setUserAgent();
//$c->enableCache(true);
//$c->setCookies();
$c->enableCookies(true);
//unset($c->result);
//print_r($c);

echo "\nCalling what are cookies\n";
$data1 = $c->basicRequest("http://www.whatarecookies.com/cookietest.asp");

$c->setUserAgent();
echo "\nUseragent is : ".$c->getUserAgent()."\n";

//unset($c->result);
//print_r($c);

$data1 = $c->basicRequest();
echo "<textarea rows='8' cols='80'>".$data1."</textarea>";
echo "\nServer is ";print_r($c->getServerType());
echo "\nCookies are ";print_r($c->getCookies());
echo "\nHttp Code: ";print_r($c->getHTTPCode());
echo "\nHeaderStatus";print_r($c->getHeaderStatus());



$c->init_cURL()->reset();
echo "\n<hr/>Calling Google\n";
$c->enableCache(true);

$data2 = $c
        ->setAutoReferer(true)
        ->setReferer(true)
        ->followLocation(true)
        ->basicRequest("https://www.google.it/?gfe_rd=cr&dcr=0&ei=SEfFWcTsKrLBXoHSnagK&gws_rd=ssl");
echo "<textarea rows='8' cols='80'>".$data2."</textarea>";
echo "\nServer is ";print_r($c->getServerType());
echo "\nCookies are ";print_r($c->getCookies());
echo "\nHttp Code: ";print_r($c->getHTTPCode());
echo "\nHeaderStatus : ";print_r($c->functionHeaders);
echo "\nHeaderStatus : ";print_r($c->getHeaderStatus());

//echo $c->getResult();

