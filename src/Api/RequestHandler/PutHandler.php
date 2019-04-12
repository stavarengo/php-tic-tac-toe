<?php
declare(strict_types=1);


namespace TicTacToe\Api\RequestHandler;


use TicTacToe\Api\Response;
use TicTacToe\Api\ResponseBody\Error;
use TicTacToe\Api\ResponseBody\GameState;
use TicTacToe\Api\Storage\StorageInterface;
use TicTacToe\App\Board\Board;
use TicTacToe\App\Board\Exception\CoordinateAlreadyInUse;
use TicTacToe\App\Board\Exception\InvalidBoardColumn;
use TicTacToe\App\Board\Exception\InvalidBoardRow;
use TicTacToe\App\Board\Exception\InvalidBoardUnit;
use TicTacToe\App\Bot\DummyBot;

class PutHandler implements RequestHandlerInterface
{
    /**
     * @var \MoveInterface
     */
    protected $bot;

    public function __construct(?\MoveInterface $bot = null)
    {
        $this->bot = $bot ?? new DummyBot();
    }

    public function handleIt(?\stdClass $requestBody, StorageInterface $storage): Response
    {
        if (!$requestBody) {
            return new Response(new Error('Missing body content.'), 422);
        }

        if (!isset($requestBody->row)) {
            return new Response(new Error('Please provide a value for the "row" attribute.'), 422);
        }

        if (!isset($requestBody->column)) {
            return new Response(new Error('Please provide a value for the "column" attribute.'), 422);
        }

        /** @var GameState $gameState */
        $gameState = $storage->get(PostHandler::STORAGE_KEY_GAME_STATE);

        if (!$gameState || !$gameState->getBoard()) {
            return new Response(new Error('There is no game in progress.'), 409);
        }

        try {
            /** @noinspection PhpUnhandledExceptionInspection */
            $gameState->getBoard()->set(
                $requestBody->row,
                $requestBody->column,
                $gameState->getBoard()->getHumanUnit()
            );
        } catch (CoordinateAlreadyInUse $e) {
            return new Response(new Error($e->getMessage()), 400);
        } catch (InvalidBoardColumn $e) {
            return new Response(new Error($e->getMessage()), 400);
        } catch (InvalidBoardRow $e) {
            return new Response(new Error($e->getMessage()), 400);
        }

        if ($this->isThereAnyMoveLeft($gameState->getBoard())) {
            $botMove = $this->bot->makeMove($gameState->getBoard()->toArray(), $gameState->getBoard()->getHumanUnit());
            try {
                $gameState->getBoard()->set($botMove[0], $botMove[1], $gameState->getBoard()->getBotUnit());
            } catch (CoordinateAlreadyInUse $e) {
                return new Response(new Error('The bot choose an invalid move. ' . $e->getMessage()), 400);
            } catch (InvalidBoardColumn $e) {
                return new Response(new Error('The bot choose an invalid move. ' . $e->getMessage()), 400);
            } catch (InvalidBoardRow $e) {
                return new Response(new Error('The bot choose an invalid move. ' . $e->getMessage()), 400);
            } catch (InvalidBoardUnit $e) {
                return new Response(new Error('The bot choose an invalid move. ' . $e->getMessage()), 400);
            }
        }

        return new Response($gameState, 200);
    }

    private function isThereAnyMoveLeft(Board $board): bool
    {
        foreach ($board->toArray() as $rowIndex => $row) {
            foreach ($row as $colIndex => $col) {
                if (!$col) {
                    return true;
                }
            }
        }

        return false;
    }
}