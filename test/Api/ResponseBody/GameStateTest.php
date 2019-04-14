<?php
/** @noinspection PhpUnhandledExceptionInspection */
declare(strict_types=1);

namespace TicTacToe\Test\Api\ResponseBody;

use PHPUnit\Framework\TestCase;
use TicTacToe\Api\ResponseBody\GameState;
use TicTacToe\App\Board\Board;
use TicTacToe\App\FinalResultChecker;

class GameStateTest extends TestCase
{
    public function testWhenBoardIsNull()
    {
        $gameState = new GameState(null);
        $this->assertNull($gameState->getBoard());

        $this->assertJsonStringEqualsJsonString(json_encode(['game' => null]), $gameState->toJson());
    }

    public function testWhenBoardIsNotNullAndDoesNotHaveWinner()
    {
        $board = new Board();
        $gameState = new GameState($board);

        $this->assertNotNull($gameState->getBoard());

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
            $gameState->toJson()
        );
    }

    public function testWhenBoardIsDraw()
    {
        $board = new Board();
        $board->set(0, 0, $board->getBotUnit());
        $board->set(0, 1, $board->getHumanUnit());
        $board->set(0, 2, $board->getBotUnit());
        $board->set(1, 0, $board->getHumanUnit());
        $board->set(1, 2, $board->getBotUnit());
        $board->set(1, 1, $board->getHumanUnit());
        $board->set(2, 1, $board->getBotUnit());
        $board->set(2, 2, $board->getHumanUnit());
        $board->set(2, 0, $board->getBotUnit());

        $finalResultChecker = new FinalResultChecker();
        $this->assertTrue($finalResultChecker->isDraw($board));

        $gameState = new GameState($board);

        $this->assertNotNull($gameState->getBoard());

        $this->assertJsonStringEqualsJsonString(
            json_encode([
                'game' => [
                    'winner' => [
                        'result' => FinalResultChecker::DRAW,
                        'coordinates' => null,
                    ],
                    "board" => $board->toArray(),
                    "units" => [
                        "human" => $board->getHumanUnit(),
                        "bot" => $board->getBotUnit(),
                    ],
                ],
            ]),
            $gameState->toJson()
        );
    }

    public function testWhenBoardHasWinner()
    {
        $board = new Board();
        $board->set(0, 0, $board->getBotUnit());
        $board->set(0, 1, $board->getBotUnit());
        $board->set(0, 2, $board->getBotUnit());
        $board->set(1, 0, $board->getHumanUnit());
        $board->set(1, 2, $board->getHumanUnit());

        $finalResultChecker = new FinalResultChecker();
        $this->assertEquals($board->getBotUnit(), $finalResultChecker->getFinalResult($board));

        $gameState = new GameState($board);

        $this->assertNotNull($gameState->getBoard());

        $this->assertJsonStringEqualsJsonString(
            json_encode([
                'game' => [
                    'winner' => [
                        'result' => $board->getBotUnit(),
                        'coordinates' => [[0, 0], [0, 1], [0, 2]],
                    ],
                    "board" => $board->toArray(),
                    "units" => [
                        "human" => $board->getHumanUnit(),
                        "bot" => $board->getBotUnit(),
                    ],
                ],
            ]),
            $gameState->toJson()
        );
    }

    public function testBoardIsEmpty()
    {
        $board = new Board();

        $finalResultChecker = new FinalResultChecker();
        $this->assertNull($finalResultChecker->getFinalResult($board));

        $gameState = new GameState($board);

        $this->assertNotNull($gameState->getBoard());

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
            $gameState->toJson()
        );
    }

    public function testBoardIsNotEmptyButTheGameIsNotDoneYet()
    {
        $board = new Board();
        $board->set(0, 0, $board->getBotUnit());
        $board->set(0, 1, $board->getHumanUnit());

        $finalResultChecker = new FinalResultChecker();
        $this->assertNull($finalResultChecker->getFinalResult($board));

        $gameState = new GameState($board);

        $this->assertNotNull($gameState->getBoard());

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
            $gameState->toJson()
        );
    }
}
