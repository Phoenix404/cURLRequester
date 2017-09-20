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

        if(strlen($url)>0) $this->setUrl($url);
        elseif($this->curlOptIsset("CURLOPT_URL")){}
        else throw new \Exception("ERROR: URL is required");

        $this->init_cURL(true);

        // Set default useCache to false
        // So every time it will send fresh request to server
        $this->enableCache($useCache);

        $this->invoke();
        $this->_close();
        return $this->result;
    }

    public function get()
    {
        // if set to false mitm attack can be activge
        //http://ademar.name/blog/2006/04/curl-ssl-certificate-problem-v.html
        //verifyHos                 t()
        //verifypeer()

    }

    public function post()
    {

    }



}