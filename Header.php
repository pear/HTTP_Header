<?php
// +----------------------------------------------------------------------+
// | PHP version 4.0                                                      |
// +----------------------------------------------------------------------+
// | Copyright (c) 1997, 1998, 1999, 2000, 2001, 2002, 2003 The PHP Group |
// +----------------------------------------------------------------------+
// | This source file is subject to version 2.0 of the PHP license,       |
// | that is bundled with this package in the file LICENSE, and is        |
// | available at through the world-wide-web at                           |
// | http://www.php.net/license/2_02.txt.                                 |
// | If you did not receive a copy of the PHP license and are unable to   |
// | obtain it through the world-wide-web, please send a note to          |
// | license@php.net so we can mail you a copy immediately.               |
// +----------------------------------------------------------------------+
// | Authors: Wolfram Kriesing <wk@visionp.de>                            |
// |                                                                      |
// +----------------------------------------------------------------------+//
// $Id$

require_once 'PEAR.php';
require_once 'HTTP.php';
                          

// Information Codes

define('HTTP_HEADER_STATUS_100', '100 Continue');
define('HTTP_HEADER_STATUS_101', '101 Switching Protocols');
define('HTTP_HEADER_STATUS_102', '102 Processing');

// Success Codes

define('HTTP_HEADER_STATUS_200', '200 OK'); 
define('HTTP_HEADER_STATUS_201', '201 Created'); 
define('HTTP_HEADER_STATUS_202', '202 Accepted'); 
define('HTTP_HEADER_STATUS_203', '203 Non-Authoriative Information'); 
define('HTTP_HEADER_STATUS_204', '204 No Content'); 
define('HTTP_HEADER_STATUS_205', '205 Reset Content'); 
define('HTTP_HEADER_STATUS_206', '206 Partial Content'); 
define('HTTP_HEADER_STATUS_207', '207 Multi-Status');

// Redirection Codes

define('HTTP_HEADER_STATUS_300', '300 Multiple Choices'); 
define('HTTP_HEADER_STATUS_301', '301 Moved Permanently'); 
define('HTTP_HEADER_STATUS_302', '302 Found'); 
define('HTTP_HEADER_STATUS_303', '303 See Other'); 
define('HTTP_HEADER_STATUS_304', '304 Not Modified'); 
define('HTTP_HEADER_STATUS_305', '305 Use Proxy'); 
define('HTTP_HEADER_STATUS_307', '307 Temporary Redirect');

// Error Codes

define('HTTP_HEADER_STATUS_400', '400 Bad Request'); 
define('HTTP_HEADER_STATUS_401', '401 Unauthorized'); 
define('HTTP_HEADER_STATUS_402', '402 Payment Granted'); 
define('HTTP_HEADER_STATUS_403', '403 Forbidden'); 
define('HTTP_HEADER_STATUS_404', '404 File Not Found'); 
define('HTTP_HEADER_STATUS_405', '405 Method Not Allowed'); 
define('HTTP_HEADER_STATUS_406', '406 Not Acceptable'); 
define('HTTP_HEADER_STATUS_407', '407 Proxy Authentication Required'); 
define('HTTP_HEADER_STATUS_408', '408 Request Time-out'); 
define('HTTP_HEADER_STATUS_409', '409 Conflict'); 
define('HTTP_HEADER_STATUS_410', '410 Gone'); 
define('HTTP_HEADER_STATUS_411', '411 Length Required'); 
define('HTTP_HEADER_STATUS_412', '412 Precondition Failed'); 
define('HTTP_HEADER_STATUS_413', '413 Request Entity Too Large'); 
define('HTTP_HEADER_STATUS_414', '414 Request-URI Too Large'); 
define('HTTP_HEADER_STATUS_415', '415 Unsupported Media Type'); 
define('HTTP_HEADER_STATUS_416', '416 Requested range not satisfiable'); 
define('HTTP_HEADER_STATUS_417', '417 Expectation Failed'); 
define('HTTP_HEADER_STATUS_422', '422 Unprocessable Entity');

define('HTTP_HEADER_STATUS_423', '423 Locked'); 
define('HTTP_HEADER_STATUS_424', '424 Failed Dependency');

// Server Errors

define('HTTP_HEADER_STATUS_500', '500 Internal Server Error');
define('HTTP_HEADER_STATUS_501', '501 Not Implemented');
define('HTTP_HEADER_STATUS_502', '502 Overloaded');
define('HTTP_HEADER_STATUS_503', '503 Gateway Timeout');
define('HTTP_HEADER_STATUS_505', '505 HTTP Version not supported');
define('HTTP_HEADER_STATUS_507', '507 Insufficient Storage');

/**
*
*/
class HTTP_Header extends HTTP
{
                                                  
    /**
    *   the values that are set as default, are the same as PHP sends by default
    *
    *   @var    array   the headers that will be sent
    */
    var $_headers = array(
                            'Content-Type'  =>  'text/html',
                            'Pragma'        =>  'no-cache',
                            'Cache-Control' =>  'no-store, no-cache, must-revalidate, post-check=0, pre-check=0'
                        );

//FIXXXME read the version and use it properly, see Cache
    var $_httpVersion = '1.0';

