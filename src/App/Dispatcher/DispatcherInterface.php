<?php
declare(strict_types=1);


namespace TicTacToe\App\Dispatcher;


use TicTacToe\Api\Storage\StorageInterface;

interface DispatcherInterface
{
    /**
     * Dispatch the request based on the $requestRoute.
     * It should return a response if, and only if, the $requestRoute is one of its route.
     * If the dispatch does not support the $requestRoute it should return null;
     *
     * @param string $requestRoute
     *      The route requested.
     *
     * @param StorageInterface $storage
     *
     * @return DispatcherResponse|null
     *
     * @throws \Throwable
     *      It can throw any exception during execution.
     */
    public function dispatch(string $requestRoute, StorageInterface $storage): ?DispatcherResponse;
}