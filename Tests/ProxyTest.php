<?php

echo "<pre>";

require_once __DIR__ . "/../vendor/autoload.php";

use cURLRequester\cURLRequester;

$option     = [];
$option1    = array("CURLOPT_RETURNTRANSFER"=>1);
$option2    = array(CURLOPT_RETURNTRANSFER =>1);

$c = new cURLRequester();
$data = $c->newCurl()->get("www.google.it")->getResult();
echo "<textarea rows='10' cols='120'>".$data."</textarea>";

$data = $c->newCurl()->setProxy("57.202.14.135:29542")->setProxyAuth("siruk", "Txt51s7")->get("www.google.it")->getResult();
echo "<textarea rows='10' cols='120'>".$data."</textarea>";


//$c->closeCurl();