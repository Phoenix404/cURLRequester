<?php

/**
 * Created by PhpStorm.
 * User: WahaGuru
 * Date: 19/09/2017
 * Time: 20:52
 */

namespace cURLRequester;


class cURLRequester extends cURLEngine
{

    function __construct($url="", $ssl=false, $options=[])
    {
        parent:: __construct($url, $ssl, $options);

        return $this;
    }

    /**
     *
     * @param string $url
     * @param bool $useCache
     * @return string
     * @throws \Exception
     */
    public function basicRequest($url = "", $useCache =false)
    {
        if(strlen($url)>0) {
            $this->setUrl($url);
        }
        elseif($this->isSetUrl()){}
        else throw new \Exception("ERROR: URL is required");
        $this->initCurl(true);

        if($useCache) $this->enableCache($useCache);
        $this->invoke();
        return $this;
    }

    /**
     * Perform get request
     * @param $url
     * @param $params
     * @param bool $useSSL
     * @param bool $secure
     * @return string
     */
    public function get($url, $params="", $useSSL=true, $secure=true)
    {
        //Try to avoid MITM Attack
        if($secure){
            $this->verifyPeer();
            $this->verifyHost();
        }

        if($useSSL) $this->setCertificateFile();

        if(!$this->isCacheEnable()) $this->initCurl(true);

        $url    = $this->makeUrl($url, $params);
        $this->setUrl($url);
        $this->setOpt("CURLOPT_HTTPGET", true);
        $this->invoke();
        return $this;
    }

    /**
     * Perform Post Request.
     * @param $url
     * @param string $params
     * @param bool $useSSL
     * @param bool $secure
     * @return $this
     */
    public function post($url, $params="", $useSSL=true, $secure=true)
    {
        if($secure){
            $this->verifyPeer();
            $this->verifyHost();
        }

        if($useSSL) $this->setCertificateFile();

        if(!$this->isCacheEnable()) $this->initCurl(true);

        $url    = $this->makeUrl($url, $params);
        $this->setUrl($url);

        $this->setOpt("CURLOPT_POST", true);

        if(!empty($params)) $this->setOpt("CURLOPT_POSTFIELDS", http_build_query($params));

        $this->invoke();
        return $this;
    }

    public function login()
    {
        // try to find the actual login form
        /*if (!preg_match('/<form .*?<\/form>/is', $page, $form)) {
            die('Failed to find log in form!');
        }*/
        //https://github.com/mindevolution/amazonSellerCentralLogin
        //https://github.com/mindevolution/amazonSellerCentralLogin/blob/master/src/Login.php

    }

}
