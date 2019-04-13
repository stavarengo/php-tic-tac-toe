<?php
declare(strict_types=1);


namespace TicTacToe\Api\RequestHandler;


use TicTacToe\Api\Response;
use TicTacToe\Api\ResponseBody\GameState;
use TicTacToe\Api\Storage\StorageInterface;

class GetHandler implements RequestHandlerInterface
{
    public function handleIt(?\stdClass $requestBody, StorageInterface $storage): Response
    {
        $board = $storage->get(PostHandler::STORAGE_KEY_GAME_BOARD, null);
        return new Response(new GameState($board), 200);
    }
}