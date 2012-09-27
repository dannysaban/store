<?php

/**
 *  ICEPAY Basicmode API 2 library
 *
 *  @version 1.0.2
 *  @author Olaf Abbenhuis
 *  @copyright Copyright (c) 2011, ICEPAY
 *
 */


/**
 *  Icepay_Project_Helper class
 *  A helper for all-in-one solutions
 * @author Olaf Abbenhuis
 * @todo Add more useful helper classes, perhaps database related
 *
 *
 */
class Icepay_Project_Helper {

    private $_release = "1.0.2";
    
    private $_basic;
    private $_result;
    private $_postback;

    public function basic(){
        if (!isset($this->_basic)) $this->_basic = new Icepay_Basicmode();
        return $this->_basic;
    }

    public function result(){
        if (!isset($this->_result)) $this->_result = new Icepay_Result();
        return $this->_result;
    }

    public function postback(){
        if (!isset($this->_postback)) $this->_postback = new Icepay_Postback();
        return $this->_postback;
    }
    
    public function getVersion(){
        return $this->_release;
    }

}

/**
 *  Icepay_Api_Basic class
 *  Loads and filters the paymentmethod classes
 *  @author Olaf Abbenhuis
 *
 * @var $instance Instance Class object
 * @var string $_content The contents of the files for the fingerprint
 * @var string $_folderPaymentMethods Folder of paymentmethod classes
 * @var array $paymentMethods List of all classes
 * @var array $_paymentMethods Filtered list
 *
 */
class Icepay_Api_Basic {

    private static $instance;

    private $version = "1.0.1";
    private $_content = null;
    private $_folderPaymentMethods;

    private $paymentMethods = null; // Classes
    private $_paymentMethod = null; // Filtered list

    /**
    * Create an instance
    * @since version 1.0.0
    * @access public
    * @return instance of self
    */
    public static function getInstance() {
        if(!self::$instance) self::$instance = new self();
        return self::$instance;
    }

    /**
    * Set the default paymentmethod classes folder
    * @since version 1.0.0
    * @access public
    */
    public function  __construct() {
        $this->setPaymentMethodsFolder(dirname(__FILE__).'/paymentmethods/');
    }

    /**
    * Set the folder where the paymentmethod classes reside
    * @since version 1.0.0
    * @access public
    * @param string $dir Folder of the paymentmethod classes
    */
    public function setPaymentMethodsFolder($dir){
        $this->_folderPaymentMethods = $dir;
        return $this;
    }

    /**
    * Store the paymentmethod class names in the paymentmethods array.
    * @since version 1.0.0
    * @access public
    * @param string $dir Folder of the paymentmethod classes
    */
    public function readFolder($dir = null){
        if ($dir) $this->setPaymentMethodsFolder($dir);
        
        $this->paymentMethods = array();
        try {
            $folder = $this->_folderPaymentMethods;
            $handle = is_dir($folder)?opendir($folder):false;

            if ($handle) {
                while (false !== ($file = readdir($handle))) {
                    if ($file != "." && $file != ".."){
                        require_once (sprintf("%s/%s",$this->_folderPaymentMethods,$file));
                        $name = strtolower(substr($file, 0, strlen($file)-4));
                        $className = "Icepay_Paymentmethod_".ucfirst($name);
                        $this->paymentMethods[$name] = $className;
                    }
                }
            }
        } catch (Exception $e){
            throw new Exception ($e->getMessage());
        }
        return $this;
    }

    /**
    * Load all the paymentmethod classes and store these in the filterable paymentmethods array.
    * @since version 1.0.0
    * @access public
    */
    public function prepareFiltering(){
        foreach($this->paymentMethods as $name => $class){
            $this->_paymentMethod[$name] = new $class();
        }
        return $this;
    }

    /**
    * Filter the paymentmethods array by currency
    * @since version 1.0.0
    * @access public
    * @param string $currency Language ISO 4217 code
    */
    public function filterByCurrency($currency){
        foreach($this->_paymentMethod as $name => $class){
            if (!in_array($currency, $class->getSupportedCurrency()) && !in_array('00', $class->getSupportedCurrency())) unset($this->paymentMethods[$name]);
        }
        return $this;
    }

