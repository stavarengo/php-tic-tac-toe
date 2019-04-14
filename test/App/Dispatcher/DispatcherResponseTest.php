<?php
/** @noinspection PhpUnhandledExceptionInspection */
declare(strict_types=1);

namespace TicTacToe\Test\App\Dispatcher;

use PHPUnit\Framework\TestCase;
use TicTacToe\App\Dispatcher\DispatcherResponse;

class DispatcherResponseTest extends TestCase
{
    public function testGetters()
    {
        $dispatcherResponse = new DispatcherResponse(999, '_CONTENT_', ['foo' => 'BAR']);

        $this->assertEquals(999, $dispatcherResponse->getStatusCode());
        $this->assertEquals('_CONTENT_', $dispatcherResponse->getContent());
        $this->assertArrayHasKey('foo', $dispatcherResponse->getHeaders());
        $this->assertEquals('BAR', $dispatcherResponse->getHeaders()['foo']);
    }
}
