<?php
/** @noinspection PhpUnhandledExceptionInspection */
declare(strict_types=1);

namespace TicTacToe\Test\App\Dispatcher;

use PHPUnit\Framework\TestCase;
use TicTacToe\Api\Storage\ArrayStorage;
use TicTacToe\App\Dispatcher\DispatcherAggregate;
use TicTacToe\App\Dispatcher\DispatcherInterface;
use TicTacToe\App\Dispatcher\DispatcherResponse;

class DispatcherAggregateTest extends TestCase
{
    public function testWithNoDispatchers()
    {
        $this->assertNull((new DispatcherAggregate('/', '/', []))->dispatch(new ArrayStorage()));
    }

    public function testWithDispatchersThatReturnNonNullValues()
    {
        /** @var DispatcherInterface $stubDispatcher */
        $stubDispatcher = $this->createMock(DispatcherInterface::class);
        $stubDispatcher->method('dispatch')
            ->willReturn(new DispatcherResponse(200, '', []));

        $dispatchers = [$stubDispatcher];

        $this->assertNotNull((new DispatcherAggregate('/', '/', $dispatchers))->dispatch(new ArrayStorage()));
    }

    public function testWithDispatchersThatReturnNullValues()
    {
        /** @var DispatcherInterface $stubDispatcher */
        $stubDispatcher = $this->createMock(DispatcherInterface::class);
        $stubDispatcher->method('dispatch')
            ->willReturn(null);

        $dispatchers = [$stubDispatcher];

        $this->assertNull((new DispatcherAggregate('/', '/', $dispatchers))->dispatch(new ArrayStorage()));
    }
}