    /**
    * Filter the paymentmethods array by country
    * @since version 1.0.0
    * @access public
    * @param string $country Country ISO 3166-1-alpha-2 code
    */
    public function filterByCountry($country){
        foreach($this->_paymentMethod as $name => $class){
            if (!in_array(strtoupper($country), $class->getSupportedCountries()) && !in_array('00', $class->getSupportedCountries())) unset($this->paymentMethods[$name]);
        }
        return $this;
    }

    /**
    * Filter the paymentmethods array by language
    * @since version 1.0.0
    * @access public
    * @param string $language Language ISO 639-1 code
    */
    public function filterByLanguage($language){
        foreach($this->_paymentMethod as $name => $class){
            if (!in_array(strtoupper($language), $class->getSupportedLanguages()) && !in_array('00', $class->getSupportedLanguages())) unset($this->paymentMethods[$name]);
        }
        return $this;
    }

    /**
    * Filter the paymentmethods array by amount
    * @since version 1.0.0
    * @access public
    * @param int $amount Amount in cents
    */
    public function filterByAmount($amount){
        foreach($this->_paymentMethod as $name => $class){
            $amountRange = $class->getSupportedAmountRange();
            if (    intval($amount) >= $amountRange["minimum"] &&
                    intval($amount) <= $amountRange["maximum"] ) {
            } else unset($this->paymentMethods[$name]);
        }
        return $this;
    }

    /**
    * Return the filtered paymentmethods array
    * @since version 1.0.0
    * @access public
    * @return array Paymentmethods
    */
    public function getArray(){
        return $this->paymentMethods;
    }


}

/**
 *  Icepay_StatusCode static class
 *  Contains the payment statuscode constants
 *  @author Olaf Abbenhuis
 */
class Icepay_StatusCode {
    const OPEN           = "OPEN";
    const ERROR          = "ERR";
    const SUCCESS        = "OK";
    const REFUND         = "REFUND";
    const CHARGEBACK     = "CBACK";
}


/**
 *  Icepay_Basicmode_Interface_Abstract interface
 *  @author Olaf Abbenhuis
 */
interface Icepay_Basicmode_Interface_Abstract {
    public function setMerchantID($mechantID);
    public function setSecretCode($secretCode);
    public function setIssuer($issuer);
    public function setCountry($country);
    public function setCurrency($currency);
    public function setLanguage($lang);
    public function setAmount($amount);
    public function setOrderID($id  = "");
    public function setReference($reference = "");
    public function setDescription($info = "");
    public function setProtocol($protocol = "https");
    public function setSuccessURL($url = "");
    public function setErrorURL($url = "");
    public function enableLogging($enable = true, $dir = null, $print = false);
}


/**
 *  Icepay_Basicmode class
 *  To start a basicmode payment
 *  @author Olaf Abbenhuis
 */
class Icepay_Basicmode implements Icepay_Basicmode_Interface_Abstract {

    protected $_basicmodeURL = "pay.icepay.eu/basic/";
    protected $_postProtocol = "https";
    protected $_basicMode = false;
    protected $_fingerPrint = null;
    
    private   $_checkout_version = 2;

    protected $data = null;
    
    

    protected $_merchantID = null;
    protected $_secretCode = null;

    protected $_loggingDirectory = "./log/";
    protected $_loggingEnabled = false;
    protected $_logToFile = false;
    protected $_logToScreen = false;
    protected $_logToHook = false;
    protected $_logHookClass = null;
    protected $_logHookFunc = null;
    protected $_logHookArgs = null;

    protected       $version        = "1.0.1";
    protected       $_method        = null;
    protected       $_readable_name = "Basicmode";
    protected       $_issuer        = null;
    protected       $_country       = null;
    protected       $_language      = null;
    protected       $_currency      = null;


    private $_defaultCountryCode    = "00";

    /**
    * Ensure the class data is set
    * @since version 1.0.0
    * @access public
    */
    public function  __construct() {
        $this->data = new stdClass ();
    }

