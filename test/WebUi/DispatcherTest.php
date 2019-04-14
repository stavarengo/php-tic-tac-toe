<?php
/** @noinspection PhpUnhandledExceptionInspection */
declare(strict_types=1);

namespace TicTacToe\Test\WebUi;

use PHPUnit\Framework\TestCase;
use TicTacToe\Api\Storage\ArrayStorage;
use TicTacToe\WebUi\Dispatcher;

class DispatcherTest extends TestCase
{
    public function testSetMethod()
    {
        $storage = new ArrayStorage();
        $routes = [
            '/' => true,
            'invalid-route' => false,
            '' => false,
        ];

        foreach ($routes as $route => $isRouteValid) {
            $failMessage = sprintf('Testing with route "%s".', $route);
            $dispatcherResponse = (new Dispatcher('/'))->dispatch($route, $storage);

            if ($isRouteValid) {
                $this->assertNotNull($dispatcherResponse, $failMessage);

                $this->assertEquals(200, $dispatcherResponse->getStatusCode(), $failMessage);
                $this->assertArrayHasKey('Content-Type', $dispatcherResponse->getHeaders(), $failMessage);
                $this->assertEquals('text/html; charset=UTF-8', $dispatcherResponse->getHeaders()['Content-Type'],
                    $failMessage);
            } else {
                $this->assertNull($dispatcherResponse, $failMessage);
            }
        }
    }

    public function testGetError500Response()
    {
        $dispatcherResponse = Dispatcher::getError500Response('/', new \Exception());

        $this->assertNotNull($dispatcherResponse);
        $this->assertEquals(500, $dispatcherResponse->getStatusCode());
        $this->assertArrayHasKey('Content-Type', $dispatcherResponse->getHeaders());
        $this->assertEquals('text/html; charset=UTF-8', $dispatcherResponse->getHeaders()['Content-Type']);
    }

    public function testGetError404Response()
    {
        $dispatcherResponse = Dispatcher::getError404Response('/');

        $this->assertNotNull($dispatcherResponse);
        $this->assertEquals(404, $dispatcherResponse->getStatusCode());
        $this->assertArrayHasKey('Content-Type', $dispatcherResponse->getHeaders());
        $this->assertEquals('text/html; charset=UTF-8', $dispatcherResponse->getHeaders()['Content-Type']);
    }
}
