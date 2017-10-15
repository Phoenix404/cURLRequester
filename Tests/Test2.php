<?php

echo "<pre>";

require_once __DIR__ . "/../vendor/autoload.php";

use cURLRequester\cURLRequester;

$option     = [];
$option1    = array("CURLOPT_RETURNTRANSFER"=>1);
$option2    = array(CURLOPT_RETURNTRANSFER =>1);

$URL            = [
                    "http://www.whatarecookies.com/cookietest.asp",
                    "https://www.google.it/?gfe_rd=cr&dcr=0&ei=SEfFWcTsKrLBXoHSnagK&gws_rd=cr",
                    "http://www.lina24.com/go/"
                    ];
$totalRequest   = count($URL)-0;


$c = new cURLRequester();
//file_put_contents("../../html.html", $data);
//echo '<iframe src="../../html.html" width="100%" height="50%"></iframe>';
for($i = 0; $i < $totalRequest; $i++)
{

    $data = $c->newCurl()
        //->setUserAgent()
        ->enableCache(true)
        //->setCookies()
        ->enableCookies()
        //->noBody()
        //->setHeaders("Connection", "Keep-Alive")
        //->setOpt("CURLOPT_COOKIELIST", 1)
        ->basicRequest($URL[$i])
        ->getResult();

    echo "<textarea rows='5' cols='120'>".$data."</textarea>";
    //echo "\nServer is ";print_r($c->getServerType());
    //echo "\nCookies are ";print_r($c->getCookies());
    //echo "\nCURLINFO_COOKIELIST are ";print_r($c->getCurlInfo(CURLINFO_COOKIELIST));
    //echo "\nHeaderStatus : ";print_r($c->functionHeaders);
    ///echo "\nHeaderStatus: ";print_r($c->getHeaderStatus());
    //echo "\nget HTTP Code: ";print_r($c->getHTTPCode());
   // echo "\nget Real Url: ";print_r($c->getRealUrl());
    //echo "\nRedirected Url: ";print_r($c->getRedirectedUrl());
    //$c->initCurl();
    //$c->resetCurl();
    echo "<hr/><br/><br/>";
}

//$c->closeCurl();