    /**
    * Post the fields and return the URL generated by ICEPAY
    * @since version 1.0.0
    * @access public
    * @return string URL or Error message
    */
    public function getURL(){

        if (!isset($this->_merchantID)) throw new Exception('Merchant ID not set, use the setMerchantID() method');
        if (!isset($this->_secretCode)) throw new Exception('Merchant ID not set, use the setSecretCode() method');

        if (!isset($this->data->ic_country)){
            if (count($this->_country) == 1) {
                $this->data->ic_country = $this->_country[0];
            //} else throw new Exception('Country not set, use the setCountry() method');
            } else $this->data->ic_country = $this->_defaultCountryCode;
        }
        
        if (!isset($this->data->ic_issuer) && $this->_method != null){
            if (count($this->_issuer) == 1) {
                $this->data->ic_issuer = $this->_issuer[0];
            } else throw new Exception('Issuer not set, use the setIssuer() method');
        }

        if (!isset($this->data->ic_language)){
            if (count($this->_language) == 1) {
                $this->data->ic_language = $this->_language[0];
            } else throw new Exception('Language not set, use the setLanguage() method');
        }

        if (!isset($this->data->ic_currency)){
            if (count($this->_currency) == 1) {
                $this->data->ic_currency = $this->_currency[0];
            } else throw new Exception('Currency not set, use the setCurrency() method');
        }

        if (!isset($this->data->ic_amount))     throw new Exception('Amount not set, use the setAmount() method');
        if (!isset($this->data->ic_orderid))    throw new Exception('OrderID not set, use the setOrderID() method');

        if (!isset($this->data->ic_reference))      $this->data->ic_reference = "";
        if (!isset($this->data->ic_description))    $this->data->ic_description = "";

        /*
         * Dynamic URLs
         * @since 1.0.1
         */
        if (!isset($this->data->ic_urlcompleted))   $this->data->ic_urlcompleted = "";
	if (!isset($this->data->ic_urlerror))       $this->data->ic_urlerror = "";
        $this->data->ic_version         = $this->_checkout_version;

        $this->data->ic_paymentmethod   = $this->_method;
        $this->data->ic_merchantid      = $this->_merchantID;
        $this->data->chk                = $this->generateCheckSumDynamic();
        

        if ($this->_method != null) return $this->postRequest($this->basicMode(), $this->prepareParameters());
        return sprintf("%s&%s",$this->basicMode(),$this->prepareParameters());
    }

    /**
    * Calls the API to generate a Fingerprint
    * @since version 1.0.0
    * @access protected
    * @return string SHA1 hash
    */
    protected function generateFingerPrint(){
        if ( $this->_fingerPrint != null ) return $this->fingerPrint;
        $this->fingerPrint = sha1($this->getVersion());
        return $this->fingerPrint;
    }

    /**
    * Generates a URL to the ICEPAY basic API service
    * @since version 1.0.0
    * @access protected
    * @return string URL
    */
    protected function basicMode()
    {
        if ( $this->_method != null )
        {
            $querystring = http_build_query( array(
                    'type'              => $this->_method,
                    'checkout'		=> 'yes',
                    'ic_redirect'	=> 'no',
                    'ic_country'	=> $this->data->ic_country,
                    'ic_language'	=> $this->data->ic_language,
                    'ic_fp'		=> $this->generateFingerPrint()
            ),'','&' );
        }
        else
        {
                $querystring = http_build_query( array(
                        'ic_country'	=> $this->data->ic_country,
                        'ic_language'	=> $this->data->ic_language,
                        'ic_fp'         => $this->generateFingerPrint()
                ),'','&' );												   
        }

        return sprintf("%s://%s?%s",$this->_postProtocol,$this->_basicmodeURL,$querystring);
    }