    /**
    *   set a header value
    *
    *   default values:
    *       last-modified       the current date and time
    *
    *   @param
    *   @param  mixed   if the value is not given the default value depends on the $key
    */
    function setHeader ( $key , $value=null )
    {
//FIXXXME do sanity checks, i.e. if the headers are valid, etc.
// may be check protocol too (HTTP 1.0/1.1)

        // is the 'last-modified' value a timestamp? if the value is an int then we assume so, or if it is not given
        if( strtolower($key) == 'last-modified' && ( $value == null || (int)$value==$value  )   ) {
            if( $value == null )
                $value = time();
            $this->_headers[$key] = HTTP::Date($value);//date('D, d M Y H:i:s T',$value);
        }
        else {
            $this->_headers[$key] = $value;
        }
    }

    /**
    *
    *
    */
    function getHeader ( $key=null )
    {
        if( $key==null )
            return $this->_headers;

        return $this->_headers[$key];
    }

    /**
    *
    *   @param  array   the keys that shall be sent, if the array is empty all
    *                   the headers will be sent (all headers that you would get vie $this->getHeader())
    */
    function sendHeaders ( $keys=array() )
    {
        foreach ( $this->_headers as $key=>$value ) {
            header( "$key: $value" );
        }
    }
                          
    /**
    *
    *
    */
    function sendStatusCode ( $code )
    {
        if ( is_int($code) ) {
            // if the $code is an int we get the constant here
            // is there an easier way to build a constant dynamically?
            eval("\$code = HTTP_HEADER_STATUS_".$code.';');
        }

        header( 'HTTP/'.$this->_httpVersion.' '.$code );
    }
                                   
    /**
    *   converts dates like
    *       Mon, 31 Mar 2003 15:26:34 GMT
    *       Tue, 15 Nov 1994 12:45:26 GMT
    *   into a timestamp, strtotime doesnt do it :-(
    */
    function dateToTimestamp($date)
    {                                      
        $months = array_flip(array('Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'));

// this was converted, i dont know why    January, 17-Fri-03 14:49:43 GMT   
//        preg_match( '~([^,]*),\s(\d+)-...-(\d+)\s(\d+):(\d+):(\d+).*~' , $date , $splitDate );
        
        // this returns: 
        // for  Mon, 31 Mar 2003 15:42:55 GMT
        //  Array ( [0] => Mon, 31 Mar 2003 15:42:55 GMT 
        //          [1] => 31 [2] => Mar [3] => 2003 [4] => 15 [5] => 42 [6] => 55 )
        preg_match('~[^,]*,\s(\d+)\s(\w+)\s(\d+)\s(\d+):(\d+):(\d+).*~',$date,$splitDate);
//        $splitDate[1] = substr($splitDate[1],0,3);
        $timestamp = mktime($splitDate[4],$splitDate[5],$splitDate[6],
                            $months[$splitDate[2]]+1,$splitDate[1],$splitDate[3]);
        
        return $timestamp;
//        $dateTime = new I18N_DateTime('de');
//print $dateTime->format($timestamp);
/*
        $dateTime = new I18N_DateTime('de');
print $_SERVER['HTTP_IF_MODIFIED_SINCE'].'<br>';
print strtotime($_SERVER['HTTP_IF_MODIFIED_SINCE']).'<br>';
        print $dateTime->format(strtotime($_SERVER['HTTP_IF_MODIFIED_SINCE']));
*/
    }

    /**
    *   This function redirects the client. This is done by issuing
    *   a Location: header and exiting.
    *   Additionally to HTTP::redirect you can also add parameters to the url.
    *   If you dont need parameters to be added, simply use
    *       HTTP::redirect
    *   otherwise
    *       HTTP_Header::redirect
    *       
    *   @see    HTTP::redirect()
    *   @author Wolfram Kriesing  <wk@visionp.de>
    *   @param  string $url URL where the redirect should go to
    *                       if none is given it redirects to the current page
    *   @param  mixed   (1) true (default) - only the session-id will be added, this is very useful
    *                       when using trans_sid<br>
    *                   (2) false - no paras to add<br>
    *                   (3) array - of parameter names, if the key is a string its assumed
    *                       to be name=>value, otherwise the value is retreived using
    *                       $GLOBALS['paraName']
    */
    function redirect($url=null,$param=true)
    {
        if ($url===null) {
            $url = $_SERVER['PHP_SELF'];
        }

        // true means add the session id only
        if ($param === true) {
            $param = array( session_name() => session_id() );
        }
        // add some other vars
        if(is_array($param) && sizeof($param)) {
            $paraString = array();
            foreach ($param as $key=>$aParam) {
                if (!is_string($key)) {
                    $paraString[] = urlencode($aParam).'='.urlencode(@$GLOBALS[$aParam]);
                } else {
                    $paraString[] = urlencode($key).'='.urlencode($aParam);
                }
            }
            $url .= '?'.implode('&',$paraString);
        }

        parent::redirect( $url );

    }


}
?>
