<?php
/** @noinspection PhpUnhandledExceptionInspection */
declare(strict_types=1);

namespace TicTacToe\Test\App\Bot;

use PHPUnit\Framework\TestCase;
use TicTacToe\App\Board\Board;
use TicTacToe\App\Bot\DummyBot;

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
}