    /**
    * Used to connect to the ICEPAY servers
    * @since version 1.0.0
    * @access protected
    * @param string $url
    * @param array $data
    * @return string Returns a response from the specified URL
    */
    protected function postRequest( $url, $data ){
            $params = array
            (
                    'http' => array
                    (
                            'method'	=> 'POST',
                            'content'	=> $data,
                            'header'	=> "Content-Type: application/x-www-form-urlencoded"
                    )
            );

            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_HEADER, 0);
            curl_setopt($ch, CURLOPT_POST,true);
            curl_setopt($ch, CURLOPT_POSTFIELDS,$data);
            $response = curl_exec($ch);
            curl_close($ch);

            if ( !$response ) throw new Exception( "Error reading $url" );

            if ( ( substr(strtolower($response), 0, 7) == "http://" ) || ( substr(strtolower($response), 0, 8) == "https://" ) )
            {
                    return $response;
            }
            else throw new Exception( "Server response: " . strip_tags($response) );
            
    }

    /**
    * Generate checksum for basicmode checkout
    * @since version 1.0.0
    * @access protected
    * @return string SHA1 encoded
    */
    protected function generateCheckSum(){
        return sha1 (
            sprintf("%s|%s|%s|%s|%s|%s|%s",
                    $this->_merchantID,
                    $this->_secretCode,
                    $this->data->ic_amount,
                    $this->data->ic_orderid,
                    $this->data->ic_reference,
                    $this->data->ic_currency,
                    $this->data->ic_country
                    )
            );
    }

    /**
    * Generate checksum for basicmode checkout using dynamic urls
    * @since version 1.0.1
    * @access protected
    * @return string SHA1 encoded
    */
    protected function generateCheckSumDynamic(){
        return sha1 (
            sprintf("%s|%s|%s|%s|%s|%s|%s|%s|%s",
                    $this->_merchantID,
                    $this->_secretCode,
                    $this->data->ic_amount,
                    $this->data->ic_orderid,
                    $this->data->ic_reference,
                    $this->data->ic_currency,
                    $this->data->ic_country,
                    $this->data->ic_urlcompleted,
                    $this->data->ic_urlerror
                    )
            );
    }

    /**
    * Create the query string
    * @since version 1.0.0
    * @access protected
    * @return string
    */
    protected function prepareParameters(){
        return http_build_query($this->data,'','&');
    }

    /**
    * Validate data
    * @since version 1.0.0
    * @access protected
    * @param string $needle
    * @param array $haystack
    * @return boolean
    */
    protected function validateValue($needle, $haystack = null){
        $result = true;
        if ($haystack && $result && $haystack[0] != "00") $result = in_array($needle, $haystack);
        return $result;
    }

    /**
    * Set the Merchant ID field
    * @since version 1.0.0
    * @access public
    * @param string $merchantID 5 digit merchant ID !Required
    */
    public function setMerchantID($merchantID){
        if (strlen($merchantID) != 5) throw new Exception('MerchantID not valid');
        $this->_merchantID = $merchantID;
        return $this;
    }

    /**
    * Set the secret code field
    * @since version 1.0.0
    * @access public
    * @param string $secretCode 40char secret code !Required
    */
    public function setSecretCode($secretCode){
        if (strlen($secretCode) != 40) throw new Exception('Secretcode not valid');
        $this->_secretCode = $secretCode;
        return $this;
    }

    /**
    * Set the country field
    * @since version 1.0.0
    * @access public
    * @param string $currency Country ISO 3166-1-alpha-2 code !Required
    * @example setCountry("NL") //Netherlands
    */
    public function setCountry($country){
        $country = strtoupper($country);
        if (!$this->validateValue($country, $this->_country)) throw new Exception('Country not supported');
        $this->data->ic_country = $country;
        return $this;
    }

    /**
    * Set the currency field
    * @since version 1.0.0
    * @access public
    * @param string $currency Language ISO 4217 code !Required
    * @example setCurrency("EUR") //Euro
    */
    public function setCurrency($currency){
        if (!$this->validateValue($currency, $this->_currency)) throw new Exception('Currency not supported');
        $this->data->ic_currency = $currency;
        return $this;
    }

    /**
    * Set the language field
    * @since version 1.0.0
    * @access public
    * @param string $lang Language ISO 639-1 code !Required
    * @example setLanguage("EN") //English
    */
    public function setLanguage($lang){
        if (!$this->validateValue($lang, $this->_language)) throw new Exception('Language not supported');
        $this->data->ic_language = $lang;
        return $this;
    }

    /**
    * Set the amount field
    * @since version 1.0.0
    * @access public
    * @param int $amount !Required
    */
    public function setAmount($amount){
        intval($amount);
        if (!$this->validateValue($amount)) throw new Exception('Amount invalid');
        $this->data->ic_amount = $amount;
        return $this;
    }

    /**
    * Set the order ID field (optional)
    * @since version 1.0.0
    * @access public
    * @param string $id
    */
    public function setOrderID($id = ""){
        $this->data->ic_orderid = $id;
        return $this;
    }

    /**
    * Set the reference field (optional)
    * @since version 1.0.0
    * @access public
    * @param string $reference
    */
    public function setReference($reference = ""){
        $this->data->ic_reference = $reference;
        return $this;
    }

    /**
    * Set the description field (optional)
    * @since version 1.0.0
    * @access public
    * @param string $info
    */
    public function setDescription($info = ""){
        $this->data->ic_description = $info;
        return $this;
    }

    /**
    * Set the success url field (optional)
    * @since version 1.0.1
    * @access public
    * @param string $url
    */
    public function setSuccessURL($url = ""){
        $this->data->ic_urlcompleted = $url;
        return $this;
    }

    /**
    * Set the error url field (optional)
    * @since version 1.0.1
    * @access public
    * @param string $url
    */
    public function setErrorURL($url = ""){
        $this->data->ic_urlerror = $url;
        return $this;
    }

    /**
    * Set the protocol for local testing
    * @since version 1.0.0
    * @access public
    * @param string $protocol [http|https]
    */
    public function setProtocol($protocol = "https"){
        $this->_postProtocol = $protocol;
        return $this;
    }

    /**
    * Enable or disable all logging
    * @since version 1.0.0
    * @access public
    * @param boolean $enable
    */
    public function enableLogging($enable = true, $logToFile = false, $logDir = null, $printLog = false, $logToHook = false){
        $this->_loggingEnabled = $enable;
        $this->_logToFile = $logToFile;
        $this->_logToScreen = $printLog;
        $this->_logToHook = $logToHook;
        if ($logDir) $this->_loggingDirectory = $logDir;
        return $this;
    }

    /**
    * Enable or disable log to file
    * @since version 1.0.0
    * @access public
    * @param boolean $enable
    */
    public function logToFile($enable = true, $dir = null){
        $this->_logToFile = $enable;
        if ($dir) $this->_loggingDirectory = $dir;
        return $this;
    }

    /**
    * Enable or disable ouput to screen logging
    * @since version 1.0.0
    * @access public
    * @param boolean $enable
    */
    public function logToScreen($enable = true){
        $this->_logToScreen = $enable;
        return $this;
    }

    /**
    * Enable or disable logging to a hooked class
    * @since version 1.0.0
    * @access public
    * @param boolean $enable
    */
    public function logToHook($enable = true){
        $this->_logToHook = $enable;
        return $this;
    }

    /**
    * Run an externally loaded class to log a line (string)
    * @since version 1.0.0
    * @access public
    * @param string $class The loaded (static) logging class
    * @param string $func The logging function to call
    */
    public function hookLogClass($class, $func){
        if (class_exists($class)) {
            $this->_logHookClass = new $class();
            $this->_logHookFunc = $func;
        }
        return $this;
    }

    /**
    * Log a line
    * @since version 1.0.0
    * @access public
    * @param string $line
    */
    public function log($line){
        if ( !$this->_loggingEnabled ) return false;

        date_default_timezone_set("Europe/Paris");
        $line = sprintf( "%s - %s\r\n", date("H:i:s", time()), $line );

        // Log to screen
        if ($this->_logToScreen) echo(sprintf("%s\n\r<BR>",$line));

        // Log to hooked class
        if ( $this->_logToHook && $this->_logHookClass ) {
            $func = $this->_logHookFunc;
            if (function_exists($func)) $this->_logHookClass->$func($line);
        }

        // Log to hooked class
        if ( $this->_logToFile ) {
            $filename = sprintf( "%s/#%s.log", $this->_loggingDirectory, date("Ymd", time()) );
            try {
                $fp = @fopen( $filename, "a" );
                @fwrite( $fp, $line );
                @fclose( $fp );
            } catch (Exception $e){
                throw new Exception ($e->getMessage());
            };
        };
        return true;
    }



    /**
    * Get the version of the API or the loaded payment method class
    * @since version 1.0.0
    * @access public
    * @return string
    */
    public function getVersion(){
        return $this->version;
    }



    /**
    * Sets the issuer and checks if the issuer exists within the paymentmethod
    * @since version 1.0.0
    * @access public
    * @param string $issuer ICEPAY Issuer code
    */
    public function setIssuer($issuer){
        if (!$this->validateValue($issuer, $this->_issuer) && $this->_method != null) throw new Exception('Issuer not supported');
        $this->data->ic_issuer = $issuer;
        return $this;
    }

    /**
    * Get the version of the API or the loaded payment method class
    * @since version 1.0.1
    * @access public
    * @return string
    */
    public function getReadableName(){
        return $this->_readable_name;
    }

    /**
    * Get the supported issuers of the loaded paymentmethod
    * @since version 1.0.0
    * @access public
    * @return array The issuer codes of the paymentmethod
    */
    public function getSupportedIssuers(){
        return $this->_issuer;
    }

    /**
    * Get the supported countries of the loaded paymentmethod
    * @since version 1.0.0
    * @access public
    * @return array The country codes of the paymentmethod
    */
    public function getSupportedCountries(){
        return $this->_country;
    }

    /**
    * Get the supported currencies of the loaded paymentmethod
    * @since version 1.0.0
    * @access public
    * @return array The currency codes of the paymentmethod
    */
    public function getSupportedCurrency(){
        return $this->_currency;
    }

    /**
    * Get the supported languages of the loaded paymentmethod
    * @since version 1.0.0
    * @access public
    * @return array The Language codes of the paymentmethod
    */
    public function getSupportedLanguages(){
        return $this->_language;
    }

    /**
    * Get the general amount range of the loaded paymentmethod
    * @since version 1.0.0
    * @access public
    * @return array [minimum(uint), maximum(uint)]
    */
    public function getSupportedAmountRange(){
        return $this->_amount;
    }

}


