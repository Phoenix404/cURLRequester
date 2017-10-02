<?php

echo "<pre>";

require_once __DIR__."/../vendor/autoload.php";

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

//$c->noBody();
echo "\nCalling what are cookies\n";
$data1 = $c
        //->setUserAgent()
        //->enableCache(true)
        //->setCookies()
        //->enableCookies(true)
        //->noBody()
        ->basicRequest("http://www.whatarecookies.com/cookietest.asp");
//$c->setUserAgent();
//echo "\nUseragent is : ".$c->getUserAgent()."\n";

//unset($c->result);
//print_r($c);

//$c->setHeaders("Connection", "Keep-Alive");
//$data1 = $c->basicRequest();
echo "<textarea rows='8' cols='80'>".$data1."</textarea>";
echo "\nServer is ";print_r($c->getServerType());
echo "\nCookies are ";print_r($c->getCookies());
//echo "\nHeaderStatus : ";print_r($c->functionHeaders);
echo "\nHeaderStatus: ";print_r($c->getHeaderStatus());
echo "\nget HTTP Code: ";print_r($c->getHTTPCode());
echo "\nget Real Url: ";var_dump($c->getRealUrl());



$c->init_cURL()->resetCurl();
echo "\n<hr/>Calling Google\n";
//$c->enableCache(true);

//CURLINFO_HTTP_CODE
//$c->setHeaders("Connection", "Keep-Alive");
$data2 = $c
        //->noBody()
        //->enableCookies(true);
        ->setAutoReferer(true)
        ->setReferer(true)
        ->followLocation(true)
        //->basicRequest("https://www.google.com/?gfe_rd=cr&dcr=0&ei=SEfFWcTsKrLBXoHSnagK&gws_rd=ssl");
        ->basicRequest("https://www.google.it/?gfe_rd=cr&dcr=0&ei=SEfFWcTsKrLBXoHSnagK&gws_rd=cr");
        //->basicRequest("www.google.com");
echo "<textarea rows='8' cols='80'>".$data2."</textarea>";
echo "\nServer is ";print_r($c->getServerType());
echo "\nCookies are ";print_r($c->getCookies());
echo "\nHeader Status : ";print_r($c->getHeaderStatus());
echo "\nHTTP Code: ";print_r($c->getHTTPCode());
echo "\nOriginal Url: ";print_r($c->getUrl());
echo "\nReal Url: ";print_r($c->getRealUrl());
echo "\nRedirected Url: ";print_r($c->getRedirectedUrl());

//echo $c->getResult();

