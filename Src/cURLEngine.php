<?php

/**
 * cUrlRequest is lib to handle...
 * Created by Phoenix404.
 * User: Phoenix404
 * Date: 10/09/2017
 * Start time: 10/09/2017 18:30
 * End time: 20/09/2017 20:00
 * Version: 1.0.0
 *
 * In next version
 * Will try to add stream_context_create support if curl is not installed.
 * FTP for checking file on the other servers..
 */

namespace cURLRequester;
use Useragent\UserAgent;
use Proxy\Proxy;

class cURLEngine {

    protected $cURL                     = null;

    protected $result                   = "";
    protected $error                    = "";
    protected $userAgent                = "";
    protected $url                      = "";

    // Arrays
    protected $options                  = array();
    protected $headers                  = array();
    protected $functionHeaders          = array();
    protected $invokable 	            = array();
    protected $errors                   = array();

    // App/Lib Identity
    public $appName                     = "cURLRequester";
    public $appVersion                  = "1.0.0";

    // Default Directories where data can be write
    protected $functionHeadersDir       = "./Other/Headers/";
    public $certPath                    = './SSL/cacert.pem';
    public $cacheDir                    = './Cache/';
    public $cookiesDir                  = "Cookies/";

	// Flags
    protected $isCookiesEnable          = false;
    protected $isCacheEnable            = false;
    protected $isCookiesNewWrite        = false;

	// need to set proxy also
	// need to add time loaded requested
    // authentication
    // download file
    // upload file
    // ftp
    // need to delete @see comments ..

    /**
	 * cURLRequest constructor
	 * @param string $url
	 * @param bool $ssl
	 * @param array $options
	 * @throws \Exception
	 */
	function __construct($url="", $ssl=false, $options=[])
	{
		if(!function_exists("curl_init"))
			throw new \Exception("cURL is not installed!");

        // Check if directories Exists or not..
        $this->setLibDirectories();

        if(strlen($url)>0) $this->setUrl($url);

        if($ssl) $this->setCertificateFile($this->certPath);

        if(!empty($options)) $this->setOptions($options);

        $this->initCurl();

        return $this;
	}

    /**
     * Set Library folders/directories
     * @param array $directories
     * @param string $path
     * @param string $permission
     * @param bool $recursive
     */
    protected function setLibDirectories($directories=[], $path="", $permission = "0777", $recursive=true)
    {
        if(empty($directories)) {
            $dirs = ["Cache", "Cookies", "SSL", "Other" => ["Headers"]];
        }else {
            if(is_array($directories)) $dirs   = $directories;
            else $dirs   = array($directories);
        }
        if(strlen($path)<=0)
            $path 	= __DIR__.DIRECTORY_SEPARATOR;

        foreach ($dirs as $key=> $dir) {
            //check if $dir is array
            // then key will be a folder that will hold subfolders are in $dir
            if(is_array($dir)){
                if(!is_dir($path.$key)) mkdir($path.$key, $permission, $recursive);
                $this->setLibDirectories($dir, $path.$key.DIRECTORY_SEPARATOR, $permission, $recursive);
            }else
                if(!is_dir($path.$dir)) mkdir($path.$dir, $permission, $recursive);
        }
    }

