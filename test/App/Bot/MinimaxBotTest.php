<?php
/** @noinspection PhpUnhandledExceptionInspection */
declare(strict_types=1);

namespace TicTacToe\Test\App\Bot;

use PHPUnit\Framework\TestCase;
use TicTacToe\App\Board\Board;
use TicTacToe\App\Bot\MinimaxBot;
use TicTacToe\App\FinalResultChecker;

class MinimaxBotTest extends TestCase
{
    public function testMinimaxBotCanNeverLoose()
    {
        $recursiveFunction = function ($whoPlays, $humanUnit, $botUnit, array $board, \TicTacToe\App\Bot\BotInterface $bot)
        use (&$recursiveFunction) {
            $whoPlaysNext = $whoPlays == $humanUnit ? $botUnit : $humanUnit;

            if (($winner = (new FinalResultChecker())->getFinalResultFromBoardArray($board))) {
                $this->assertNotEquals(
                    $humanUnit,
                    $winner,
                    sprintf(
                        'The bot "%s" lose with the board "%s".',
                        get_class($bot),
                        json_encode($board)
                    )
                );
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

        foreach (Board::VALID_UNITS as $whoStarts) {
            $boardArray = (new Board())->toArray();
            $recursiveFunction($whoStarts, Board::VALID_UNITS[0], Board::VALID_UNITS[1], $boardArray, new MinimaxBot());
        }

        $this->assertTrue(true, 'Bot where able to play all possible moves successfully.');
    }
}
