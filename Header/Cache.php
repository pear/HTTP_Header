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
* This package provides methods to easier handle caching of HTTP pages.
* That means that the pages can be cached at the client (User agent or browser)
* and your application only needs to send "hey client you already have the pages".
* Which is dont by sending the HTTP-Status 304 ('Not Modified'), so that your
* application load can be reduced and the net traffic too, since you only need
* to send the complete page once. This is really an advantage i.e. for generated
* style sheet, or simply pages that do only change rarely. 
* 
* I.e. when you dont want to send a client-side-cached page multiple times
* you can do this:
* <code>
* $httpCache = new HTTP_Header_Cache();                                          
* // if the page is cached, then we send a 304-Not modified header here and EXIT the code right here!
* // if not this method sets all the headers so that the page gets cached
* $httpCache->exitIfCached();
*
* ...do the work that renders the real page, that shall be cached by the client...
* </code>
*
* Or when you know that the page shall only be cached for some time:
* <code>
* $httpCache = new HTTP_Header_Cache();
* // check if the page the client has is older than 2 days
* if ($httpCache->isOlderThan(2,'days')) {
*     $httpCache->sendHeaders(); // make sure that the headers, that tell this page shall be cached get sent     
*     ...generate and send all the cacheable content to the client...
* } else {
*     $httpCache->exitIfCached();
* }
*
* </code>
*
* @package HTTP_Header
* @author Wolfram Kriesing <wolfram@kriesing.de>
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
    function HTTP_Header_Cache($caching=true)
    {
        $this->_caching = $caching;
        if ($this->_caching) {
            $this->setHeader('Pragma','cache');
            $this->setHeader('Cache-Control','public');
        }
    }

    function getCacheStart()
    {
        return $this->dateToTimestamp($_SERVER['HTTP_IF_MODIFIED_SINCE']);
    }

    /**
    * You can call it like this:
    * <code>
    *   $httpCache->isOlderThan(1,'day');
    *   $httpCache->isOlderThan(47,'days');
    *
    *   $httpCache->isOlderThan(1,'week');
    *   $httpCache->isOlderThan(3,'weeks');
    *
    *   $httpCache->isOlderThan(1,'hour');
    *   $httpCache->isOlderThan(5,'hours');
    *
    *   $httpCache->isOlderThan(1,'minute');
    *   $httpCache->isOlderThan(15,'minutes');
    *
    *   $httpCache->isOlderThan(1,'second');
    *   $httpCache->isOlderThan(15);    // is the same as isOlderThan(15,'seconds')
    * </code>
    *
    * @param integer the number of units, if no second paramter given it means seconds
    * @param string the unit, 
    *  can be one of: 'week', 'weeks', 'day', 'days', 'hour', 'hours', 'minute', 'minutes', 'second'
    * @return boolean true if it is older than what the parameters say
    */
    function isOlderThan($time=0, $unit='')
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
        if ($time && $this->getCacheStart()+$time<time()) {
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
        if ($this->isCached($condition)) {
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
