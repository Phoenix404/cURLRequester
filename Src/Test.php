<?php 

echo "<pre>";
require_once __DIR__."/../vendor/autoload.php";

use cURLRequester\cURLRequester;

$option     = [];
$option1    = array("CURLOPT_RETURNTRANSFER"=>1);
$option2    = array(CURLOPT_RETURNTRANSFER =>1);

//$c 	= new cURLRequester("https://www.google.it/?gfe_rd=cr&dcr=0&ei=QqS-WdTWJsj68Ae27YaABg", true);
$c 	= new cURLRequester("https://www.google.it/?gfe_rd=cr&dcr=0&ei=QqS-WdTWJsj68Ae27YaABg", true);
//$c 	= new cURLRequest("http://php.net/manual/en/features.commandline.options.php");

$c->setUserAgent();
//$c->enableCache(true);
$c->setOpt("CURLOPT_COOKIE", true);
$c->enableCookies(true);

//print_r($c);

//$data = $c->basicRequest("http://www.whatarecookies.com/");
/*
$data = $c->basicRequest("http://www.whatarecookies.com/cookietest.asp");
unset($c->result);
print_r($c);
*/
//echo "<textarea row='15' cols='100%'>".$data."</textarea>";

//$data = $c->basicRequest("https://www.google.it/?gfe_rd=cr&dcr=0&ei=QqS-WdTWJsj68Ae27YaABg", true);
//echo "<br/><textarea row='15' cols='100%'>".$data."</textarea>";

//echo $c->getResult();
/*$lines = explode("\n", $data);
$headers = array();
$body = "";

foreach($lines as $num => $line){
    $l = str_replace("\r", "", $line);

    //Empty line indicates the start of the message body and end of headers
    if(trim($l) === ""){

        $headers = array_slice($lines, 0, $num);

        $body = $lines[$num + 1];

        //Pull only cookies out of the headers
        $cookies = preg_grep('/^Set-Cookie:/', $headers);
        break;
    }
}
echo "\nCookies are ";print_r($cookies);*/


/*
$cookies = Array();
$ch = curl_init('http://www.whatarecookies.com/');
// Ask for the callback.
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_HEADERFUNCTION, "curlResponseHeaderCallback");
$result = curl_exec($ch);
print_r($cookies);

function curlResponseHeaderCallback($ch, $headerLine) {
    global $cookies;
    if (preg_match('/^Set-Cookie:\s*([^;]*)/mi', $headerLine, $cookie) == 1)
        $cookies[] = $cookie;
    return strlen($headerLine); // Needed by curl
}*/

$ch = curl_init();
$headers = [];
curl_setopt($ch, CURLOPT_URL, "http://www.whatarecookies.com/");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

// this function is called by curl for each header received
curl_setopt($ch, CURLOPT_HEADERFUNCTION,
    function($curl, $header) use (&$headers)
    {
        $len = strlen($header);
        echo "\n ONEE";
        print_r($header);
        $header = explode(':', $header, 2);
        if (count($header) < 2) // ignore invalid headers
            return $len;

        $name = strtolower(trim($header[0]));
        if (!array_key_exists($name, $headers))
            $headers[$name] = [trim($header[1])];
        else
            $headers[$name][] = trim($header[1]);

        return $len;
    }
);

$data = curl_exec($ch);
print_r($headers);