<?php
declare(strict_types=1);


namespace TicTacToe\Api;


use TicTacToe\Api\RequestHandler\DeleteHandler;
use TicTacToe\Api\RequestHandler\GetHandler;
use TicTacToe\Api\RequestHandler\PostHandler;
use TicTacToe\Api\RequestHandler\PutHandler;
use TicTacToe\Api\Storage\StorageInterface;
use TicTacToe\App\Dispatcher\DispatcherInterface;
use TicTacToe\App\Dispatcher\DispatcherResponse;

class Dispatcher implements DispatcherInterface
{
    protected $method;

    /**
     * Dispatcher constructor.
     * @param $method
     */
    public function __construct(string $method)
    {
        $this->method = $method;
    }

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
    public function dispatch(string $requestRoute, StorageInterface $storage): ?DispatcherResponse
    {
        if ($requestRoute != '/api/board') {
            return null;
        }

        $requestBody = null;
        switch (strtoupper($this->method)) {
            case 'GET':
                $handler = new GetHandler();
                break;
            case 'DELETE':
                $handler = new DeleteHandler();
                break;
            case 'POST':
            case 'PUT':
                $input = file_get_contents('php://input');
                $requestBody = json_decode($input);

                if ($this->method == 'POST') {
                    $handler = new PostHandler();
                } else {
                    $handler = new PutHandler();
                }
                break;
            default:
                $handler = null;
        }

        if (!$handler) {
            return null;
        }

        $apiResponse = $handler->handleIt($requestBody, $storage);

        return new DispatcherResponse(
            $apiResponse->getStatusCode(),
            $apiResponse->getBody() ? $apiResponse->getBody()->toJson() : '',
            [
                'Content-Type' => 'application/json; charset=UTF-8',
            ]
        );
    }
}