/**
 *  Icepay_Postback class
 *  To handle the postback
 *  @author Olaf Abbenhuis
 */
class Icepay_Postback extends Icepay_Basicmode {

    public function  __construct() {
        $this->data = new stdClass();
    }

    /**
    * Return minimized transactional data
    * @since version 1.0.0
    * @access public
    * @return string
    */
    public function getTransactionString(){
        return sprintf(
                "Paymentmethod: %s \n| OrderID: %s \n| Status: %s \n| StatusCode: %s \n| PaymentID: %s \n| TransactionID: %s \n| Amount: %s",
                isset($this->data->paymentMethod)?$this->data->paymentMethod:"",
                isset($this->data->orderID)?$this->data->orderID:"",
                isset($this->data->status)?$this->data->status:"",
                isset($this->data->statusCode)?$this->data->statusCode:"",
                isset($this->data->paymentID)?$this->data->paymentID:"",
                isset($this->data->transactionID)?$this->data->transactionID:"",
                isset($this->data->amount)?$this->data->amount:""
                );
    }

    /**
    * Return the statuscode field
    * @since version 1.0.0
    * @access public
    * @return string
    */
    public function getStatus(){
        return (isset($this->data->status))?$this->data->status:null;
    }

    /**
    * Return the orderID field
    * @since version 1.0.0
    * @access public
    * @return string
    */
    public function getOrderID(){
        return (isset($this->data->orderID))?$this->data->orderID:null;
    }

