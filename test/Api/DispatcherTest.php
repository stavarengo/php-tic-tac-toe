<?php
/** @noinspection PhpUnhandledExceptionInspection */
declare(strict_types=1);

namespace TicTacToe\Test\Api;

use PHPUnit\Framework\TestCase;
use TicTacToe\Api\Dispatcher;
use TicTacToe\Api\Storage\ArrayStorage;

class DispatcherTest extends TestCase
{
    public function testSetMethod()
    {
        $storage = new ArrayStorage();

        $httpMethods = [
            'GET' => true,
            'POST' => true,
            'PUT' => true,
            'DELETE' => true,
            'HEAD' => false,
            'CONNECT' => false,
            'OPTIONS' => false,
            'TRACE' => false,
            'INVALID' => false,

            'get' => true,
            'post' => true,
            'put' => true,
            'delete' => true,
            'head' => false,
            'connect' => false,
            'options' => false,
            'trace' => false,
            'invalid' => false,

            '' => false,
        ];

        $routes = [
            '/api/board' => true,
            '/API/BOARD' => false,
            'invalid-route' => false,
            '' => false,
        ];

        foreach ($httpMethods as $method => $shouldReturnResponse) {
            foreach ($routes as $route => $isRouteValid) {
                $failMessage = sprintf('Testing method "%s" with route "%s".', $method, $route);
                $dispatcherResponse = (new Dispatcher($method))->dispatch($route, $storage);

                if ($shouldReturnResponse && $isRouteValid) {
                    $this->assertNotNull($dispatcherResponse, $failMessage);
                    $this->assertLessThan(500, $dispatcherResponse->getStatusCode(), $failMessage);
                    $this->assertArrayHasKey('Content-Type', $dispatcherResponse->getHeaders(), $failMessage);
                    $this->assertEquals('application/json; charset=UTF-8', $dispatcherResponse->getHeaders()['Content-Type'], $failMessage);
                } else {
                    $this->assertNull($dispatcherResponse, $failMessage);
                }
            }
        }
    }
}
