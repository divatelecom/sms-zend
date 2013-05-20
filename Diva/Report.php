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

class Diva_Report 
{

        public $batch;
        public $mobile;
        public $param1;
        public $param2;
        public $code;
        public $time;
        public $credits;
        public $status;

        public function __construct($batch=null,$mobile=null,$param1=null,$param2=null,$code=null,$time=null,$credits=null,$status=null)
        {
                $this->batch   = $batch;
                $this->mobile  = $mobile;
                $this->param1  = $param1;
                $this->param2  = $param2;
                $this->code    = $code;
                $this->time    = $time;
                $this->credits = $credits;
                $this->status  = $status;
        }

}


?>