    /**
    * Return the postback checksum
    * @since version 1.0.0
    * @access protected
    * @return string SHA1 encoded
    */
    protected function generateChecksumForPostback(){
        return sha1 (
            sprintf("%s|%s|%s|%s|%s|%s|%s|%s|%s|%s|%s|%s",
            $this->_secretCode,
            $this->_merchantID,
            $this->data->status,
            $this->data->statusCode,
            $this->data->orderID,
            $this->data->paymentID,
            $this->data->reference,
            $this->data->transactionID,
            $this->data->amount,
            $this->data->currency,
            $this->data->duration,
            $this->data->consumerIPAddress
            )
        );
    }

    /**
    * Return the version checksum
    * @since version 1.0.2
    * @access protected
    * @return string SHA1 encoded
    */
    protected function generateChecksumForVersion(){
        return sha1 (
            sprintf("%s|%s|%s|%s",
            $this->_secretCode,
            $this->_merchantID,
            $this->data->status,
            substr(strval(time()), 0, 8)
            )
        );
    }

    /**
    * Returns the postback response parameter names, useful for a database install script
    * @since version 1.0.1
    * @access public
    * @return array
    */
    public function getPostbackResponseFields(){
        return array(
            //object reference name => post param name
            "status"                => "Status",
            "statusCode"            => "StatusCode",
            "merchant"              => "Merchant",
            "orderID"               => "OrderID",
            "paymentID"             => "PaymentID",
            "reference"             => "Reference",
            "transactionID"         => "TransactionID",
            "consumerName"          => "ConsumerName",
            "consumerAccountNumber" => "ConsumerAccountNumber",
            "consumerAddress"       => "ConsumerAddress",
            "consumerHouseNumber"   => "ConsumerHouseNumber",
            "consumerCity"          => "ConsumerCity",
            "consumerCountry"       => "ConsumerCountry",
            "consumerEmail"         => "ConsumerEmail",
            "consumerPhoneNumber"   => "ConsumerPhoneNumber",
            "consumerIPAddress"     => "ConsumerIPAddress",
            "amount"                => "Amount",
            "currency"              => "Currency",
            "duration"              => "Duration",
            "paymentMethod"         => "PaymentMethod",
            "checksum"              => "Checksum");
    }



