<?php
// +----------------------------------------------------------------------+
// | PEAR :: HTTP :: Header :: Cache                                      |
// +----------------------------------------------------------------------+
// | This source file is subject to version 3.0 of the PHP license,       |
// | that is available at http://www.php.net/license/3_0.txt              |
// | If you did not receive a copy of the PHP license and are unable      |
// | to obtain it through the world-wide-web, please send a note to       |
// | license@php.net so we can mail you a copy immediately.               |
// +----------------------------------------------------------------------+
// | Authors: Wolfram Kriesing <wk@visionp.de>                            |
// |          Michael Wallner <mike@php.net>                              |
// +----------------------------------------------------------------------+
//
// $Id$

require_once 'HTTP/Header.php';

/**
 * HTTP_Header_Cache
 * 
 * This package provides methods to easier handle caching of HTTP pages.  That 
 * means that the pages can be cached at the client (user agent or browser) and 
 * your application only needs to send "hey client you already have the pages".
 * 
 * Which is done by sending the HTTP-Status "304 Not Modified", so that your
 * application load and the network traffic can be reduced, since you only need
 * to send the complete page once.  This is really an advantage e.g. for 
 * generated style sheets, or simply pages that do only change rarely.
 * 
 * Usage:
 * <code>
 *  require_once 'HTTP/Header/Cache.php';
 *  $httpCache = new HTTP_Header_Cache(4, 'weeks');
 *  $httpCache->sendHeaders();
 *  // your code goes here
 * </code>
 * 
 * @package     HTTP_Header
 * @category    HTTP
 * @license     PHP License
 * @access      public
 * @version     $Revision$
 */
class HTTP_Header_Cache extends HTTP_Header
{
    /**
     * Constructor
     * 
     * Set the amount of time to cache.
     * 
     * @access  public
     * @return  object  HTTP_Header_Cache
     * @param   int     $expires 
     * @param   string  $unit
     */
    function HTTP_Header_Cache($expires = 0, $unit = 'seconds')
    {
        parent::HTTP_Header();
        $this->setHeader('Pragma', 'cache');
        $this->setHeader('Last-Modified', $this->getCacheStart());
        
        if (isset($_SESSION)) {
            $this->setHeader('ETag', '"'. session_id() .'"');
            $this->setHeader('Cache-Control', 'private');
        } else {
            $this->setHeader('Cache-Control', 'public');
        }
        
        if ($expires && !$this->isOlderThan($expires, $unit)) {
            $this->exitIfCached();
        }
    }

    /**
     * Get Cache Start
     * 
     * Returns the unix timestamp of the If-Modified-Since HTTP header or the
     * current time if the header was not sent by the client.
     * 
     * @access  public
     * @return  int     unix timestamp
     */
    function getCacheStart()
    {
        if (isset($_SERVER['HTTP_IF_MODIFIED_SINCE'])) {
            return strtotime($_SERVER['HTTP_IF_MODIFIED_SINCE']);
        }
        return time();
    }

    /**
     * Is Older Than
     * 
     * You can call it like this:
     * <code>
     *  $httpCache->isOlderThan(1, 'day');
     *  $httpCache->isOlderThan(47, 'days');
     * 
     *  $httpCache->isOlderThan(1, 'week');
     *  $httpCache->isOlderThan(3, 'weeks');
     * 
     *  $httpCache->isOlderThan(1, 'hour');
     *  $httpCache->isOlderThan(5, 'hours');
     * 
     *  $httpCache->isOlderThan(1, 'minute');
     *  $httpCache->isOlderThan(15, 'minutes');
     * 
     *  $httpCache->isOlderThan(1, 'second');
     *  $httpCache->isOlderThan(15);
     * </code>
     * 
     * If you specify something greater than "weeks" as time untit, it just 
     * works approximatly, because a month is taken to consist of 4.3 weeks.
     * 
     * @access  public
     * @return  bool    Returns true if requested page is older than specified.
     * @param   int     $time The amount of time.
     * @param   string  $unit The unit of the time amount - (year[s], month[s], 
     *                  week[s], day[s], hour[s], minute[s], second[s]).
     */
    function isOlderThan($time = 0, $unit = 'seconds')
    {
        static $cacheStart;
        
        if (!$time) {
            return false;
        }
        
        if (!isset($cacheStart)) {
            $cacheStart = $this->getCacheStart();
        }
        
        switch (strtolower($unit))
        {
            case 'year':
            case 'years':
                $time *= 12;
            case 'month':
            case 'months':
                $time *= 4.3;
            case 'week':
            case 'weeks':
                $time *= 7;
            case 'day':
            case 'days':
                $time *= 24;
            case 'hour':
            case 'hours':
                $time *= 60;
            case 'minute':
            case 'minutes':
                $time *= 60;
            default:
                $time += $cacheStart;
        }
        
        return $time < time();
    }

    /**
     * Is Cached
     * 
     * @access  public
     * @return  bool    Whether the page/resource is considered to be cached.
     * @param   int     $lastModified Unix timestamp of last modification.
     */
    function isCached($lastModified = 0)
    {
        if (    isset($_SESSION, $_SERVER['HTTP_IF_NONE_MATCH']) &&
                '"'. session_id() .'"' != $_SERVER['HTTP_IF_NONE_MATCH']) {
            return false;
        }
        if (!isset($_SERVER['HTTP_IF_MODIFIED_SINCE'])) {
            return false;
        }
        if (!$lastModified) {
            return true;
        }
        return $lastModified < $this->getCacheStart();
    }
    
    /**
     * Exit If Cached
     * 
     * @access  public
     * @return  void
     */
    function exitIfCached()
    {
        if ($this->isCached()) {
            $this->sendHeaders();
            $this->sendStatusCode(304);
            exit;
        }
    }
    
    /**
     * Set Last Modified
     * 
     * @access  public
     * @return  void
     * @param   int     $lastModified The unix timestamp of last modification.
     */
    function setLastModified($lastModified = null)
    {
        $this->setHeader('Last-Modified', $lastModified);
    }
}
?>
