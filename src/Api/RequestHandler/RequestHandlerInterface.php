<?php
declare(strict_types=1);


namespace TicTacToe\Api\RequestHandler;


use TicTacToe\Api\Response;
use TicTacToe\Api\Storage\StorageInterface;

interface RequestHandlerInterface
{
    /**
     * Handle the received request.
     *
     * @param \stdClass|null $requestBody
     *      The request body, if any.
     *
     * @param StorageInterface $storage
     *      The handler can use this storage to store anything it needs.
     *
     * @return Response
     */
    public function handleIt(?\stdClass $requestBody, StorageInterface $storage): Response;
}