<?php
/** @noinspection PhpUnhandledExceptionInspection */
declare(strict_types=1);

namespace TicTacToe\Test\App;

use PHPUnit\Framework\TestCase;
use TicTacToe\App\Board\Board;
use TicTacToe\App\FinalResultChecker;

class FinalResultCheckerTest extends TestCase
{
    public function testTryingToGetFinalResultFromAnInvalidBoard()
    {
        $invalidBoards = [
            [],
            [1, 3, 4],
            [[], [], []],
            [[1, 2, 3], [], []],
            [['', ''], ['', '', ''], ['', '']],
            [['', '', ''], ['', '', ''], ['', '']],
            [['X', 'X', 'X'], ['', '', ''], ['', '']],
        ];

        $finalResultChecker = new FinalResultChecker();

        foreach ($invalidBoards as $invalidBoard) {
            $this->assertNull(
                $finalResultChecker->getFinalResultFromBoardArray($invalidBoard),
                sprintf('Wrong result when using this invalid board "%s".', json_encode($invalidBoard))
            );
            $this->assertFalse(
                $finalResultChecker->isDrawFromBoardArray($invalidBoard),
                sprintf('Wrong result when using this invalid board "%s".', json_encode($invalidBoard))
            );

            $this->assertNull(
                $finalResultChecker->getWinnerCoordinatesFromBoardArray($invalidBoard),
                sprintf('Wrong result when using this invalid board "%s".', json_encode($invalidBoard))
            );
        }
    }

    public function testGettingTheWinnerInAllPossibleCombinations()
    {
        $unit1 = Board::VALID_UNITS[0];
        $unit2 = Board::VALID_UNITS[1];
        $finalResultChecker = new FinalResultChecker();
        $allCombinations = [
            // Rows victory
            [[0, 0], [0, 1], [0, 2]],
            [[1, 0], [1, 1], [1, 2]],
            [[2, 0], [2, 1], [2, 2]],

            // Columns victory
            [[0, 0], [1, 0], [2, 0]],
            [[0, 1], [1, 1], [2, 1]],
            [[0, 2], [1, 2], [2, 2]],

            // Diagonals victory
            [[0, 0], [1, 1], [2, 2]],
            [[0, 2], [1, 1], [2, 0]],
        ];

        foreach ([$unit1, $unit2] as $winnerUnit) {
            foreach ($allCombinations as $coordinates) {
                $board = new Board($unit1, $unit2);
                foreach ($coordinates as $coordinate) {
                    $board->set($coordinate[0], $coordinate[1], $winnerUnit);
                }

                $this->assertEquals(
                    $winnerUnit,
                    $finalResultChecker->getFinalResult($board),
                    sprintf('Wrong result when the winner should be "%s", using coordinates "%s".', $winnerUnit,
                        json_encode($coordinates))
                );
                $this->assertEquals(
                    $winnerUnit,
                    $finalResultChecker->getFinalResultFromBoardArray($board->toArray()),
                    sprintf('Wrong result when the winner should be "%s", using coordinates "%s".', $winnerUnit,
                        json_encode($coordinates))
                );

                $this->assertFalse(
                    $finalResultChecker->isDraw($board),
                    sprintf('Wrong result when the winner should be "%s", using coordinates "%s".', $winnerUnit,
                        json_encode($coordinates))
                );
                $this->assertFalse(
                    $finalResultChecker->isDrawFromBoardArray($board->toArray()),
                    sprintf('Wrong result when the winner should be "%s", using coordinates "%s".', $winnerUnit,
                        json_encode($coordinates))
                );

                $this->assertJsonStringEqualsJsonString(
                    json_encode($coordinates),
                    json_encode($finalResultChecker->getWinnerCoordinates($board)),
                    sprintf('Wrong result when the winner should be "%s", using coordinates "%s".', $winnerUnit,
                        json_encode($coordinates))
                );
                $this->assertJsonStringEqualsJsonString(
                    json_encode($coordinates),
                    json_encode($finalResultChecker->getWinnerCoordinatesFromBoardArray($board->toArray())),
                    sprintf('Wrong result when the winner should be "%s", using coordinates "%s".', $winnerUnit,
                        json_encode($coordinates))
                );
            }
        }
    }

    public function testTheGameIsDraw()
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
        $this->assertTrue($finalResultChecker->isDrawFromBoardArray($board->toArray()));
        $this->assertEquals(FinalResultChecker::DRAW, $finalResultChecker->getFinalResult($board));
        $this->assertEquals(FinalResultChecker::DRAW,
            $finalResultChecker->getFinalResultFromBoardArray($board->toArray()));
        $this->assertNull($finalResultChecker->getWinnerCoordinates($board));
        $this->assertNull($finalResultChecker->getWinnerCoordinatesFromBoardArray($board->toArray()));
    }

    public function testBoardIsEmpty()
    {
        $board = new Board();
        $finalResultChecker = new FinalResultChecker();

        $this->assertFalse($finalResultChecker->isDraw($board));
        $this->assertFalse($finalResultChecker->isDrawFromBoardArray($board->toArray()));
        $this->assertNull($finalResultChecker->getFinalResult($board));
        $this->assertNull($finalResultChecker->getFinalResultFromBoardArray($board->toArray()));
        $this->assertNull($finalResultChecker->getWinnerCoordinates($board));
        $this->assertNull($finalResultChecker->getWinnerCoordinatesFromBoardArray($board->toArray()));
    }

    public function testBoardIsNotEmptyButTheGameIsNotDoneYet()
    {
        $finalResultChecker = new FinalResultChecker();

        // Just one player has played
        $board = new Board();
        $board->set(0, 0, $board->getBotUnit());
        $this->assertFalse($finalResultChecker->isDraw($board));
        $this->assertFalse($finalResultChecker->isDrawFromBoardArray($board->toArray()));
        $this->assertNull($finalResultChecker->getFinalResult($board));
        $this->assertNull($finalResultChecker->getFinalResultFromBoardArray($board->toArray()));
        $this->assertNull($finalResultChecker->getWinnerCoordinates($board));
        $this->assertNull($finalResultChecker->getWinnerCoordinatesFromBoardArray($board->toArray()));

        // Both players has played
        $board = new Board();
        $board->set(0, 0, $board->getBotUnit());
        $board->set(0, 1, $board->getHumanUnit());
        $this->assertFalse($finalResultChecker->isDraw($board));
        $this->assertFalse($finalResultChecker->isDrawFromBoardArray($board->toArray()));
        $this->assertNull($finalResultChecker->getFinalResult($board));
        $this->assertNull($finalResultChecker->getFinalResultFromBoardArray($board->toArray()));
        $this->assertNull($finalResultChecker->getWinnerCoordinates($board));
        $this->assertNull($finalResultChecker->getWinnerCoordinatesFromBoardArray($board->toArray()));
    }
}