    /**
    * Validate for version check
    * @since version 1.0.2
    * @access public
    * @return boolean
    */
    public function validateVersion() {
        if ($_SERVER['REQUEST_METHOD'] != 'POST') return false;
        if ($this->generateChecksumForVersion() != $this->data->checksum) return false;
        return true;
    }
    
    /**
    * Has Version Check status
    * @since version 1.0.2
    * @access public
    * @return boolean
    */
    public function isVersionCheck(){
        if ($_SERVER['REQUEST_METHOD'] != 'POST') return false;
        if ($this->data->status != "VCHECK") return false;
        return true;
    }
    
    

    /**
    * Validate the postback data
    * @since version 1.0.0
    * @access public
    * @return boolean
    */
    public function validate() {
        if ($_SERVER['REQUEST_METHOD'] != 'POST') {
            $this->log("Invalid request method");
            return false;
        };

        //$this->log(sprintf("Postback: %s", serialize($_POST)));

        /* @since version 1.0.2 */
        foreach($this->getPostbackResponseFields() as $obj => $param){
            $this->data->$obj = (isset($_POST[$param])) ? $_POST[$param] : "";
        }

        if (!is_numeric($this->data->merchant)) {
            $this->log("Merchant ID is not numeric");
            return false;
        }

        if (!is_numeric($this->data->amount)) {
            $this->log("Amount is not numeric");
            return false;
        }

        if ($this->_merchantID != $this->data->merchant) {
            $this->log("Invalid Merchant ID");
            return false;
        }

        if (!in_array(strtoupper($this->data->status), array(
                    Icepay_StatusCode::OPEN,
                    Icepay_StatusCode::SUCCESS,
                    Icepay_StatusCode::ERROR,
                    Icepay_StatusCode::REFUND,
                    Icepay_StatusCode::CHARGEBACK
                ))) {
            $this->log("Unknown status");
            return false;
        }

        if ($this->generateChecksumForPostback() != $this->data->checksum) {
            $this->log("Checksum does not match");
            return false;
        }
        return true;
    }

