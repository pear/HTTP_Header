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

require_once 'HTTP/Header.php';

/**
*
*
*   @package    HTTP_Header
*   @author     Wolfram Kriesing <wolfram@kriesing.de>
*/
class HTTP_Header_Cache extends HTTP_Header
{

    var $_caching = true;

    /**
    *
    *
    *
    *   @param  boolean     shall the
    *   @param  integer     the number of seconds after which the page expires
    *                       0 - is never
    */
    function HTTP_Header_Cache( $caching=true )
    {
        $this->_caching = $caching;

        if ( $this->_caching ) {
            $this->setHeader('Pragma','cache');
            $this->setHeader('Cache-Control','public');
        }
    }

    function getCacheStart()
    {
        return $this->dateToTimestamp($_SERVER['HTTP_IF_MODIFIED_SINCE']);
    }

    /**
    *   you can call it like this
    *   isOlderThan( 2 , 'days' )
    */
    function isOlderThan( $time=0 , $unit='' )
    {
        switch (strtolower($unit)) {
            case 'week':
            case 'weeks':   $time = $time*7;
            case 'day':
            case 'days':    $time = $time*24;
            case 'hour':
            case 'hours':   $time = $time*60;
            case 'minute':
            case 'minutes': $time = $time*60;
            case 'second':  break;
        }

        if ( $time && $this->getCacheStart()+$time < time() ) {
            return true;
        }
        return false;
    }

    /**
    *   optionally you can pass an additional condition via parameter
    *   which is simply checked for true
    *   it would be the same as
    *       $this->isCached() && $condition  ===  $this->isCached($condition)
    *   it is suggested to use the parameter since the handling for sending the
    *   'last-modified' is included in this method here
    *
    *   @author     Wolfram Kriesing <wolfram@kriesing.de>
    *   @param      boolean
    */
    function isCached( $condition=true )
    {
        if ( isset($_SERVER['HTTP_IF_MODIFIED_SINCE']) && $condition==true ) {
            return true;
        }

        // if the file is not cached and 'caching' is on
        // then it shall be cached, and therefore we need to send
        // the 'last-modified' header in order to get a 'if-modified-since'
        // next time, so that we can answer with a 304 in case it is still cached
        if ( $this->_caching ) {
            $this->setHeader( 'Last-Modified' );
        }
        return false;
    }



    /**
    *   this method exits the script if the page is
    *   still cached by the user agent, the condition
    *   if given will be AND-ed to the 'isCached' call
    *   Since it returns false in case the page is not cached you can also use it in an
    *   if. for example:
    *       if (!$httpCache->exitIfCached()) {
    *           do stuff here in case it is not cached
    *       }
    *
    *   @param  boolean     will be AND-ed to the 'isCached' call
    */
    function exitIfCached( $condition=true )
    {
        if ( $this->isCached( $condition ) ) {
            $this->sendHeaders();
            $this->sendStatusCode(304);
            exit;
        }
        return false;
    }




/*  ???? does that make sense??? 'must-revalidate' is one of many possible values for Cache-Control
    we could check HTTP-compilance here, see   http://www.w3.org/Protocols/rfc2616/rfc2616-sec14.html
    function mustRevalidate( $yes=true )
    {
        $this->setHeader('Cache-Control','must-revalidate');
    }
*/
}
?>
