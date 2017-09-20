<?php 

echo "<pre>";
require_once __DIR__."/../vendor/autoload.php";

use cURLRequester\cURLRequester;

$option     = [];
$option1    = array("CURLOPT_RETURNTRANSFER"=>1);
$option2    = array(CURLOPT_RETURNTRANSFER =>1);

$c 	= new cURLRequester("https://www.google.it/?gfe_rd=cr&dcr=0&ei=QqS-WdTWJsj68Ae27YaABg", true);
//$c 	= new cURLRequest("http://php.net/manual/en/features.commandline.options.php");

$c->setUserAgent();
$c->enableCache(true);

//print_r($c);

$data = $c->basicRequest();
//echo "<textarea row='15' cols='100%'>".$data."</textarea>";
//$data = $c->basicRequest("https://www.google.it/?gfe_rd=cr&dcr=0&ei=QqS-WdTWJsj68Ae27YaABg", true);
//echo "<br/><textarea row='15' cols='100%'>".$data."</textarea>";
//echo $c->getResult();
//print_r($c->getCookieList());
//print_r($c->getRealUrl());
//print_r($c->getCurlInfo());




