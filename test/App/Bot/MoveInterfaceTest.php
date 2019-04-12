<?php
/** @noinspection PhpUnhandledExceptionInspection */
declare(strict_types=1);

namespace TicTacToe\Test\App\Bot;

use PHPUnit\Framework\TestCase;
use TicTacToe\App\Board\Board;
use TicTacToe\App\Bot\DummyBot;

class MoveInterfaceTest extends TestCase
{
    /**
     * @var \MoveInterface[]
     */
    protected $allBotsImplementation;

    protected function setUp(): void
    {
        $this->allBotsImplementation = [
            new DummyBot(),
        ];
    }

    public function testBotShouldReturnTheRightUnitToBeUsedInTheMove()
    {
        foreach ($this->allBotsImplementation as $bot) {
            $board = new Board('O', 'X');
            $nextMove = $bot->makeMove($board->toArray(), $board->getHumanUnit());
            $this->assertEquals(
                $board->getBotUnit(),
                $nextMove[2],
                sprintf('The bot "%s" dit not return the correct unit.', get_class($bot))
            );

            $board = new Board('X', 'O');
            $nextMove = $bot->makeMove($board->toArray(), $board->getHumanUnit());
            $this->assertEquals(
                $board->getBotUnit(),
                $nextMove[2],
                sprintf('The bot "%s" dit not return the correct unit.', get_class($bot))
            );
        }
    }

    public function testTryToMoveUsingEmptyBoard()
    {
        $board = new Board();
        foreach ($this->allBotsImplementation as $bot) {
            $nextMove = $bot->makeMove($board->toArray(), $board->getHumanUnit());
            $this->assertJsonStringEqualsJsonString(
                json_encode([$nextMove[0], $nextMove[1], $board->getBotUnit()]),
                json_encode($nextMove),
                sprintf('The bot "%s" dit not return the expected move when the board is empty.', get_class($bot))
            );
        }
    }

    public function testTryToMoveUsingBoardThatHasNoEmptySpaceLeft()
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

        foreach ($this->allBotsImplementation as $bot) {
            $nextMove = $bot->makeMove($board->toArray(), $board->getHumanUnit());
            $this->assertJsonStringEqualsJsonString(
                json_encode([-1, -1, $board->getBotUnit()]),
                json_encode($nextMove),
                sprintf('The bot "%s" dit not return the expected result when the board is full.', get_class($bot))
            );
        }
    }

    public function testTryToMoveUsingBoardThatHasSpaceLeftButTheGameAlreadyHaveAWinner()
    {
        $board = new Board();
        $board->set(0, 0, $board->getBotUnit());
        $board->set(1, 0, $board->getBotUnit());
        $board->set(2, 0, $board->getBotUnit());
        $board->set(0, 2, $board->getHumanUnit());
        $board->set(1, 2, $board->getHumanUnit());

        foreach ($this->allBotsImplementation as $bot) {
            $nextMove = $bot->makeMove($board->toArray(), $board->getHumanUnit());
            $this->assertJsonStringEqualsJsonString(
                json_encode([-1, -1, $board->getBotUnit()]),
                json_encode($nextMove),
                sprintf('The bot "%s" dit not return the expected result when the board is not full, but the bot already had win the game.', get_class($bot))
            );
        }

        $board = new Board();
        $board->set(0, 0, $board->getHumanUnit());
        $board->set(1, 0, $board->getHumanUnit());
        $board->set(2, 0, $board->getHumanUnit());
        $board->set(0, 2, $board->getBotUnit());
        $board->set(1, 2, $board->getBotUnit());

        foreach ($this->allBotsImplementation as $bot) {
            $nextMove = $bot->makeMove($board->toArray(), $board->getHumanUnit());
            $this->assertJsonStringEqualsJsonString(
                json_encode([-1, -1, $board->getBotUnit()]),
                json_encode($nextMove),
                sprintf('The bot "%s" dit not return the expected result when the board is not full, but the human already had win the game.', get_class($bot))
            );
        }

    }
}
