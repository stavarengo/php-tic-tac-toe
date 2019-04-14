<?php
declare(strict_types=1);


namespace TicTacToe\Api\RequestHandler;


use TicTacToe\Api\Response;
use TicTacToe\Api\ResponseBody\Error;
use TicTacToe\Api\ResponseBody\GameState;
use TicTacToe\Api\Storage\StorageInterface;
use TicTacToe\App\Board\Board;
use TicTacToe\App\Board\Exception\InvalidBoardUnit;

class PostHandler implements RequestHandlerInterface
{
    /**
     * ID used to store the {@link \TicTacToe\Api\ResponseBody\GameState}.
     */
    public const STORAGE_KEY_GAME_BOARD = 'game-board';

    public function handleIt(?\stdClass $requestBody, StorageInterface $storage): Response
    {
        if (!$requestBody) {
            return new Response(new Error('Missing body content.'), 422);
        }

        if (!isset($requestBody->humanUnit)) {
            return new Response(new Error('Missing the "humanUnit" attribute.'), 422);
        }

        if (!isset($requestBody->botUnit)) {
            return new Response(new Error('Missing the "botUnit" attribute.'), 422);
        }

        if (!$requestBody->humanUnit) {
            return new Response(new Error('Please provide a value for the "humanUnit" attribute.'), 422);
        }

        if (!$requestBody->botUnit) {
            return new Response(new Error('Please provide a value for the "botUnit" attribute.'), 422);
        }

        if ($storage->has(self::STORAGE_KEY_GAME_BOARD)) {
            return new Response(
                new Error('There is already another game in progress. To start a new game you must delete the one currently in progress.'),
                409
            );
        }

        try {
            $gameState = new GameState(new Board($requestBody->botUnit, $requestBody->humanUnit));
        } catch (InvalidBoardUnit $e) {
            return new Response(new Error($e->getMessage()), 422);
        }

        $storage->set(self::STORAGE_KEY_GAME_BOARD, $gameState->getBoard());

        return new Response($gameState, 201);
    }
}