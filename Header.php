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

require_once 'HTTP.php';


// Information Codes

define('HTTP_HEADER_STATUS_100', 'Continue');
define('HTTP_HEADER_STATUS_101', 'Switching Protocols');
define('HTTP_HEADER_STATUS_102', 'Processing');
define('HTTP_HEADER_STATUS_INFORMATIONAL',1);

// Success Codes

define('HTTP_HEADER_STATUS_200', 'OK');
define('HTTP_HEADER_STATUS_201', 'Created');
define('HTTP_HEADER_STATUS_202', 'Accepted');
define('HTTP_HEADER_STATUS_203', 'Non-Authoriative Information');
define('HTTP_HEADER_STATUS_204', 'No Content');
define('HTTP_HEADER_STATUS_205', 'Reset Content');
define('HTTP_HEADER_STATUS_206', 'Partial Content');
define('HTTP_HEADER_STATUS_207', 'Multi-Status');
define('HTTP_HEADER_STATUS_SUCCESSFUL',2);

// Redirection Codes

define('HTTP_HEADER_STATUS_300', 'Multiple Choices');
define('HTTP_HEADER_STATUS_301', 'Moved Permanently');
define('HTTP_HEADER_STATUS_302', 'Found');
define('HTTP_HEADER_STATUS_303', 'See Other');
define('HTTP_HEADER_STATUS_304', 'Not Modified');
define('HTTP_HEADER_STATUS_305', 'Use Proxy');
define('HTTP_HEADER_STATUS_306','(Unused)');
define('HTTP_HEADER_STATUS_307', 'Temporary Redirect');
define('HTTP_HEADER_STATUS_REDIRECT',3);

// Error Codes

define('HTTP_HEADER_STATUS_400', 'Bad Request');
define('HTTP_HEADER_STATUS_401', 'Unauthorized');
define('HTTP_HEADER_STATUS_402', 'Payment Granted');
define('HTTP_HEADER_STATUS_403', 'Forbidden');
define('HTTP_HEADER_STATUS_404', 'File Not Found');
define('HTTP_HEADER_STATUS_405', 'Method Not Allowed');
define('HTTP_HEADER_STATUS_406', 'Not Acceptable');
define('HTTP_HEADER_STATUS_407', 'Proxy Authentication Required');
define('HTTP_HEADER_STATUS_408', 'Request Time-out');
define('HTTP_HEADER_STATUS_409', 'Conflict');
define('HTTP_HEADER_STATUS_410', 'Gone');
define('HTTP_HEADER_STATUS_411', 'Length Required');
define('HTTP_HEADER_STATUS_412', 'Precondition Failed');
define('HTTP_HEADER_STATUS_413', 'Request Entity Too Large');
define('HTTP_HEADER_STATUS_414', 'Request-URI Too Large');
define('HTTP_HEADER_STATUS_415', 'Unsupported Media Type');
define('HTTP_HEADER_STATUS_416', 'Requested range not satisfiable');
define('HTTP_HEADER_STATUS_417', 'Expectation Failed');
define('HTTP_HEADER_STATUS_422', 'Unprocessable Entity');
define('HTTP_HEADER_STATUS_423', 'Locked');
define('HTTP_HEADER_STATUS_424', 'Failed Dependency');
define('HTTP_HEADER_STATUS_CLIENT_ERROR',4);

// Server Errors

