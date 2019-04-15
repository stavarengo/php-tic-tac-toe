<?php
/** @noinspection PhpUnhandledExceptionInspection */
declare(strict_types=1);

namespace TicTacToe\Test\App\Bot;

use PHPUnit\Framework\TestCase;
use TicTacToe\App\Board\Board;
use TicTacToe\App\Bot\DummyBot;
use TicTacToe\App\FinalResultChecker;

class DummyBotTest extends TestCase
{
    public function testDummyBotMustAlwaysChooseTheFirstSpotAvailable()
    {
        // Prepare the board in a way that is going to be draw
        $board = new Board();
        $board->set(0,0, $board->getHumanUnit());
        $board->set(1,1, $board->getHumanUnit());
        $board->set(0,2, $board->getHumanUnit());

        $bot = new DummyBot();

        foreach ($board->toArray() as $rowIndex => $row) {
            foreach ($row as $colIndex => $col) {
                if ($col) {
                    // This column is already fulfilled
                    continue;
                }

                $nextMove = $bot->makeMove($board->toArray(), $board->getHumanUnit());

                $this->assertJsonStringEqualsJsonString(
                    json_encode([$rowIndex, $colIndex, $board->getBotUnit()]),
                    json_encode($nextMove)
                );

                $board->set($nextMove[0], $nextMove[1], $nextMove[2]);
            }
        }
    }

    public function testMustReturnNegativeFour()
    {
        $mockFinalResultChecker = $this->createMock(FinalResultChecker::class);
        $mockFinalResultChecker->method('getFinalResultFromBoardArray')->willReturn(null);

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

        /** @var FinalResultChecker $mockFinalResultChecker */

        $this->assertJsonStringEqualsJsonString(
            json_encode([-4, -4, null]),
            json_encode((new DummyBot($mockFinalResultChecker))->makeMove($board->toArray(), $board->getHumanUnit()))
        );
    }
}
