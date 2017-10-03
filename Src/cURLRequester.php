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
            echo "\nUrl is $url\n";
            $this->setUrl($url);
        }
        elseif($this->isSetUrl()){}
        else throw new \Exception("ERROR: URL is required");

        $this->initCurl(true);

        // Set default useCache to false
        // So every time it will send fresh request to server
        //$this->enableCache($useCache);

        $this->invoke();
        //$this->_close();
        return $this->result;
    }

    public function get()
    {
        // if set to false MITM attack can be activate
        //http://ademar.name/blog/2006/04/curl-ssl-certificate-problem-v.html
        //verifyHos                 t()
        //verifypeer()

    }

    public function post()
    {

    }

    public function login()
    {}

}