define('HTTP_HEADER_STATUS_500','Internal Server Error');
define('HTTP_HEADER_STATUS_501','Not Implemented');
define('HTTP_HEADER_STATUS_502','Bad Gateway');
define('HTTP_HEADER_STATUS_503','Service Unavailable');
define('HTTP_HEADER_STATUS_504','Gateway Timeout');
define('HTTP_HEADER_STATUS_505','HTTP Version Not Supported');
define('HTTP_HEADER_STATUS_507', 'Insufficient Storage');
define('HTTP_HEADER_STATUS_SERVER_ERROR',5);

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
    function setHeader($key, $value=null)
    {
//FIXXXME do sanity checks, i.e. if the headers are valid, etc.
// may be check protocol too (HTTP 1.0/1.1)

        // is the 'last-modified' value a timestamp? if the value is an int then we assume so, or if it is not given
        if(strtolower($key)=='last-modified' && ($value==null || (int)$value==$value)) {
            if ($value == null) {
                $value = time();
            }
            $this->_headers[$key] = HTTP::Date($value);//date('D, d M Y H:i:s T',$value);
        } else {
            $this->_headers[$key] = $value;
        }
    }

    /**
    *
    *
    */
    function getHeader ($key=null)
    {
        if ($key==null) {
            return $this->_headers;
        }
        return $this->_headers[$key];
    }

    /**
    * Send out the header that you set via setHeader(). If the parameter $keys
    * is given only the headers given in there will be sent.
    *
    * @param array the keys that shall be sent, if the array is empty all
    *  the headers will be sent (all headers that you would get vie $this->getHeader())
    */
    function sendHeaders($keys=array())
    {
        foreach ($this->_headers as $key=>$value) {
            if ($keys) {
                if (in_array($key,$keys))  {
                    header("$key: $value");
                }
            } else {
                header("$key: $value");
            }
        }
    }

    /**
    * Send out the given HTTP-Status code.
    * Use this for example when you want to tell the client this page is
    * cached, then you would call sendStatusCode(304), 
    * see HTTP_Header_Cache::exitIfCached() for example usage.
    *
    * @param int the status code to be sent, i.e. 404, 304, 200, etc.
    */
    function sendStatusCode( $code)
    {
        if (defined('HTTP_HEADER_STATUS_' .$code)) {
            $status_msg = constant('HTTP_HEADER_STATUS_' .$code);
            header( 'HTTP/'.$this->_httpVersion. ' ' .$code. ' ' .$status_msg);
        } else {
            return false;
        }
    }

    /**
    *   converts dates like
    *       Mon, 31 Mar 2003 15:26:34 GMT
    *       Tue, 15 Nov 1994 12:45:26 GMT
    *   into a timestamp, strtotime() doesnt do it :-(
    *
    * @param string the data to be converted
    * @return int the unix-timestamp
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
    *   @param  mixed   (1) null (default) - only the session-id will be added, but only
    *                       when trans_sid=1 is set<br>
    *                   (2) false - no paras to add<br>
    *                   (3) true - add the session-id<br>
    *                   (4) array - of parameter names, if the key is a string its assumed
    *                       to be name=>value, otherwise the value is retreived using
    *                       $GLOBALS['paraName']
    */
    function redirect( $url=null, $param=null)
    {
        if ($url===null) {
            $url = $_SERVER['PHP_SELF'];
        }
        if ($param===true || ini_get('session.use_trans_sid')) {
            $param = array( session_name() => session_id() );
        }
        if (is_array($param) && sizeof($param)) {
            $paraString = array();
            foreach ($param as $key=>$aParam) {
                if (!is_string($key)) {
                    $paraString[] = urlencode($aParam).'='.urlencode(@$GLOBALS[$aParam]);
                } else {
                    $paraString[] = urlencode($key).'='.urlencode($aParam);
                }
            }
            // we need to know how to add the additional parameter, either with a & or a ?
            $parsedUrl = parse_url($url);
//FIXXXME this still causes a problem with (inproper) urls like: "http://php.net?" if just http_build_query() was already available :-)
            $url .= (isset($parsedUrl['query'])?'&':'?').implode('&',$paraString);
        }
        parent::redirect($url);
    }

    /**#@+
     * @author Davey Shafik <davey@php.net>
     * @param int $http_code HTTP Code to check
     */

    /**
     * Return HTTP Status Code Type
     *
     * @return int|false
     */

    function getStatusType($http_code) 
    {
        if(defined('HTTP_HEADER_STATUS_' .$http_code)) {
            $type = substr($http_code,0,1);
            switch ($type) {
                case HTTP_HEADER_STATUS_INFORMATIONAL:
                case HTTP_HEADER_STATUS_SUCCESSFUL:
                case HTTP_HEADER_STATUS_REDIRECT:
                case HTTP_HEADER_STATUS_CLIENT_ERROR:
                case HTTP_HEADER_STATUS_SERVER_ERROR:
                    return $type;
                    break;
                default:
                    return false;
                    break;
            }
        } else {
            return false;
        }
    }

    /**
     * Return Status Code Message
     *
     * @return string|false
     */

    function getStatusText($http_code) 
    {
        if ($this->statusType($http_code)) {
            return constant('HTTP_HEADER_STATUS_' .$http_code);
        } else {
            return false;
        }
    }

    /**
     * Checks if HTTP Status code is Information (1xx)
     *
     * @return boolean
     */

    function isInformational($http_code) 
    {
        if ($status_type = $this->statusType($http_code)) {
            return $status_type{0} == HTTP_HEADER_STATUS_INFORMATIONAL;
        } else {
            return false;
        }
    }

    /**
     * Checks if HTTP Status code is Successful (2xx)
     *
     * @return boolean
     */

    function isSuccessful($http_code) 
    {
        if ($status_type = $this->statusType($http_code)) {
            return $status_type{0} == HTTP_HEADER_STATUS_SUCCESSFUL;
        } else {
            return false;
        }
    }

    /**
     * Checks if HTTP Status code is a Redirect (3xx)
     *
     * @return boolean
     */

    function isRedirect($http_code) 
    {
        if ($status_type = $this->statusType($http_code)) {
            return $status_type{0} == HTTP_HEADER_STATUS_REDIRECT;
        } else {
            return false;
        }
    }

    /**
     * Checks if HTTP Status code is a Client Error (4xx)
     *
     * @return boolean
     */

    function isClientError($http_code) 
    {
        if ($status_type = $this->statusType($http_code)) {
            return $status_type{0} == HTTP_HEADER_STATUS_CLIENT_ERROR;
        } else {
            return false;
        }
    }

    /**
     * Checks if HTTP Status code is Server Error (5xx)
     *
     * @return boolean
     */

    function isServerError($http_code) 
    {
        if ($status_type = $this->statusType($http_code)) {
            return $status_type{0} == HTTP_HEADER_STATUS_SERVER_ERROR;
        } else {
            return false;
        }
    }

    /**
     * Checks if HTTP Status code is Server OR Client Error (4xx or 5xx)
     *
     * @return boolean
     */

    function isError($http_code) 
    {
        if ($status_type = $this->statusType($http_code)) {
            return (($status_type == HTTP_HEADER_STATUS_CLIENT_ERROR) || ($status_type == HTTP_HEADER_STATUS_SERVER_ERROR)) ? true : false;
        } else {
            return false;
        }
    }

    /**#@-*/

}
?>
