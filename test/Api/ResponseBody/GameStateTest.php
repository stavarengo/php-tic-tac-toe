<?php
/** @noinspection PhpUnhandledExceptionInspection */
declare(strict_types=1);

namespace TicTacToe\Test\Api\ResponseBody;

use PHPUnit\Framework\TestCase;
use TicTacToe\Api\ResponseBody\GameState;
use TicTacToe\App\Board\Board;

class GameStateTest extends TestCase
{
    public function testWhenBoardIsNull()
    {
        $error = new GameState(null);
        $this->assertNull($error->getBoard());

        $this->assertJsonStringEqualsJsonString(json_encode(['game' => null]), $error->toJson());
    }

    public function testWhenBoardIsNotNull()
    {
        $board = new Board();
        $error = new GameState($board);

        $this->assertNotNull($error->getBoard());

        $this->assertJsonStringEqualsJsonString(
            json_encode([
                'game' => [
                    'winner' => null,
                    "board" => $board->toArray(),
                    "units" => [
                        "human" => $board->getHumanUnit(),
                        "bot" => $board->getBotUnit(),
                    ],
                ],
            ]),
            $error->toJson()
        );
    }
}
