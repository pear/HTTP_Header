<?php
/**
 * Test Case for HTTP_Header2::Cache
 *
 * Id$
 */

require_once 'PHPUnit/Framework/TestCase.php';
require_once 'HTTP/Header2/Cache.php';
require_once 'HTTP/Request2.php';

class HTTP_Header2::CacheTest extends PHPUnit_Framework_TestCase
{
    function setUp()
    {
        $this->testScript = 'http://local/www/mike/pear/HTTP_Header2/tests/cacheresponse.php';
    }

    function testHTTP_Header2::Cache()
    {
        $this->assertTrue(is_a(new HTTP_Header2::Cache, 'HTTP_Header2::Cache'));
    }

    function testgetCacheStart()
    {
        $c = new HTTP_Header2::Cache;
        $this->assertEquals(time(), $c->getCacheStart());
        $_SERVER['HTTP_IF_MODIFIED_SINCE'] = HTTP::Date(strtotime('yesterday'));
        $this->assertEquals($_SERVER['HTTP_IF_MODIFIED_SINCE'], HTTP::Date($c->getCacheStart()));
        unset($c, $_SERVER['HTTP_IF_MODIFIED_SINCE']);
    }

    function testisOlderThan()
    {
        $c = new HTTP_Header2::Cache;
        $this->assertTrue($c->isOlderThan(1, 'second'));
        $this->assertTrue($c->isOlderThan(1, 'hour'));
        $_SERVER['HTTP_IF_MODIFIED_SINCE'] = HTTP::Date(time() - 3);
        $this->assertTrue($c->isOlderThan(1, 'second'));
        unset($c, $_SERVER['HTTP_IF_MODIFIED_SINCE']);
    }

    function testisCached()
    {
        $c = new HTTP_Header2::Cache;
        $this->assertFalse($c->isCached(), 'no last modified');
        $_SERVER['HTTP_IF_MODIFIED_SINCE'] = HTTP::Date(strtotime('yesterday'));
        $this->assertTrue($c->isCached(), 'last modified header');
        $this->assertFalse($c->isCached(time()), 'last modified header (yesterday) and param (now)');
        $this->assertTrue($c->isCached(strtotime('last year')), 'last modified header (yesterday) and param (last year)');
        unset($c, $_SERVER['HTTP_IF_MODIFIED_SINCE']);
    }

    function testexitIfCached()
    {
        $r = new HTTP_Request2($this->testScript);
        $r->setMethod(HTTP_Request2::METHOD_GET);
        $r->setHeader('If-Modified-Since', HTTP::Date());
        $response = $r->send();
        $this->assertEquals(304, $response->getStatus(), 'HTTP 304 Not Modified');
        $r->setHeader('If-Modified-Since', HTTP::Date(strtotime('yesterday')));
        $r->send();
        $this->assertEquals(200, $response->getStatus(), 'HTTP 200 Ok');
        unset($r);
    }

    function testget()
    {
        $r = new HTTP_Request2($this->testScript);
        $r->setMethod(HTTP_Request2::METHOD_GET);
        $response = $r->send();
        $this->assertEquals(200, $response->getStatus(), 'HTTP 200 Ok (simple plain GET)');
        $r->setHeader('If-Modified-Since', $response->getHeader('last-modified'));
        sleep(3);
        $response = $r->send();
        $this->assertEquals(304, $response->getStatus(), 'HTTP 304 Not Modified (GET with If-Modified-Since set to Last-Modified of prior request');
        unset($r);
    }

    function testhead()
    {
        $r = new HTTP_Request2($this->testScript);
        $r->setMethod(HTTP_Request2::METHOD_HEAD);
        $response = $r->send();
        $this->assertEquals(200, $response->getStatus(), 'HTTP 200 Ok (simple plain GET)');
        $r->setHeader('If-Modified-Since', $response->getHeader('last-modified'));
        sleep(3);
        $response = $r->send();
        $this->assertEquals(304, $response->getStatus(), 'HTTP 304 Not Modified (GET with If-Modified-Since set to Last-Modified of prior request');
        unset($r);
    }

    function testpost()
    {
        $r = new HTTP_Request2($this->testScript);
        $r->setMethod(HTTP_Request2::METHOD_GET);
        $response = $r->send();
        $lm = $response->getHeader('last-modified');
        $r->setMethod(HTTP_Request2::METHOD_POST);
        $response = $r->send();
        $this->assertEquals(200, $response->getStatus(), 'HTTP 200 Ok (POST without If-Modified-Since)');
        $r->setHeader('If-Modified-Since', HTTP::Date(strtotime('yesterday')));
        $response = $r->send();
        $this->assertEquals(200, $response->getStatus(), 'HTTP 200 Ok (POST with If-Modified-Since == yesterday)');
        $r->setHeader('If-Modified-Since', HTTP::Date(time() - 3));
        $response = $r->send();
        $this->assertEquals(200, $response->getStatus(), 'HTTP 200 Ok (POST with If-Modified-Since == now)');
        $r->setHeader('If-Modified-Since', HTTP::Date($lm));
        sleep(3);
        $response = $r->send();
        $this->assertEquals(200, $response->getStatus(), 'HTTP 200 Ok (POST with If-Modified-Since == Last-Modified)');
        $this->assertEquals(HTTP::Date(), $response->getHeader('last-modified'), 'POST time() == Last-Modified');
        unset($r);
    }
}
