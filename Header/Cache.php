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

        if( $this->_caching ) {
            $this->setHeader('Pragma','cache');
            $this->setHeader('Cache-Control','public');
        }
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
        if( isset($_SERVER['HTTP_IF_MODIFIED_SINCE']) && $condition==true ) {
            return true;
        }

        // if the file is not cached and 'caching' is on
        // then it shall be cached, and therefore we need to send
        // the 'last-modified' header in order to get a 'if-modified-since'
        // next time, so that we can answer with a 304 in case it is still cached
        if( $this->_caching ) {
            $this->setHeader( 'Last-Modified' );
        }
        return false;
    }




    function exitIfCached( $condition )
    {
        if( $this->isCached( $condition ) ) {
            $this->sendHeaders();
            $this->sendStatusCode(304);
            exit;
        }
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
