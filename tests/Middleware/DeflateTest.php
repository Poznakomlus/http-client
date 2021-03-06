<?php
declare(strict_types = 1);

namespace Zelenin\HttpClient\Test\Middleware;

use PHPUnit_Framework_TestCase;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Zelenin\HttpClient\Middleware\Deflate;
use Zelenin\HttpClient\Psr7\DiactorosPsr7Factory;
use Zelenin\HttpClient\RequestConfig;
use Zelenin\HttpClient\Transport\CurlTransport;
use Zend\Diactoros\Request;
use Zend\Diactoros\Uri;

final class DeflateTest extends PHPUnit_Framework_TestCase
{
    public function testDeflate()
    {
        $factory = new DiactorosPsr7Factory();

        $middleware = new Deflate($factory);

        $request = new Request(new Uri('https://example.com/'), 'GET', 'php://temp', [
            'Accept-Encoding' => 'gzip'
        ]);

        $response = (new CurlTransport(new RequestConfig(), $factory))->send($request);

        $this->assertEquals('gzip', $response->getHeader('Content-Encoding')[0]);
        $this->assertNotContains('Example Domain', $response->getBody()->getContents());

        $middleware($request, $response, function (
            RequestInterface $request,
            ResponseInterface $response,
            callable $next = null
        ) {
            $this->assertContains('Example Domain', $response->getBody()->getContents());

            return $response;
        });
    }
}
