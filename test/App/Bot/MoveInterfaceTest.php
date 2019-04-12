<?php
/** @noinspection PhpUnhandledExceptionInspection */
declare(strict_types=1);

namespace TicTacToe\Test\App\Bot;

use PHPUnit\Framework\TestCase;
use TicTacToe\App\Board\Board;
use TicTacToe\App\Bot\DummyBot;
use TicTacToe\App\Bot\MinimaxBot;
use TicTacToe\App\Bot\RandomBot;

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
            new RandomBot(),
            new MinimaxBot(),
        ];
    }

    public function testTryingToGetTheNextMoveUsingAnInvalidPlayerUnit()
    {
        $board = new Board();
        $invalidUnitByteCode = 0;

        do {
            $invalidUnit = chr($invalidUnitByteCode++);

            if (Board::isValidUnit($invalidUnit)) {
                continue;
            }

            foreach ($this->allBotsImplementation as $bot) {
                $nextMove = $bot->makeMove($board->toArray(), $invalidUnit);
                $this->assertEquals(
                    [-1, -1, null],
                    $nextMove,
                    sprintf(
                        'The bot "%s" dit not return the expected value when using the invalid unit "%s" (byte code "%s").',
                        get_class($bot),
                        $invalidUnit,
                        $invalidUnitByteCode -1
                    )
                );
            }
        } while ($invalidUnitByteCode < 256);
    }

    public function testTryingToGetTheNextMoveFromAnInvalidBoard()
    {
        $invalidBoards = [
            [],
            [1, 3, 4],
            [[], [], []],
            [[1, 2, 3], [], []],
            [['', ''], ['', '', ''], ['', '']],
            [['', '', ''], ['', '', ''], ['', '']],
        ];

        foreach ($this->allBotsImplementation as $bot) {
            foreach ($invalidBoards as $invalidBoard) {
                // Make sure the board is really invalid.
                $this->assertFalse(Board::isValidBoard($invalidBoard));

                $nextMove = $bot->makeMove($invalidBoard, 'O');
                $this->assertEquals(
                    [-1, -1, null],
                    $nextMove,
                    sprintf(
                        'The bot "%s" dit not return the expected value when using this invalid board "%s".',
                        get_class($bot),
                        json_encode($invalidBoard)
                    )
                );
            }
        }
    }

    public function testBotShouldReturnTheRightUnitToBeUsedInTheMove()
    {
        foreach ($this->allBotsImplementation as $bot) {
            $board = new Board(Board::VALID_UNITS[0], Board::VALID_UNITS[1]);
            // Set some moves to reduce the response time of Minimax Algorithm
            $board->set(0, 1, $board->getHumanUnit())->set(0, 2, $board->getBotUnit());
            $nextMove = $bot->makeMove($board->toArray(), $board->getHumanUnit());
            $this->assertEquals(
                $board->getBotUnit(),
                $nextMove[2],
                sprintf('The bot "%s" dit not return the correct unit.', get_class($bot))
            );

            $board = new Board(Board::VALID_UNITS[1], Board::VALID_UNITS[0]);
            // Set some moves to reduce the response time of Minimax Algorithm
            $board->set(0, 1, $board->getHumanUnit())->set(0, 2, $board->getBotUnit());
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
                json_encode([-1, -1, null]),
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
                json_encode([-1, -1, null]),
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
                json_encode([-1, -1, null]),
                json_encode($nextMove),
                sprintf('The bot "%s" dit not return the expected result when the board is not full, but the human already had win the game.', get_class($bot))
            );
        }

    }
}
