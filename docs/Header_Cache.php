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

    
    //
    //  this file does not work, it is only an example
    //  to show how i am caching my style sheets, which are dynamically
    //  created
    //  $tpl is an instance of the template engine (i.e. HTML_Template_Xipe as used here)
    //  the CSS-file is actually also a template, which needs to be compiled first
    //

    require_once 'HTTP/Header/Cache.php';


    // compile the template (if needed), so we can check $tpl->compiled()
    // compile() does only compile if there is any change (or forceCompile is true)
    $tpl->compile($layout->getContentTemplate(__FILE__));


    $httpCache = new HTTP_Header_Cache();                                          
    // we are working with a style sheet, so set the content-type
    $httpCache->setHeader( 'Content-Type' , 'text/css' );        

    // if the template had NOT been compiled before, so to say NO change was made
    if( !$tpl->compiled() )                                      
        // then we send a 304-Not modified header here
        // and EXIT the code right here!
        $httpCache->exitIfCached();
        
        
        

    // otherwise we send all the headers
    // in this case we added 'Last-modified' so the UA knows
    // the web-server-time that will be returned for the If-Modified-Since
    $httpCache->sendHeaders();

    // here we finally include the compiled-template
    // this only happens if the template really has changed and
    // we need to send a new file!
    include($tpl->getCompiledTemplate());

?>