    protected function initSettings()
    {
        //$this->readCookiesFile =
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
     * Initialize cURL if not initialized and reset curl option
     * @param bool $fresh_no_cache
     * @internal param bool $fresh
     * @return $this
     */
    public function initCurl($fresh_no_cache=false)
    {

        if(!is_resource($this->cURL))
            $this->cURL = curl_init();

        curl_reset($this->cURL);

        $this->freshConnection($fresh_no_cache);
        return $this;
    }

    public function newCurl($fresh_no_cache=false)
    {
        $this->cURL = curl_init();
        curl_reset($this->cURL);
        $this->freshConnection($fresh_no_cache);
        return $this;
    }

    /**
     * Reset attributes and curl request
     * @return $this
     */
    public function resetCurl()
    {
        $this->result                   = "";
        $this->error                    = "";
        $this->userAgent                = "";

        $this->isCookiesEnable    = false;
        $this->isCacheEnable      = false;

        $this->options                  = array();
        $this->invokable                = array();
        $this->headers                  = array();
        $this->url                      = array();
        $this->functionHeaders          = array();

        if(is_resource($this->cURL)) curl_reset($this->cURL);

        return $this;
    }

    /**
	 * Close curl connection
	 */
	public function closeCurl()
	{
		curl_close($this->cURL);
	}

    /**
     * Check whether curl opt is set or not
     * @param $opt
     * @return bool
     */
    public function isSetCurlOpt($opt)
	{
		return isset($this->options["curl_opt"][$opt]);
	}

    /**
     * Remove the curl option if is set
     * @param $opt
     * @return $this
     */
    public function removeCurlOpt($opt)
    {
        if($this->isSetCurlOpt($opt))
        {
            unset($this->options["curl_opt"][$opt]);
        }
        return $this;
    }

    /**
	 * Set cURL options
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
        return $this->isSetCurlOpt($opt)?$this->options["curl_opt"][$opt]:null;
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
            if(strpos($opt, "CURL") === false) return false;

            if(defined(strtoupper($opt))) return constant($opt); // return constant value

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
        $options    = $this->resetCurlOptions();
        if(is_array($options)){
            curl_setopt_array($this->cURL, $options);
        }
        return $this;
    }

    /**
     * @return bool
     */
    public function invoke()
    {
        $this->functionHeaders = array();

        if(!$this->isSetUrl()){
            $this->errors[] = "Url no found";
            return false;
        }

        //To check if user has called enableCache method
        if($this->isCacheEnable())
            $this->_enableCache(true);

        if($this->isCookiesEnable()) {
            $this->_enableCookies(true);
        }
        if(!$this->hasFalseValue($this->invokable))
        {
            echo "\nPlease remove me from line ".__LINE__." I am from invoke method.\n";

            //avoid print the result and force it to return the result in a variable
            curl_setopt($this->cURL, CURLOPT_RETURNTRANSFER, true);
            //Get Curl Header response
            curl_setopt($this->cURL, CURLOPT_HEADERFUNCTION, array($this, "cURLHeadersFunction"));
            $this->prepareCurlOption();
            //print_r($this->options["curl_opt"]);
            $this->result = curl_exec($this->cURL);
            $this->writeFunctionHeaders();
        }

        // if cache is enabled
        if($this->isCacheEnable())
        {
            $filename   = $this->getCacheFileName();
            file_put_contents($filename, $this->result);
            unset($filename);
        }

        // Reinitialize the array. So in next request we get new invokable status_es
        $this->invokable 	= array();

        //Check if result is true
        if($this->result == false)
            $this->result = $this->getCurlErrors();

        return $this->result;
    }

    /**
     * Curl Header function to get response header info
     * @param $curl
     * @param $header
     * @return int
     */
    protected function cURLHeadersFunction($curl, $header)
    {
        $len = strlen($header);
        if($this->str_contains(strtolower($header), "http"))
        {
            // status  HTTP/1.1 200 OK;
            $this->functionHeaders["status"]    = $header;
        }

        $header = explode(':', $header, 2);
        if (count($header) < 2)
            return $len;

        $name = strtolower(trim($header[0]));
        if (!array_key_exists($name, $this->functionHeaders))
            $this->functionHeaders[$name] = [trim($header[1])];
        else
            $this->functionHeaders[$name][] = trim($header[1]);

        return $len;

    }

    /**
     * Check whether has false value
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
    public function getCurlErrors()
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
     * Get request info
     * @param string $opt
     * @return mixed
     */
    public function getCurlInfo($opt="")
    {
        if(!is_resource($this->cURL)) $this->initCurl();

        $option = $this->isCurlNativeOpt($opt);

        if($option) return curl_getinfo($this->cURL, $option);

        $info   = curl_getinfo($this->cURL);
        if(isset($info[$opt])) return $info[$opt];

        if(strlen($opt)<=0) return $info;
        return false;
    }

    /**
     * @param bool $val
     * @return $this
     */
    public function noBody($val=true)
    {
        $this->setOpt("CURLOPT_NOBODY", $val);
        return $this;
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
     * Determine whether url is set.
     * @return bool
     */
    public function isSetUrl()
    {
        return $this->isSetCurlOpt("CURLOPT_URL");
    }

    /**
     * Determine If file exists on web
     * @param $path
     * @return bool
     */
    protected function uriFileExists($path)
    {
        // need to improve with ftp
        // in next version
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
     * Set Cookies file in which you want to write the cookies
     * @param string $jar
     * @return $this
     */
    protected function setCookiesJar($jar="")
    {
        if(strlen($jar)<=0) {
            $jar = $this->getCookiesFileName();
        }

        $this->setOpt("CURLOPT_COOKIEJAR", realpath($jar));
        return $this;
    }

    /**
     * Set cookies file in which the cookies are already written.
     * @param string $file
     * @return $this
     */
    public function setCookiesFile($file="")
    {
        if(strlen($file)<=0) {
            $file = $this->getCookiesFileName();
        }

        $this->setOpt("CURLOPT_COOKIEFILE", realpath($file));
        return $this;
    }

    /**
     * Set Cookies with custom key and values. Where key can be name or array of cookies
     * @see https://stackoverflow.com/questions/6453347/php-curl-and-setcookie-problem
     * @param array|string $key
     * @param string $value
     * @return $this
     */
    public function setCookies($key="", $value="")
    {
        if(is_array($key))
        {
            $cookies    = http_build_query($key);
        }elseif(is_string($key))
        {
            $cookies    = http_build_query(array($key=>$value));
        }else
            return $this;

        $this->setOpt("CURLOPT_COOKIE", $cookies);
        return $this;
    }

    /**
     * It will enable the Cookies and ll write in a file
     * You can change default value just by assigning value to $this->CookiesFile
     * @param bool $val
     * @return $this
     */
    protected function _enableCookies($val = true)
    {
        if($val){
            $cookiesFile = $this->getCookiesFileName();
            if(file_exists($cookiesFile)) {
                $this->setCookiesFile($cookiesFile);
            }else{
                file_put_contents($cookiesFile, "");
                $this->setCookiesJar($cookiesFile);
            }
        }else{
            $this->removeCurlOpt("CURLOPT_COOKIEJAR");
            $this->removeCurlOpt("CURLOPT_COOKIEFILE");
        }
        return $this;
    }

    /**
     * Call this method to enable the cookies
     * @param bool $val
     * @return $this
     */
    public function enableCookies($val=true, $file="", $writeAllCookiesInSingleFile=false, $readAllCookiesFromSingleFile=false)
    {
        $this->isCookiesEnable = $val;
        $options["cookiesOptions"]     = compact($file, $writeAllCookiesInSingleFile, $readAllCookiesFromSingleFile);
        return $this;

    }

    /**
     * Disable the cookies
     */
    public function disableCookies()
    {
        $this->enableCookies(false);
    }

    /**
     * Check if cookies are enabled or not
     * @return bool
     */
    public function isCookiesEnable()
    {
        return $this->isCookiesEnable;
    }

    /**
     * Set the directory for cookies where you want to write or read them.
     * @param $path
     * @return bool
     */
    public function setCookiesDir($path)
    {
        if(@is_dir($path))
        {
            // Check if file is writeAble
            if(@is_writable($path))
            {
                $this->cookiesDir     = realpath($path);
                return true;
            }else
                return false;
        }else
            return false;
    }

    /**
     * Get the name of the cookies file
     * @return bool|string
     */
    public function getCookiesFileName($ext=".cookies")
    {
        if(!$this->isSetUrl())
            return false;

        $url        = $this->getOpt("CURLOPT_URL");
        $urlParts   = parse_url($url);

        // php 5.* -> WHERE * € [1-9]
        $urlParts["host"]    = str_replace("http", "", $urlParts["host"]);
        $urlParts["host"]    = str_replace("https", "", $urlParts["host"]);
        $urlParts["host"]    = str_replace("www", "", $urlParts["host"]);
        $urlParts["host"]    = str_replace(".", "_", $urlParts["host"]);
        //php 7
        //$urlParts["host"]    = str_replace(["www", "https", "http", "."], "", $urlParts["host"]);
        //$urlParts["host"]    = str_replace(["."], "_", $urlParts["host"]);
        $fileName   = substr(md5($url),0,10)."_".$urlParts["host"].$ext;
        $fileName   = realpath($this->cookiesDir).DIRECTORY_SEPARATOR.$fileName;

        return $fileName;
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
     * Set timeout
     * @param $seconds
     */
    public function setTimeout($seconds=0)
    {
        $this->setOpt(CURLOPT_TIMEOUT, $seconds);
    }

    /**
     * Make lib own useragent
     * @return $this
     */
    protected function makeCustomUserAgent()
    {
        $this->userAgent = "Mozilla/5.0 (cURL; PhoenixOS; 512x) ".$this->appName."Kit/777.77 (KHTML, like Phoenix) ".
            $this->appName."/".$this->appVersion." Phoenix404/777.77";
        return $this;
    }

    /**
     * @param string $str
     * @return $this
     */
    public function setUserAgent($str="random")
	{
        if($str===false)
        {
            $this->removeCurlOpt("CURLOPT_USERAGENT");
            return $this;
        }

        $useragentsReserved = ["windows", "linux",  "mac", "unix", "mozilla", "firefox", "chrome", "ie", "safari",
                                "opera", "maxthon", "android", "kindle", "apple", "blackberry", "acer", "amazonKindle",
                                "GoogleNexus", "HP", "HTC", "LG", "Motorola", "Nokia", "Samsung", "Sony", "Tablets", "mac",
                                "Playstation", "Wii", "PSP", "SuperBot", "Wget", "ELinks", "NetBSD", "Lynx", "IEMobile",
                                "Baiduspider", "iPhone", "Puffin", "Yahoo","Galeon","Symbian","Googlebot-Mobile"];

        $randomUserAgent = in_array(strtolower($str), array_map("strtolower", $useragentsReserved));
	    if($randomUserAgent || strtolower($str)=="random" || $str==true || strlen($str)<=0)
        {
            if(class_exists("\\Useragent\UserAgent")){
                $useragent = new UserAgent();
                if($randomUserAgent)
                {
                    $this->userAgent = $useragent->getRandomUserAgent(strtolower($str));
                }elseif(strtolower($str)=="random")
                {
                    $this->userAgent = $useragent->getRandomUserAgent();
                }else{
                    $this->userAgent = $useragent->getRealUserAgent();
                    if(strlen($this->userAgent)<=0)
                    {
                        $this->makeCustomUserAgent();
                    }
                }
            }else{
                echo "\nclass doesn't exists\n";
                //create custom useragent
                $this->makeCustomUserAgent();
            }
            $this->setOpt("CURLOPT_USERAGENT", $this->userAgent);
        }elseif(strlen($str) > 0){
		    $this->userAgent     = $str;
			$this->setOpt("CURLOPT_USERAGENT", $str);
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

            if(is_dir($dir))
            {
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
    public function autoDeleteFiles($directory, $timestamp, $beforeEqualAfter="<", $specificExtension="all")
    {
        if(!is_dir($directory)) return false;
        $dirs = scandir($directory);
        foreach($dirs as $dir) {
            if ($dir === "." || $dir === "..")
                continue;

            if (is_dir($dir)) {
                //$this->getDirectorySize($directory . DIRECTORY_SEPARATOR . $dir);
                $this->autoDeleteFiles($directory . DIRECTORY_SEPARATOR . $dir, $timestamp, $beforeEqualAfter, $specificExtension);
            }

            if (is_file($directory . DIRECTORY_SEPARATOR . $dir))
            {
                $file   = $directory . DIRECTORY_SEPARATOR . $dir;
                $ext    = pathinfo($file);

                if($specificExtension !== "all" && $specificExtension === $ext["extension"])
                    continue;
                switch ($beforeEqualAfter){
                    case("="):
                        if (filemtime($file) === $timestamp)
                            unlink($file);
                        break;
                    case("<"):
                        if (filemtime($file) < $timestamp)
                            unlink($file);
                        break;
                    case("<="):
                        if (filemtime($file) <= $timestamp)
                            unlink($file);
                        break;
                    case(">"):
                        if (filemtime($file) > $timestamp)
                            unlink($file);
                        break;
                    case(">="):
                        if (filemtime($file) >= $timestamp)
                            unlink($file);
                        break;
                }
            }

        }
        return true;
    }

    /**
     * $option["MaxDiskSize"] = 10*1024*1024. //max 10mb of Cache directory
     * $option["MaxFileOldDuration"] = (60*60*24)*7. // if file exists and not older than 7 days
     * $option["MinFileSize"]  = 2. // Minimum file size in byte to check if it has some data from previous request if not it will new req
     * then give back result of this file instead of sending request to server
     * @param array $option
     * @return array
     */
    public function setCacheSetting($option=[])
    {
        // Check if option array is empty and $this->options["cacheOptions"] exists
        // We return $this->options["cacheOptions"] to avoid to change previous user/default values

        if((count($option) == 0 || empty($option)))
        {
            if(isset($this->options["cacheOptions"])) {
                if (is_array($this->options["cacheOptions"]))
                    return $this->options["cacheOptions"];
            }
        }else {
                // add or modify new options add by user
                $this->options["cacheOptions"]   = $option;
                // get new fresh key/values
                $option     = $this->options["cacheOptions"];
        }


        // Lets say, our src/cache folder can have only 10mb by default
        if(!isset($option["MaxDiskSize"]))
            $option["MaxDiskSize"]  = 10*1024*1024;

        // Lets say, a file can have old duration
        // if file is older than (60*60*24)*7.. so we will make new request
        if(!isset($option["MaxFileOldDuration"]))
            $option["MaxFileOldDuration"]  = (60*60*24)*7;

        // Minimum file size in byte to check if it got some data from result or not
        if(!isset($option["MinFileSize"]))
            $option["MinFileSize"]  = 2;

        if(isset($this->options["cacheOptions"])) {
            if (is_array($this->options["cacheOptions"]))
                $this->options["cacheOptions"] = array_merge($this->options["cacheOptions"], $option);
            else
                $this->options["cacheOptions"]  = $option;
        }else
            $this->options["cacheOptions"]  = $option;

        return $this->options["cacheOptions"];
    }

    /**
     * Get cache Setting
     * @param string $val
     * @return mixed
     */
    protected function getCacheSetting($val="")
    {
        // Call the setcacheSetting
        $this->setCacheSetting(is_array($val) ? $val : []);
        if (!is_string($val))
            return $this->options["cacheOptions"];

       if (strlen($val) > 0 && isset($this->options["cacheOptions"][$val]))
           return $this->options["cacheOptions"][$val];
       else
           return $this->options["cacheOptions"];

    }

    /**
     * If this method is enable,
     *  - It will check for the file of last request executed of given url
     *  - If it doesn't found it will create the file for given url.
     *  - If file already exists, so it will not make a cURL request and give back response of last time request executed
     * Second parameter by default has following values :
     *  $option["MaxDiskSize"] = 10*1024*1024. //max 10mb of Cache directory
     *  $option["MaxFileOldDuration"] = (60*60*24)*7. // if file exists and not older than 7 days
     *  $option["MinFileSize"]  = 2. // Minimum file size in byte to check if it has some data from previous request if not it will new req
     *  then give back result of this file instead of sending request to server
     *
     *  $option["MinFileSize"] = 2. //cache file that can have minimum file size
     * @param bool $cache |default false
     * @param array $option
     * @return $this|bool
     */
    protected function _enableCache($cache=false)
	{
        //If cache is disabled then we will send fresh request to server(!)
        if($cache===false){
            $this->freshConnection(true);
            return $this;
        }

        $option                     = $this->getCacheSetting();
        $this->invokable["cache"]   = false;

        //check whether cache dir has enough space depend on MaxDiskSize Options
		if($this->getDirectorySize($this->cacheDir) > $option["MaxDiskSize"])
        {
            //Auto Maintenance
            $this->autoDeleteFiles($this->cacheDir, $option["MaxFileOldDuration"], ">=", "cache");
            $this->invokable["cache"]    = true;
        }

        $fileName   = $this->getCacheFileName();

		//Check if file Cache exists
		if(file_exists($fileName))
		{
		    $fileTime       = filemtime($fileName);
			$timeDiff 		= time() - $fileTime;

			// Check if the cache file size is equal or less than in option indicated
			if(filesize($fileName) <= (int)$option["MinFileSize"])
            {
                $this->invokable["cache"] 	= true;
            }

            // Check if the file is older than that indicated in option
		    if((int)$option["MaxFileOldDuration"] >= $timeDiff)
            {
				$this->result 				= file_get_contents($fileName);
            }else{
                $this->invokable["cache"] 	= true;
            }

        }else {
            $this->invokable["cache"] 	= true;
        }

		return $this;
	}

    /**
     * Call this method to check to enable the cache,
     *  - It will check for the file of last request executed of given url
     *  - If it doesn't found it will create the file for given url.
     *  - If file already exists, so it will not make a cURL request and give back response of last time request executed
     * Second parameter by default has following values :
     *  $option["MaxDiskSize"] = 10*1024*1024. //max 10mb of Cache directory
     *  $option["MaxFileOldDuration"] = (60*60*24)*7. // if file exists and not older than 7 days
     *  $option["MinFileSize"]  = 2. // Minimum file size in byte to check if it has some data from previous request if not it will new req
     *  then give back result of this file instead of sending request to server
     *
     *  $option["MinFileSize"] = 2. //cache file that can have minimum file size
     * @param bool $cache |default false
     * @param array $option
     * @return $this|bool
     */
    public function enableCache($val=true, $options=[])
    {
        $this->setCacheSetting($options);
        $this->isCacheEnable = $val;
        return $this;
    }

    /**
     * Determine whether cache option is enabled or not
     * @return bool
     */
    public function isCacheEnable()
    {
        return $this->isCacheEnable;
    }

    /**
     * @return $this
     */
    public function disableCache()
    {
        $this->enableCache(false);
        return $this;
    }

    /**
     * get the cache file name for current url requested
     * @return bool|string
     */
    public function getCacheFileName()
    {
        if(!$this->isSetUrl())
            return false;

        $url    = $this->getOpt("CURLOPT_URL");
        $urlParts = parse_url($url);

        // php 5.*
        $urlParts["host"]    = str_replace("http", "", $urlParts["host"]);
        $urlParts["host"]    = str_replace("https", "", $urlParts["host"]);
        $urlParts["host"]    = str_replace("www", "", $urlParts["host"]);
        $urlParts["host"]    = str_replace(".", "_", $urlParts["host"]);
        //php 7
        //$urlParts["host"]    = str_replace(["www", "https", "http", "."], "", $urlParts["host"]);
        //$urlParts["host"]    = str_replace(["."], "_", $urlParts["host"]);

        $fileName   = $this->cacheDir."/".substr(md5($url),0,10)."_".$urlParts["host"].".cache";
        return $fileName;
    }

    /**
     * Set the cache file name. Would be better if you set absolute Directory Path.
     * It returns true on if file set correctly otherwise false.
     * @param $name
     * @return bool
     */
    public function setCacheDir($name)
    {
        if(@is_dir($name))
        {
            // Check if file is writeAble
            if(@is_writable($name))
            {
                $this->cacheDir     = realpath($name);
                return true;
            }else
                return false;
        }else
            return false;
    }

    /**
     * @param bool $val
     * @return $this
     */
    public function freshConnection($val = true)
	{
	    if($val) $this->setOpt("CURLOPT_FRESH_CONNECT", $val);
	    else $this->removeCurlOpt("CURLOPT_FRESH_CONNECT");
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
        $this->setOpt("CURLOPT_PORT", $port);
        return $this;
    }

    /**
     * Set random Port number
     * @param string $typeOf
     * @return $this
     */
    public function setRandomPort($typeOf="")
    {
        if(class_exists("\\Proxy\Proxy")){
            $proxy = new Proxy();
            $port   = $proxy->getIP($typeOf);
            $this->setPort($port["port"]);
        }else{
            $this->setPort(rand(1100,10000));
        }

        return $this;
    }

    /**
     * Set proxy
     * @param $proxy
     * @return $this
     */
    public function setProxy($proxy, $port="")
    {

        $this->setOpt("CURLOPT_PROXY", $proxy);

        if(strlen($port)>0)
            $this->setPort($port);

        return $this;
    }

    public function setRandomProxy($typeOf)
    {
        if(class_exists("\\Proxy\Proxy")){
            $proxy = new Proxy();
            $ip   = $proxy->getIP($typeOf);
            $this->setProxy($ip["ip"].":".$ip["port"]);
        }else{
            $inHopeSomeDaySomeOneWillUpdateTheseProxyArray = ["136.55.8.193","35.185.63.122","45.77.143.196","45.77.143.125"];
            // btw this else cond is not going be execute never but but but
            $rand   = rand(0,3);
            $this->setProxy($inHopeSomeDaySomeOneWillUpdateTheseProxyArray[$rand]);
        }
        return $this;
    }

    public function setProxyAuth($username, $password)
    {
        $this->setOpt("CURLOPT_PROXYUSERPWD", $username.":".$password);
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
     * @param bool $val
     * @return $this
     */
    public function verifyPeer($val=true)
    {
        $this->setOpt("CURLOPT_SSL_VERIFYPEER", $val);
        return $this;
    }

    /**
     * @param bool $val
     * @return $this
     */
    public function verifyHost($val=false)
    {
        $this->setOpt("CURLOPT_SSL_VERIFYHOST", $val);
        return $this;
    }

    /**
     * @return $this
     */
    public function fastTCP()
    {
        $this->setOpt("CURLOPT_TCP_FASTOPEN", true);
        return $this;
    }

    public function getFunctionHeadersFilename($ext=".heads")
    {
        if(!$this->isSetUrl())
            return false;

        $url    = $this->getOpt("CURLOPT_URL");
        $urlParts = parse_url($url);

        // php 5.*
        $urlParts["host"]    = str_replace("http", "", $urlParts["host"]);
        $urlParts["host"]    = str_replace("https", "", $urlParts["host"]);
        $urlParts["host"]    = str_replace("www", "", $urlParts["host"]);
        $urlParts["host"]    = str_replace(".", "_", $urlParts["host"]);
        //php 7
        //$urlParts["host"]    = str_replace(["www", "https", "http", "."], "", $urlParts["host"]);
        //$urlParts["host"]    = str_replace(["."], "_", $urlParts["host"]);

        $fileName   = realpath($this->functionHeadersDir).DIRECTORY_SEPARATOR.substr(md5($url),0,10)."_".$urlParts["host"].$ext;

        return ($fileName);
    }

    protected function writeFunctionHeaders()
    {
        $file   = $this->getFunctionHeadersFilename();
        $this->addExtraFunctionHeadersValues("CURLINFO_SSL_VERIFYRESULT", true)
            ->addExtraFunctionHeadersValues("CURLINFO_HTTP_CODE", true)
            ->addExtraFunctionHeadersValues("CURLINFO_EFFECTIVE_URL", true)
            ->addExtraFunctionHeadersValues("CURLINFO_NAMELOOKUP_TIME ", true)
            ->addExtraFunctionHeadersValues("CURLINFO_REDIRECT_TIME ", true)
            ->addExtraFunctionHeadersValues("CURLINFO_REDIRECT_URL", true)
            ->addExtraFunctionHeadersValues("redirect_url", true)
            ->addExtraFunctionHeadersValues("CURLINFO_PRIMARY_IP", true)
            ->addExtraFunctionHeadersValues("CURLINFO_PRIMARY_PORT", true)
            ->addExtraFunctionHeadersValues("CURLINFO_SIZE_DOWNLOAD", true)
            ->addExtraFunctionHeadersValues("CURLINFO_SPEED_DOWNLOAD", true)
            ->addExtraFunctionHeadersValues("CURLINFO_CONTENT_LENGTH_DOWNLOAD", true)
            ->addExtraFunctionHeadersValues("CURLINFO_SSL_ENGINES", true)
            ->addExtraFunctionHeadersValues("CURLINFO_CERTINFO", true)
            ->addExtraFunctionHeadersValues("CURLINFO_CONTENT_TYPE", 0)
            ->addExtraFunctionHeadersValues("CURLINFO_REQUEST_SIZE", 0)
            ->addExtraFunctionHeadersValues("CURLINFO_COOKIELIST", true);
        @file_put_contents($file, json_encode($this->functionHeaders));
        return $this;
    }

    protected function addExtraFunctionHeadersValues($key, $value=true)
    {
        if(is_bool($value)){
            $this->functionHeaders[$key]    = $this->getCurlInfo($key);
            return $this;
        }
        if(!isset($this->functionHeaders[$key]))
            $this->functionHeaders[$key]    = $value;
        return $this;
    }

    protected function getFunctionHeadersFromCache($option)
    {
        $file = $this->getFunctionHeadersFilename();
        if(!file_exists($file))
            return false;
        $data = file_get_contents($file);
        $data = json_decode($data,1);
        if(isset($data[$option]))
            return $data[$option];
        else
            return false;
    }

    protected function getRequestInfoOption($option)
    {
        if(isset($this->functionHeaders[$option])){
            if(is_array($this->functionHeaders[$option]) && !empty($this->functionHeaders[$option])){
                return $this->functionHeaders[$option];
            }elseif(is_string($this->functionHeaders[$option])){
                if(strlen($this->functionHeaders[$option])>0)
                    return $this->functionHeaders[$option];
            }else{
                $data = $this->getCurlInfo($option);
                return $data;
            }
        }elseif(!$this->hasFalseValue($this->invokable) && !empty($this->invokable)){
            // if request has executed so we can simply get the data and return
            return $this->getCurlInfo($option);
        }

        $data       = $this->getFunctionHeadersFromCache($option);
        if($data !== false) return $data;
        return "";
    }

    /**
     * @return mixed
     */
    public function getSSLResult()
    {
        return $this->getRequestInfoOption("CURLINFO_SSL_VERIFYRESULT");
    }

    /**
     * @return mixed
     */
    public function getCookieList()
    {
        return $this->getRequestInfoOption("CURLINFO_COOKIELIST");
    }

    /**
     * @return mixed
     */
    public function getRequestSize()
    {
        return $this->getRequestInfoOption("CURLINFO_REQUEST_SIZE");
    }

    /**
     * @return mixed
     */
    public function getHTTPCode()
    {
        return $this->getRequestInfoOption("CURLINFO_HTTP_CODE");
    }

    /**
     * Return http status if exists otherwise an empty string
     * Some time it returns cookies depend on site
     * for example if you request this url https://www.google.it/?gfe_rd=cr&dcr=0&ei=SEfFWcTsKrLBXoHSnagK&gws_rd=ssl
     * it will return you status as cookies..
     * @See https://www.google.com/support/accounts/answer/151657?hl=en
     * @return mixed|string
     */
    public function getHeaderStatus()
    {
        return $this->getRequestInfoOption("status");
    }

    /**
     * @return mixed|array|string
     */
    public function getCacheControl()
    {
        return $this->getRequestInfoOption("cache-control");
    }

    /**
     * @return mixed|array|string
     */
    public function getContentType()
    {
        return $this->getRequestInfoOption("content-type");
    }

    /**
     * Returns the connection status of request e.g close, maybe keep-alive..
     * @return mixed|array|string
     */
    public function getConnectionStatus()
    {
        return $this->getRequestInfoOption("connection");
    }

    /**
     * @return mixed|array|string
     */
    public function getServerType()
    {
        return ($this->getRequestInfoOption("server"));
    }

    /**
     * Returns curl Instance
     * @return resource
     */
    public function getInstance()
    {
        return $this->cURL;
    }

    /**
     * @return mixed|array|string
     */
    public function getCookies()
    {
       if(isset($this->functionHeaders["set-cookie"])){
            if(is_array($this->functionHeaders["set-cookie"]) && !empty($this->functionHeaders["set-cookie"])){
                return $this->functionHeaders["set-cookie"];
            }elseif(is_string($this->functionHeaders["set-cookie"])){
                if(strlen($this->functionHeaders["set-cookie"])>0)
                    return $this->functionHeaders["set-cookie"];
            }
        }
        $data = $this->getFunctionHeadersFromCache("set-cookie");
        if($data !== false) return $data;
        else return $this->getCookiesFromFile();
    }

    /**
     * Return cookies from file if exists else empty string.
     * @param string $cookiesFile
     * @return array|bool|mixed|string
     */
    protected function getCookiesFromFile($cookiesFile="")
    {
        if(strlen($cookiesFile)<=0)
            $cookiesFile = $this->getCookiesFileName();

        if(file_exists($cookiesFile)){
            $data = file ($cookiesFile, FILE_SKIP_EMPTY_LINES|FILE_IGNORE_NEW_LINES);
            if($this->str_contains($data[0], "#"))unset($data[0]);
            if($this->str_contains($data[1], "#"))unset($data[1]);
            if($this->str_contains($data[2], "#"))unset($data[2]);
            return array_values($data);
        }
        return "";
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
     * Real url is where from the request is executed
     * @return mixed
     */
    public function getRealUrl()
    {
        return $this->getFunctionHeadersFromCache("CURLINFO_EFFECTIVE_URL");
    }

    /**
     * Get Redirected url
     * @return mixed
     */
    public function getRedirectedUrl()
    {
        //return $this->getCurlInfo("CURLINFO_REDIRECT_URL ");
        return $this->getFunctionHeadersFromCache("redirect_url");
    }

    /**
     * Returns the array of errors if occurred or empty array in case of no error
     * @return array
     */
    public function getErrors()
    {
        return $this->errors;
    }

    /**
     * check whether str contains
     * @param $haystack
     * @param $needles
     * @return bool
     */
    public function str_contains($haystack, $needles)
    {
        foreach ((array) $needles as $needle){
            if ($needle !== '' && mb_strpos($haystack, $needle) !== false) {
                return true;
            }
        }
        return false;
    }


}
