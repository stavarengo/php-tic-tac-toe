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

    public function testGetRequestRoute()
    {
        $expectedRoutes = [
            '/',
            '/api/board',
        ];

        foreach ($expectedRoutes as $expectedRoute) {
            foreach (['', 'public', 'html/public'] as $basePath) {
                $basePathVariations = [
                    $basePath,
                    "/$basePath",
                    "$basePath/",
                    "/$basePath/",
                ];

                foreach ($basePathVariations as $basePathVariation) {
                    $failMsg = 'Failed when base path was "%s" and request URI was "%s"';

                    $requestUriVariations = [
                        $basePathVariation . $expectedRoute,
                        $basePathVariation . ltrim($expectedRoute, '/'),
                        $basePathVariation . rtrim($expectedRoute, '/'),
                        $basePathVariation . trim($expectedRoute, '/'),
                    ];
                    foreach ($requestUriVariations as $requestUriVariation) {
                        $requestUriVariation = preg_replace('~^/+(.*)~', '/$1', $requestUriVariation);

                        if (parse_url($requestUriVariation) === false) {
                            continue;
                        }

                        $fullRequestUri = $requestUriVariation;
                        $this->assertEquals(
                            $expectedRoute,
                            DispatcherAggregate::getRequestRoute($basePathVariation, $fullRequestUri),
                            sprintf($failMsg, $basePathVariation, $fullRequestUri)
                        );

                        $fullRequestUri = "$requestUriVariation?param=value";
                        $this->assertEquals(
                            $expectedRoute,
                            DispatcherAggregate::getRequestRoute($basePathVariation, $fullRequestUri),
                            sprintf($failMsg, $basePathVariation, $fullRequestUri)
                        );

                        $fullRequestUri = "$requestUriVariation#compoent";
                        $this->assertEquals(
                            $expectedRoute,
                            DispatcherAggregate::getRequestRoute($basePathVariation, $fullRequestUri),
                            sprintf($failMsg, $basePathVariation, $fullRequestUri)
                        );

                        $fullRequestUri = "$requestUriVariation?param=value#component";
                        $this->assertEquals(
                            $expectedRoute,
                            DispatcherAggregate::getRequestRoute($basePathVariation, $fullRequestUri),
                            sprintf($failMsg, $basePathVariation, $fullRequestUri)
                        );
                    }
                }
            }
        }
    }
}
