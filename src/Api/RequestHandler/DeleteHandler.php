<?php
declare(strict_types=1);


namespace TicTacToe\Api\RequestHandler;


use TicTacToe\Api\Response;
use TicTacToe\Api\Storage\StorageInterface;

class DeleteHandler implements RequestHandlerInterface
{
    public function handleIt(?\stdClass $requestBody, StorageInterface $storage): Response
    {
        $storage->delete(PostHandler::STORAGE_KEY_GAME_BOARD);

        return new Response(null, 204);
    }
}