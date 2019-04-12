<?php
/** @noinspection PhpUnhandledExceptionInspection */
declare(strict_types=1);

namespace TicTacToe\Test\App\Bot;

use PHPUnit\Framework\TestCase;
use TicTacToe\App\Board\Board;
use TicTacToe\App\Bot\DummyBot;

class DummyBotTest extends TestCase
{
    public function testChooseNextMoveInAEmptyBoard()
    {
        $board = new Board();
        $nextMove = (new DummyBot())->makeMove($board->toArray(), $board->getHumanUnit());

        $this->assertJsonStringEqualsJsonString(json_encode([0, 0, $board->getBotUnit()]), json_encode($nextMove));
    }

    public function testChooseNextMoveInAWitNoSpotLeft()
    {
        $board = new Board();
        $board->set(0, 0, $board->getBotUnit());
        $board->set(0, 1, $board->getHumanUnit());
        $board->set(0, 2, $board->getBotUnit());
        $board->set(1, 0, $board->getHumanUnit());
        $board->set(1, 1, $board->getBotUnit());
        $board->set(1, 2, $board->getHumanUnit());
        $board->set(2, 0, $board->getBotUnit());
        $board->set(2, 1, $board->getHumanUnit());
        $board->set(2, 2, $board->getBotUnit());

        $nextMove = (new DummyBot())->makeMove($board->toArray(), $board->getHumanUnit());

        $this->assertJsonStringEqualsJsonString(json_encode([-1, -1, $board->getBotUnit()]), json_encode($nextMove));
    }

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
