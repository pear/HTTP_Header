<?php
/**
 * Test Case for HTTP_Header_Cache
 * 
 * Id$
 */

require_once 'PHPUnit.php';
require_once 'HTTP/Header/Cache.php';

class HTTP_Header_CacheTest extends PHPUnit_TestCase
{
    function HTTP_Header_CacheTest($name)
    {
        $this->PHPUnit_TestCase($name);
    } 

    function setUp()
    {
        $this->testScript = 'http://local/www/mike/pear/HTTP_Header/tests/cacheresponse.php';
    } 

    function tearDown()
    {
    } 

    function testHTTP_Header_Cache()
    {
        $this->assertTrue(is_a(new HTTP_Header_Cache, 'HTTP_Header_Cache'));
    } 

    function testgetCacheStart()
    {
        $c = &new HTTP_Header_Cache;
        $this->assertEquals(time(), $c->getCacheStart());
        $_SERVER['HTTP_IF_MODIFIED_SINCE'] = HTTP::Date(strtotime('yesterday'));
        $this->assertEquals($_SERVER['HTTP_IF_MODIFIED_SINCE'], HTTP::Date($c->getCacheStart()));
        unset($c, $_SERVER['HTTP_IF_MODIFIED_SINCE']);
    } 

    function testisOlderThan()
    {
        $c = &new HTTP_Header_Cache;
        $this->assertFalse($c->isOlderThan(1, 'second'));
        $this->assertFalse($c->isOlderThan(1, 'hour'));
        sleep(2);
        $this->assertTrue($c->isOlderThan(1, 'second'));
        unset($c);
    } 

    function testisCached()
    {
        $c = &new HTTP_Header_Cache;
        $this->assertFalse($c->isCached(), 'no last modified');
        $_SERVER['HTTP_IF_MODIFIED_SINCE'] = HTTP::Date(strtotime('yesterday'));
        $this->assertTrue($c->isCached(), 'last modified header');
        $this->assertFalse($c->isCached(time()), 'last modified header (yesterday) and param (now)');
        $this->assertTrue($c->isCached(strtotime('last sunday')), 'last modified header (yesterday) and param (last sunday)');
        unset($c, $_SERVER['HTTP_IF_MODIFIED_SINCE']);
    } 

    function testexitIfCached()
    {
        require_once 'HTTP/Request.php';
        $r = &new HTTP_Request($this->testScript);
        $r->setMethod(HTTP_REQUEST_METHOD_GET);
        $r->addHeader('If-Modified-Since', HTTP::Date());
        $r->sendRequest();
        $this->assertEquals(304, $r->getResponseCode());
        $r->addHeader('If-Modified-Since', HTTP::Date(strtotime('yesterday')));
        $r->sendRequest();
        $this->assertEquals(200, $r->getResponseCode());
    } 

} 

$suite  = &new PHPUnit_TestSuite('HTTP_Header_CacheTest');
$result = &PHPUnit::run($suite);
echo $result->toString();

?>
