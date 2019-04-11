<?php
declare(strict_types=1);


namespace TicTacToe\Api\RequestHandler;


use TicTacToe\Api\Response;
use TicTacToe\Api\ResponseBody\Error;
use TicTacToe\Api\ResponseBody\GameState;
use TicTacToe\Api\Storage\StorageInterface;
use TicTacToe\App\Board\Board;
use TicTacToe\App\Board\Exception\InvalidBoardUnit;

class GetHandler implements RequestHandlerInterface
{
    public function handleIt(?\stdClass $requestBody, StorageInterface $storage): Response
    {
        $gameState = $storage->get(PostHandler::STORAGE_KEY_GAME_STATE, new GameState(null));
        return new Response($gameState, 200);
    }
}