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
                                     


define( HTTP_HEADER_STATUS_304 , '304 Not modified' );



/**
*
*/
class HTTP_Header extends PEAR
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
    function setHeader( $key , $value=null )
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
    function getHeader( $key=null )
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
    function sendHeaders( $keys=array() )
    {
        foreach( $this->_headers as $key=>$value ) {
            header( "$key: $value" );
        }
    }

    function sendStatusCode( $code )
    {
        if( is_int($code) ) {
            // if the $code is an int we get the constant here
            // is there an easier way to build a constant dynamically?
            eval("\$code = HTTP_HEADER_STATUS_".$code.';');
        }

        header( 'HTTP/'.$this->_httpVersion.' '.$code );
    }



}
?>
