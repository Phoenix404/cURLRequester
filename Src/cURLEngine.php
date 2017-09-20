<?php

/**
 * cUrlRequest is lib to handle...
 * Created by Phoenix404.
 * User: Phoenix404
 * Date: 10/09/2017
 * Start time: 18:30
 * End time: 20:00
 * Version: 0.0.1
 *
 * In next version, will add stream_context_create support if curl is not installed.
 *
 */


namespace cURLRequester;
use Useragent\UserAgent as useragent;

class cURLEngine {

    protected $options         = array();

    protected $cURL            = null;
    protected $result          = "";
    protected $error           = "";
    protected $recallUseCache  = false;
    protected $userAgent       = "";
    protected $url             = "";
    protected $headers         = array();


    public $appName            = "cURLRequester";
    public $appVersion         = "1.0.0";

    public $CookiesJar          = "./Cookies/Cookies.txt";
    public $CookiesFile         = "./Cookies/Cookies.txt";
    public $certPath            = './SSL/cacert.pem';
    public $cacheDir            = './Cache/';

	protected $invokable 	   = array();

	// need to set proxy also
	// need to set cookies
	// get
	// post and fields..
	// google dns
	// need to add time loaded requested
	// curl_setopt($handle, CURLOPT_SSL_VERIFYPEER, false);
    // curl_setopt($handle, CURLOPT_SSL_VERIFYHOST, false);
    // https://en.wikipedia.org/wiki/List_of_HTTP_header_fields
    // https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers
    // authentication
    // download file
    // upload file
    // ftp

    /**
	 * cURLRequest constructor.
	 * @param string $url
	 * @param bool $ssl
	 * @param array $options
	 * @throws \Exception
	 */
	function __construct($url="", $ssl=false, $options=[])
	{
		if(!function_exists("curl_init"))
			throw new \Exception("cURL is not installed!");

		if(strlen($url)>0) $this->setUrl($url);

        if($ssl) $this->setCertificateFile($this->certPath);

		if(!empty($options)) $this->setOptions($options);

		$this->init_cURL();

		return $this;
	}

    /**
     * @param $options
     */
    public function setOptions($options)
    {
        if(isset($this->options["options"]))
            $this->options["options"] = array_merge($this->options["options"], compact($options));
        else
            $this->options["options"]   = compact($options);
    }

    /**
     * Init cURL
     * @param bool $fresh_no_cache
     * @internal param bool $fresh
     */
    public function init_cURL($fresh_no_cache=false)
    {

        if(!is_resource($this->cURL))
            $this->cURL = curl_init();

        curl_reset($this->cURL);

        $this->freshConnection($fresh_no_cache);
    }

    /**
     * Reset attributes and curl request
     * @return $this
     */
    public function reset()
    {

        $this->result           = "";
        $this->error            = "";
        $this->recallUseCache   = false;
        $this->userAgent        = "";
        $this->options          = array();
        $this->invokable        = array();
        $this->headers          = array();
        $this->url              = array();

        if(is_resource($this->cURL)) curl_reset($this->cURL);

        return $this;
    }

    /**
	 * Close curl connection
	 */
	public function _close()
	{
		curl_close($this->cURL);
	}

    /**
     * @param $opt
     * @return bool
     */
    public function curlOptIsset($opt)
	{
		return isset($this->options["curl_opt"][$opt]);
	}

    public function removeCurlOpt($opt)
    {
        if($this->curlOptIsset($opt))
        {
            unset($this->options["curl_opt"]);
        }
        return $this;
    }

    /**
	 * @see http://php.net/manual/en/function.curl-setopt.php
	 * @see http://php.net/manual/en/function.curl-setopt.php
	 * @param $option
	 * @param $value
	 * @return $this
	 */
	public function setOpt($option, $value)
	{
	   $this->options["curl_opt"][$option]=$value;
	   return $this;
	}

    /**
     * Get cURL set option
     * @param string $opt
     * @return null
     */
    protected function getOpt($opt="")
    {
        return $this->curlOptIsset($opt)?$this->options["curl_opt"][$opt]:null;
    }

