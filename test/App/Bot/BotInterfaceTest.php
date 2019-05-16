<?php
/** @noinspection PhpUnhandledExceptionInspection */
declare(strict_types=1);

namespace TicTacToe\Test\App\Bot;

use PHPUnit\Framework\TestCase;
use TicTacToe\App\Board\Board;
use TicTacToe\App\Bot\DummyBot;
use TicTacToe\App\Bot\MinimaxBot;
use TicTacToe\App\Bot\RandomBot;
use TicTacToe\App\FinalResultChecker;

class BotInterfaceTest extends TestCase
{
    /**
     * @var \TicTacToe\App\Bot\BotInterface[]
     */
    protected $allBotsImplementation;

    public function testAllPossibleMovies()
    {

        $recursiveFunction = function ($whoPlays, $humanUnit, $botUnit, array $board, \TicTacToe\App\Bot\BotInterface $bot)
        use (&$recursiveFunction) {
            $whoPlaysNext = $whoPlays == $humanUnit ? $botUnit : $humanUnit;

            if ((new FinalResultChecker())->getFinalResultFromBoardArray($board)) {
                return;
            }

            if ($whoPlays == $humanUnit) {
                for ($row = 0; $row < 3; $row++) {
                    for ($col = 0; $col < 3; $col++) {
                        if ($board[$row][$col]) {
                            continue;
                        }
                        $board[$row][$col] = $humanUnit;

                        $recursiveFunction($whoPlaysNext, $humanUnit, $botUnit, $board, $bot);

                        $board[$row][$col] = '';
                    }
                }
            } else {
                try {
                    $move = $bot->makeMove($board, $humanUnit);
                } catch (\Throwable $e) {
                    $this->fail(
                        sprintf(
                            'The bot "%s" failed with exception "%s" when deciding the its next move in the board "%s". The exception message is: "%s".',
                            get_class($bot),
                            get_class($e),
                            json_encode($board),
                            $e->getMessage()
                        )
                    );
                }

                $failMsg = sprintf(
                    'The bot "%s" returned an invalid move "%s" for the board "%s".',
                    get_class($bot),
                    json_encode($move),
                    json_encode($board)
                );

                $this->assertArrayHasKey(0, $move, $failMsg);
                $this->assertIsInt($move[0], $failMsg);
                $this->assertArrayHasKey(1, $move, $failMsg);
                $this->assertIsInt($move[1], $failMsg);
                $this->assertArrayHasKey(2, $move, $failMsg);
                $this->assertEquals($botUnit, $move[2], $failMsg);

                $board[$move[0]][$move[1]] = $move[2];

                $recursiveFunction($whoPlaysNext, $humanUnit, $botUnit, $board, $bot);

                $board[$move[0]][$move[1]] = '';
            }
        };

        foreach ($this->allBotsImplementation as $bot) {
            foreach (Board::VALID_UNITS as $whoStarts) {
                $boardArray = (new Board())->toArray();
                $recursiveFunction($whoStarts, Board::VALID_UNITS[0], Board::VALID_UNITS[1], $boardArray, $bot);
            }
        }

        $this->assertTrue(true, 'all bots where able to play all possible moves successfully.');
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
                        $invalidUnitByteCode - 1
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

                $expectedValue = [-1, -1, null];
                if ($bot instanceof DummyBot) {
                    $expectedValue = [-2, -2, null];
                }

                $this->assertEquals(
                    $expectedValue,
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

            $expectedValue = [-1, -1, null];
            if ($bot instanceof DummyBot) {
                $expectedValue = [-3, -3, null];
            }

            $this->assertJsonStringEqualsJsonString(
                json_encode($expectedValue),
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

            $expectedValue = [-1, -1, null];
            if ($bot instanceof DummyBot) {
                $expectedValue = [-3, -3, null];
            }

            $this->assertJsonStringEqualsJsonString(
                json_encode($expectedValue),
                json_encode($nextMove),
                sprintf('The bot "%s" dit not return the expected result when the board is not full, but the bot already had win the game.',
                    get_class($bot))
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

            $expectedValue = [-1, -1, null];
            if ($bot instanceof DummyBot) {
                $expectedValue = [-3, -3, null];
            }

            $this->assertJsonStringEqualsJsonString(
                json_encode($expectedValue),
                json_encode($nextMove),
                sprintf('The bot "%s" dit not return the expected result when the board is not full, but the human already had win the game.',
                    get_class($bot))
            );
        }

    }

    protected function setUp(): void
    {
        $this->allBotsImplementation = [
            new DummyBot(),
            new RandomBot(),
            new MinimaxBot(),
        ];
    }
}
