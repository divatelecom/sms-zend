<?php

/**
 *
 * LICENSE
 *
 * This source file is subject to the new BSD license that is bundled
 * with this package in the file LICENSE.txt.
 *
 * It is also available through the world-wide-web at this URL:
 * http://www.divatxt.co.uk/license/new-bsd
 *
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to support@divatxt.co.uk so we can send you a copy immediately.
 *
 * @category   Diva
 * @package    Diva
 * @copyright  Copyright (c) 2013 Diva Telecom Ltd (http://www.divatelecom.co.uk)
 * @license    http://www.divatxt.co.uk/license/new-bsd.php     New BSD License
 * @version    1
 */

/**
 * @see Zend_Rest_Client
 */
require_once 'Zend/Rest/Client.php';


class Diva_Txt
{
        const API_BASE_URI = 'https://secure.divatelecom.co.uk';
        const PATH_MESSAGE = 'API/index.php/V1/message';
        const PATH_ACCOUNT = 'API/index.php/V1/account';


        /**
         * Zend_Service_Rest instance
         *
         * @var Zend_Service_Rest
         */
        protected $_rest;


        /**
         * Username
         *
         * @var string
         */
        protected $_authUname;

        /**
         * Password
         *
         * @var string
         */
        protected $_authPass;


        /**
         * Delivery Handler
         *
         * @var string
         */
        protected $_handler;

        /**
         * Last request status (HTTP Response code)
         *
         * @var int
         */
        protected $_status;

        /**
         * Constructs a new DivaTxt Web Services Client
         *
         * @param  string $uname Client username
         * @param  string $pass  Client password
         * @return void
         */
        public function __construct($uname = null, $pass = null)
        {
                $this->_rest = new Zend_Rest_Client(self::API_BASE_URI);
                $this->_rest->getHttpClient()->setHeaders('Accept', 'application/json');
                $this->setAuth($uname, $pass);
        }


        /**
         * Set client username and password
         *
         * @param  string $uname Client user name
         * @param  string $pass  Client password
         * @return Diva_Txt Provides a fluent interface
         */
        public function setAuth($uname, $pass)
        {
                $this->_authUname = $uname;
                $this->_authPass  = $pass;

                return $this;
        }
        

        /**
         * Set the URL Diva will POST delivery notifications to - leave empty to disable
         *
         * @param string $handlerURL publiccally accessible URL to receive notifications
         * @return Diva_Txt Provides a fluent interface 
         */
        public function setHandler($handlerURL)
        {
                $this->_handler = $handlerURL;
                return $this;
        }


        /**
         * Get the last HTTP Status response code 
         *
         * @return int 0 if the HTTP request failed
         */
         public function getStatus()
         {
                return $this->_status;
         }


        /**
         * Send the request to the platform 
         *      
         * @param       string $path URL Path for method
         * @param       string $type Type - either GET or POST
         * @param       array $params Array 
         * @return      array decoded reponse from web service
         * @throws      Diva_Exception,Zend_Http_Client_Adapter_Exception 
         */ 
        public function makeRequest($path, $type='GET', array $params = array())
        {
                $response = null; 
                if($this->_authUname == null || $this->_authPass == null)
                {
                        require_once 'Diva/Exception.php';
                        throw new Diva_Exception('User credentials not set');
                }
                        
                $this->_rest->getHttpClient()->setAuth($this->_authUname, $this->_authPass);
                $this->_status = 0;
                //This block may throw a Zend_Http_Client_Adapter_Exception if there is network problem reaching the API URL
                if($type=='POST')
                {
                        $response = $this->_rest->restPost($path, $params);
                }
                else
                {
                        $response = $this->_rest->restGet($path, $params);              
                }


                if ($response!=null)
                {
                        $this->_status = $response->getStatus();
                        if ($response->isSuccessful()) {
                                $responseBody = $response->getBody();
                                return Zend_Json_Decoder::decode($responseBody);
                        }
                        else
                        {
                                //Rest error e.g. unauthorised if user/pass wrong, error message and body will give details
                                require_once 'Diva/Exception.php';
                                throw new Diva_Exception('REST Error - Status :'.$response->getStatus().' Msg : '.$response->getMessage().' - '.$response->getBody());
                        }
                }


        }

        
        /**
         * Get the credit balance of the account assoicated with this API user 
         *      
         * @return      float   Balance of the account in credits, positive = pre-pay, negative = post-pay
         * @throws      Diva_Exception, Zend_Http_Client_Adapter_Exception
         */
        public function getBalance()
        {
                $data = $this->makeRequest(self::PATH_ACCOUNT,'GET');
                return $data['credits'];        
        }


        /**
         * Send an SMS message
         * 
         * @param       string/array  $mobile The mobile(s) to send the message to, either a single string of comma seperated, or an array of strings 
         * @param       string  $message The message to send, this is currently limited to 608 characters 
         * @param       string  $subject This is the name or number you want the message to appear to have come from 11 alphanumeric characters
         * @param       string  $param1 Optional - This is a reference number you can define, so you can marry up the message in delivery reports
         * @param       string  $param2 Optional - This is a second user defined reference
         * @return      int     DivaTxt batchID (is returned in delivery handler along with param1 & param2)
         * @throws      Diva_Exception, Zend_Http_Client_Adapter_Exception
         */
        public function sendMessage($mobiles=null,$message=null,$subject='SMS',$param1=null,$param2=null)
        {
                $mobile = '';
                if(is_array($mobiles))
                {
                        $mobile = implode(',',$mobiles);
                }
                else
                {
                        $mobile=$mobiles;
                }
                $params=array(
                        "handler" => $this->_handler,
                        "mobile" => $mobile,
                        "message" => $message,
                        "subject" => $subject,
                        "param1" => $param1,
                        "param2" => $param2
                );
                $data = $this->makeRequest(self::PATH_MESSAGE,'POST',$params);
                return $data['batch'];
        }



        /**
         * Query status of an SMS 
         *
         * @param       batch $batch The batch ID recieved when the SMS was submitted
         * @param       mobile $mobile Optional mobile number within this batch if the message was sent to multiple recipients
         * @return      array of Diva_Reports
         */
         public function queryMessage($batch=null,$mobile=null)
         {
                require_once 'Diva/Report.php';
                $reports = array();
                if($batch != null)
                {
                        $params=array(
                                "batch" => $batch,
                                "mobile" => $mobile
                        );
                        $data = $this->makeRequest(self::PATH_MESSAGE,'GET',$params);
                        $param1 = $data['param1'];
                        $param2 = $data['param2'];
                        $reports_arr = $data['reports'];
                        foreach($reports_arr as $report_arr)
                        {
                                $report = new Diva_Report();
                                $report->batch = $batch;
                                $report->param1 = $param1;
                                $report->param2 = $param2;
                                foreach($report_arr as $key=>$val)
                                {
                                        $report->$key = $val;
                                }
                                $reports[]=$report;
                        }
                }
                return $reports;
         }
}




?>