    /**
     * Get request info
     * @param string $opt
     * @return mixed
     */
    public function getCurlInfo($opt="")
    {
        if(!is_resource($this->cURL))
            $this->init_cURL(true);

        $opt = $this->isCurlNativeOpt($opt);
        if($opt)
         return curl_getinfo($this->cURL, $opt);

        return curl_getinfo($this->cURL);
    }

    /**
     * Determine if given option is native opt of curl or not
     * @param null $opt
     * @param array $constArr
     * @return bool|mixed|null|string
     */
    protected function isCurlNativeOpt($opt = null, $constArr=[])
    {
        if(is_null($opt) ||  strlen($opt)<=0 || $opt === 0)
            return false;

        if(is_string($opt))
        {
            $opt = strtoupper($opt);
            if(strpos($opt, "CURL") === false )
                return false;

            if(defined(strtoupper($opt)))
                return constant($opt); // return constant value

        }elseif(is_int($opt)){
            if(empty($constArr))
                $constArr = get_defined_constants(1);

            if(!in_array($opt, $constArr["curl"]))
                return false;

            return $opt;
        }

        return false;
    }

    /**
     * Get all options in options array
     * Check for curl options as strings(constants) or numbers
     * @param null $optArr
     * @return null
     */
    protected function resetCurlOptions($optArr = null)
    {
        if(is_null($optArr))
            $optArr     = $this->options["curl_opt"];

        if(!is_array($optArr))
            $optArr     = array($optArr=>$optArr);

        $curlOptions = null;
        $constants   = get_defined_constants(1);

        foreach($optArr as $key => $option)
        {
            $opt = $this->isCurlNativeOpt($key, $constants);
            if($opt == false) continue;
            $curlOptions[$opt] = $option;

        }
        return $curlOptions;
    }

    /**
	 * Init Curl setup option
	 */
	protected function prepareCurlOption()
	{
		curl_setopt_array($this->cURL, $this->resetCurlOptions());
		return $this;
	}

    /**
     * @return bool
     */
    public function invoke()
	{

		//To check if user has called useCache method before setting the url
		if($this->recallUseCache)
		    $this->enableCache(true);

        // If invokable array is empty
        // It executes the request
        if(!$this->hasFalseValue($this->invokable))
        {
            //avoid print the result and force it to return the result in a variable
            curl_setopt($this->cURL, CURLOPT_RETURNTRANSFER, 1);

            $this->prepareCurlOption();
            $this->result = curl_exec($this->cURL);

        }

		// if cache is enabled
		if(isset($this->options["cacheFileName"]))
        {
            file_put_contents($this->options["cacheFileName"], $this->result);
            unset($this->options["cacheFileName"]);
        }

		// Reinitialize the array
        // So in next request we get new invokable status
        $this->invokable 	= array();

        //Check if result is true
		if($this->result == false)
			$this->result = $this->getError();

		return $this->result;
	}

    /**
     * @param $arr
     * @return bool
     */
    protected function hasFalseValue($arr)
    {
        foreach ($arr as $key => $value)
        {
            if($value == false) return true;
        }
        return false;
    }

    /**
	 * https://curl.haxx.se/libcurl/c/libcurl-errors.html
	 * http://php.net/manual/en/function.curl-strerror.php
	 */
	public function getError()
	{
		// get the current executed request error number
		$errno  = curl_errno($this->cURL);
		if($errno>0) {
			$errorMessage = curl_strerror($errno);
			return $this->error = "cURL error (" . $errno . "): " . $errorMessage;
		}

		// return false if request proceed successfully
		return $this->result;
	}

    /**
     * @param $url
     * @return $this
     */
    public function setUrl($url)
    {
        $this->url  = $url;
        $this->setOpt("CURLOPT_URL", $url);
        return $this;
    }

    /**
     * Get back the given url
     * @return string
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * Determine If file exists on web
     * @param $path
     * @return bool
     */
    protected function uriFileExists($path)
    {
        if (filter_var($path, FILTER_VALIDATE_URL) === FALSE)
            return false;

        $headers=get_headers($path);
        return stripos($headers[0],"200 OK")?true:false;
    }

