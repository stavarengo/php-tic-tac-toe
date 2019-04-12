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
        $board = new Board();
        $bot = new DummyBot();

        foreach ($board->toArray() as $rowIndex => $row) {
            foreach ($row as $colIndex => $col) {
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
