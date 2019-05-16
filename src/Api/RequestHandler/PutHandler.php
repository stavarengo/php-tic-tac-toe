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
use TicTacToe\App\Bot\MinimaxBot;
use TicTacToe\App\FinalResultChecker;

class PutHandler implements RequestHandlerInterface
{
    /**
     * @var \TicTacToe\App\Bot\BotInterface
     */
    protected $bot;
    /**
     * @var FinalResultChecker
     */
    private $finalResultChecker;

    public function __construct(?\TicTacToe\App\Bot\BotInterface $bot = null)
    {
        $this->bot = $bot ?? new MinimaxBot();
        $this->finalResultChecker = new FinalResultChecker();
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

        /** @var Board $board */
        $board = $storage->get(PostHandler::STORAGE_KEY_GAME_BOARD);

        if (!$board) {
            return new Response(new Error('There is no game in progress.'), 409);
        }

        if ($this->finalResultChecker->getFinalResult($board)) {
            return new Response(new Error('The game is already done.'), 409);
        }

        try {
            /** @noinspection PhpUnhandledExceptionInspection */
            $board->set(
                $requestBody->row,
                $requestBody->column,
                $board->getHumanUnit()
            );
        } catch (CoordinateAlreadyInUse $e) {
            return new Response(new Error($e->getMessage()), 400);
        } catch (InvalidBoardColumn $e) {
            return new Response(new Error($e->getMessage()), 400);
        } catch (InvalidBoardRow $e) {
            return new Response(new Error($e->getMessage()), 400);
        }

        if (!$this->finalResultChecker->getFinalResult($board)) {
            $botMove = $this->bot->makeMove($board->toArray(), $board->getHumanUnit());
            try {
                $board->set($botMove[0], $botMove[1], $botMove[2]);
            } catch (CoordinateAlreadyInUse $e) {
                $board->clear($requestBody->row, $requestBody->column);

                return new Response(new Error('The bot chosen an invalid move. ' . $e->getMessage()), 400);
            } catch (InvalidBoardColumn $e) {
                $board->clear($requestBody->row, $requestBody->column);

                return new Response(new Error('The bot chosen an invalid move. ' . $e->getMessage()), 400);
            } catch (InvalidBoardRow $e) {
                $board->clear($requestBody->row, $requestBody->column);

                return new Response(new Error('The bot chosen an invalid move. ' . $e->getMessage()), 400);
            } catch (InvalidBoardUnit $e) {
                $board->clear($requestBody->row, $requestBody->column);

                return new Response(new Error('The bot chosen an invalid move. ' . $e->getMessage()), 400);
            }
        }

        return new Response(new GameState($board), 200);
    }
}