    /**
     * Set .pem files
     * @param string $path
     * @return $this
     * @throws \Exception
     */
    public function setCertificateFile($path="")
	{
		if(strlen($path)<=0)
			$path = $this->certPath;

		if(@file_exists($path))
		{
		    $this->verifyPeer(true);
            $this->setOpt("CURLOPT_CAINFO", realpath($path));
            return $this;
        }

        if($this->uriFileExists($path))
        {
            $this->setOpt("CURLOPT_CAINFO", $path);
            $this->verifyPeer(true);
            return $this;
        }

        throw new \Exception("Cacert path doesn't exists!");

	}

    /**
     * Set directory of certificates
     * @param string $path
     * @return $this
     * @throws \Exception
     */
    public function setCertificatePath($path="")
    {
        if(strlen($path)<=0) {
            $path = $this->certPath;
        }

        if(@is_dir($path))
        {
            $this->verifyPeer(true);
            $this->setOpt("CURLOPT_CAPATH", realpath($path));
            return $this;
        }

        if($this->uriFileExists($path))
        {
            $this->verifyPeer(true);
            $this->setOpt("CURLOPT_CAPATH", $path);
            return $this;
        }

        throw new \Exception("Certificate directory doesn't exists!");
    }

    /**
     * @see https://en.wikipedia.org/wiki/HTTP_referer
     * @param bool $val
     * @return $this
     */
	public function setAutoReferer($val = true)
	{
		$this->setOpt("CURLOPT_AUTOREFERER", $val);
		return $this;
	}

    /**
     * @see https://en.wikipedia.org/wiki/HTTP_referer
     * @param bool $val
     * @return $this
     */
	public function setReferer($val = true)
	{
		$this->setOpt("CURLOPT_REFERER", $val);
		return $this;
	}

    /**
     * set headers
     * @param array|string $key
     * @param string $value
     * @param bool $headerVal
     */
    public function setHeaders($key, $value="", $headerVal=true)
    {
        if(is_array($key)){
            $this->headers     = array_merge($this->headers, $key);
        }else {
            $this->headers[$key] = $value;
        }

        $headers = array();
        foreach ($this->headers as $key => $value) {
            $headers[] = $key . ': ' . $value;
        }

        $this->setOpt(CURLOPT_HEADER, $headerVal);
        $this->setOpt(CURLOPT_HTTPHEADER, $headers);
    }

    /**
     * Set maximum redirect allow
     * @param int $level
     * @return $this
     */
    public function setMaxRedirects($level= -1)
    {
        $this->setOpt("CURLOPT_MAXREDIRS", $level);
        return $this;
    }

    /**
     * It will follow end location of referer urls
     * @param bool $val
     * @return $this
     */
    public function followLocation($val=true)
    {
        $this->setOpt("CURLOPT_FOLLOWLOCATION ", $val);
        return $this;
    }

    /**
     * Real url is where from the request is executed
     * @return mixed
     */
    public function getRealUrl()
    {
        return $this->getCurlInfo("CURLINFO_EFFECTIVE_URL");
    }

    /**
     * Set timeout
     * @param $seconds
     */
    public function setTimeout($seconds=0)
    {
        $this->setOpt(CURLOPT_TIMEOUT, $seconds);
    }

    /**
     * @param string $str
     * @return $this
     */
    public function setUserAgent($str="")
	{
		if (strlen($str) > 0 || $str === false){
		    $this->userAgent     = $str;
			$this->setOpt("CURLOPT_USERAGENT", $str);
		}else{
			if(class_exists("useragent")){
				$useragent = new useragent();
				$this->userAgent = $useragent->getRealUserAgent();
				$this->setOpt("CURLOPT_USERAGENT", $this->userAgent);
			}else{
			    if(isset($_SERVER["HTTP_USER_AGENT"]))
			    {
                    $this->setOpt("CURLOPT_USERAGENT", $_SERVER["HTTP_USER_AGENT"]);
                }else
                {
                    $customUseragent = "Mozilla/5.0 (cURL; PhoenixOS; 512x) ".$this->appName."Kit/777.77 (KHTML, like Phoenix) ".
                        $this->appName."/".$this->appVersion." Phoenix404/777.77";

                    $this->setOpt("CURLOPT_USERAGENT", $customUseragent);
                }
			}
		}
		return $this;
	}