    /**
    * Return the postback data
    * @since version 1.0.0
    * @access public
    * @return object
    */
    public function getPostback(){
        return $this->data;
    }

    /**
    * Check between ICEPAY statuscodes whether the status can be updated.
    * @since version 1.0.0
    * @access public
    * @param string $currentStatus The ICEPAY statuscode of the order before a statuschange
    * @return boolean
    */
    public function canUpdateStatus($currentStatus) {
        if (!isset($this->data->status)){
            $this->log( "Status not set" );
            return false;
        }
        switch ($this->data->status) {
            case Icepay_StatusCode::SUCCESS:       return ($currentStatus == Icepay_StatusCode::OPEN);
            case Icepay_StatusCode::OPEN:          return ($currentStatus == Icepay_StatusCode::OPEN);
            case Icepay_StatusCode::ERROR:         return ($currentStatus == Icepay_StatusCode::OPEN);
            case Icepay_StatusCode::CHARGEBACK:    return ($currentStatus == Icepay_StatusCode::SUCCESS);
            case Icepay_StatusCode::REFUND:        return ($currentStatus == Icepay_StatusCode::SUCCESS);
            default:
                return false;
        };
    }

}

/**
 *  Icepay_Result class
 *  To handle the success and error page
 *  @author Olaf Abbenhuis
 */
class Icepay_Result extends Icepay_Basicmode {

    /**
    * Validate the ICEPAY GET data
    * @since version 1.0.0
    * @access public
    * @return boolean
    */
    public function validate(){
        if ( $_SERVER['REQUEST_METHOD'] != 'GET' ) {
            $this->log("Invalid request method");
            return false;
        }

        //$this->log(sprintf("Page data: %s", serialize($_GET)));
        
        $this->data->status	 	= (isset($_GET['Status']))		?$_GET['Status']		:"";
        $this->data->statusCode 	= (isset($_GET['StatusCode']))		?$_GET['StatusCode']            :"";
        $this->data->merchant 		= (isset($_GET['Merchant']))		?$_GET['Merchant']		:"";
        $this->data->orderID		= (isset($_GET['OrderID']))		?$_GET['OrderID']		:"";
        $this->data->paymentID		= (isset($_GET['PaymentID']))		?$_GET['PaymentID']		:"";
        $this->data->reference		= (isset($_GET['Reference']))		?$_GET['Reference']		:"";
        $this->data->transactionID	= (isset($_GET['TransactionID']))	?$_GET['TransactionID']         :"";
        $this->data->checksum		= (isset($_GET['Checksum']))		?$_GET['Checksum']		:"";

        if ($this->generateChecksumForPage() != $this->data->checksum) {
            $this->log("Checksum does not match");
            return false;
        }

        return true;
    }

    /**
    * Get the ICEPAY status
    * @since version 1.0.0
    * @access public
    * @param boolean $includeStatusCode Add the statuscode message to the returned string for display purposes
    * @return string ICEPAY statuscode (and statuscode message)
    */
    public function getStatus($includeStatusCode = false){
        if (!isset($this->data->status)) return null;
        return ($includeStatusCode)?sprintf("%s: %s",$this->data->status,$this->data->statusCode):$this->data->status;
    }

    /**
    * Return the orderID field
    * @since version 1.0.2
    * @access public
    * @return string
    */
    public function getOrderID(){
        return (isset($this->data->orderID))?$this->data->orderID:null;
    }

    /**
    * Return the result page checksum
    * @since version 1.0.0
    * @access protected
    * @return string SHA1 hash
    */
    protected function generateChecksumForPage(){
        return sha1 (
            sprintf("%s|%s|%s|%s|%s|%s|%s|%s",
            $this->_secretCode,
            $this->data->merchant,
            $this->data->status,
            $this->data->statusCode,
            $this->data->orderID,
            $this->data->paymentID,
            $this->data->reference,
            $this->data->transactionID
            )
        );
    }

    /**
    * Return the get data
    * @since version 1.0.1
    * @access public
    * @return object
    */
    public function getResultData(){
        return $this->data;
    }


}

?>