    /**
     * @return string
     */
    public function getUserAgent()
    {
        return $this->userAgent;
    }

    /**
     * Get the size of directory
     * @param $directory
     * @return bool|int
     */
    public function getDirectorySize($directory)
    {
        if(!is_dir($directory))
            return false;

        $size = 0;
        $dirs = scandir($directory);
        foreach($dirs as $dir)
        {
            if($dir === "." || $dir === "..")
                continue;

            if(is_dir($dir)) {

                $size += $this->getDirectorySize($directory.DIRECTORY_SEPARATOR.$dir);
            }

            if(is_file($directory.DIRECTORY_SEPARATOR.$dir))
            {
                $size += filesize($directory.DIRECTORY_SEPARATOR.$dir);
            }
        }

        return $size;
    }

    /**
     * @param $directory
     * @param $timestamp
     * @param string $beforeEqualAfter|<|<=|=|>=|>
     * @return bool
     */
    public function autoDeleteFiles($directory, $timestamp, $beforeEqualAfter="<")
    {
        if(!is_dir($directory)) return false;
        $dirs = scandir($directory);
        foreach($dirs as $dir) {
            if ($dir === "." || $dir === "..")
                continue;

            if (is_dir($dir))
                $this->getDirectorySize($directory . DIRECTORY_SEPARATOR . $dir);

            if (is_file($directory . DIRECTORY_SEPARATOR . $dir))
            {
                switch ($beforeEqualAfter) {
                    case("="):
                        if (filemtime($directory . DIRECTORY_SEPARATOR . $dir) === $timestamp)
                            unlink($directory . DIRECTORY_SEPARATOR . $dir);
                        break;
                    case("<"):
                        if (filemtime($directory . DIRECTORY_SEPARATOR . $dir) < $timestamp)
                            unlink($directory . DIRECTORY_SEPARATOR . $dir);
                        break;
                    case("<="):
                        if (filemtime($directory . DIRECTORY_SEPARATOR . $dir) <= $timestamp)
                            unlink($directory . DIRECTORY_SEPARATOR . $dir);
                        break;
                    case(">"):
                        if (filemtime($directory . DIRECTORY_SEPARATOR . $dir) > $timestamp)
                            unlink($directory . DIRECTORY_SEPARATOR . $dir);
                        break;
                    case(">="):
                        if (filemtime($directory . DIRECTORY_SEPARATOR . $dir) >= $timestamp)
                            unlink($directory . DIRECTORY_SEPARATOR . $dir);
                        break;
                }
            }

        }
        return true;
    }

	/**
     * If this method is enable,
     *  - It will check for the file of last request executed of given url
     *  - If it doesn't found it will create the file for given url.
     *  - If file already exists, so it will not make a cURL request and give back response of last time request executed
     * Second parameter by default has following values :
     *  $option["MaxDiskSize"] = 10*1024*1024. //max 10mb of Cache directory
     *  $option["MaxFileOldDuration"] = (60*60*24)*7. // if file exists and not older than 7 days
     *  then give back result of this file instead of sending request to server
     *
     *  $option["MinFileSize"] = 2. //cache file that can have minimum file size
     * @param bool $cache |default false
     * @param array $option
     * @return $this|bool
     */
    public function enableCache($cache=false, $option=[])
	{
        //So we send fresh request to server(!)
        if($cache===false){
            $this->freshConnection(true);
            return $this;
        }

        //By default the cache is false
        $this->invokable["cache"] 	= false;
        $this->recallUseCache       = false;

        if(empty($option) || count($option)===0)
        {
            // Lets say, our src/cache folder can have only 10mb by default
            $option["MaxDiskSize"]  = 10*1024*1024;

            // Lets say, a file can have old duration
            // if file is older than (60*60*24)*7.. so we will make new request
            $option["MaxFileOldDuration"]  = (60*60*24)*7;

            $option["MinFileSize"]         = 2;
        }

        if(!isset($option["MaxDiskSize"]))
            $option["MaxDiskSize"]  = 10*1024*1024;

        if(!isset($option["MaxFileOldDuration"]))
            $option["MaxFileOldDuration"]  = (60*60*24)*7;

        if(!isset($option["MinFileSize"]))
            $option["MinFileSize"]  = 2;

        //check weather cache dir exists or not
		if(!is_dir($this->cacheDir))
			mkdir($this->cacheDir, 0777);

		if($this->getDirectorySize($this->cacheDir) > $option["MaxDiskSize"])
        {
            //auto maintenance
            // >= delete all file greater than..
            $this->autoDeleteFiles($this->cacheDir, $option["MaxFileOldDuration"], ">=");
            $this->invokable["true"]    = true;
        }

		// check if url is set or not
		// if not, we set the flag useCache to true
		// and we will try to call again this method in invoke method
		if(!$this->curlOptIsset("CURLOPT_URL")) {
            $this->recallUseCache = true;
            return $this;
        }

        $fileName   = $this->cacheDir."/".md5($this->getOpt("CURLOPT_URL")).".cache";

		if(file_exists($fileName))
		{
		    $fileTime   = filemtime($fileName);
			$timeDiff 		= time() - $fileTime;

			if(filesize($fileName) <= (int)$option["MinFileSize"])
            {
                $this->invokable["cache"] 	= true;
            }

		    if((int)$option["MaxFileOldDuration"] >= $timeDiff)
            {
				$this->result 				= file_get_contents($fileName);
            }else{
                $this->invokable["cache"] 	= true;
            }
        }else {
		    // If cache doesn't exists
		    // then create new file when request is sent to server
            $this->options["cacheFileName"] = $fileName;
            // Force to send request to Server
            $this->invokable["cache"] 	= true;
        }

		$this->recallUseCache 	= false;
		return $this;
	}

    /**
     * @param bool $val
     * @return $this
     */
    public function freshConnection($val = true)
	{
		$this->setOpt("CURLOPT_FRESH_CONNECT", $val);
		return $this;
	}

    /**
     * @return string
     */
    public function getResult ()
    {
        return $this->result;
    }

    /**
     * Set Port num
     *
     * @param  $port
     * @return $this
     */
    public function setPort($port)
    {
        $this->setOpt(CURLOPT_PORT, intval($port));
        return $this;
    }

    /**
     * Set Connect Timeout
     * @param  $seconds
     * @return $this
     */
    public function setConnectTimeout($seconds)
    {
        $this->setOpt(CURLOPT_CONNECTTIMEOUT, $seconds);
        return $this;
    }

    /**
     * @return mixed
     */
    public function getCookieList()
    {
        return $this->getCurlInfo("CURLINFO_COOKIELIST");
    }

    public function getRequestSize()
    {
        return $this->getCurlInfo("CURLINFO_REQUEST_SIZE");
    }

    public function verifyPeer($val=true)
    {
        $this->setOpt("CURLOPT_SSL_VERIFYPEER", $val);
        return $this;
    }

    public function verifyHost($val=false)
    {
        $this->setOpt("CURLOPT_SSL_VERIFYHOST", $val);
        return $this;
    }

    public function fastTCP()
    {
        $this->setOpt("CURLOPT_TCP_FASTOPEN", true);
        return $this;
    }

    public function getSSLResult()
    {
        return $this->getCurlInfo("CURLINFO_SSL_VERIFYRESULT");
    }

    public function setCookiesJar($jar="")
    {
        if(strlen($jar)<=0)
            $jar = $this->CookiesJar;

        $this->setOpt("CURLOPT_COOKIEJAR", $jar);
        return $this;
    }


    public function setCookiesFile($file="")
    {
        if(strlen($file)<=0)
            $file   =  $this->CookiesFile;
            
        $this->setOpt("CURLOPT_COOKIEFILE", $file);
        return $this;
    }

    /**
     * @param bool $val
     */
    public function enableCookies($val = true)
    {
        if($var){
        }else{
            $thiis->removeCurlOpt("CURLOPT_COOKIEJAR");
            $thiis->removeCurlOpt("CURLOPT_COOKIEFILE");
        }
        return $this;
    }

}
