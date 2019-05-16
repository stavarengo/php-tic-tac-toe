<?php
declare(strict_types=1);


namespace TicTacToe\App\Bot;

use TicTacToe\App\Board\Board;
use TicTacToe\App\FinalResultChecker;

/**
 * This bot choose its next move randomly.
 */
class RandomBot implements \TicTacToe\App\Bot\BotInterface
{
    /**
     * @var FinalResultChecker
     */
    protected $finalResultChecker;

    /**
     * DummyBot constructor.
     */
    public function __construct()
    {
        $this->finalResultChecker = new FinalResultChecker();
    }

    public function makeMove(array $boardState, string $playerUnit = 'X'): array
    {
        if (!Board::isValidUnit($playerUnit)) {
            return [-1, -1, null];
        }

        $botUnit = Board::VALID_UNITS[0];
        if ($playerUnit == $botUnit) {
            $botUnit = Board::VALID_UNITS[1];
        }

        if (!Board::isValidBoard($boardState)) {
            return [-1, -1, null];
        }

        if ($this->finalResultChecker->getFinalResultFromBoardArray($boardState)) {
            return [-1, -1, null];
        }

        $allPossibleMoves = [];
        foreach ($boardState as $rowIndex => $row) {
            foreach ($row as $colIndex => $col) {
                if (!$col) {
                    $allPossibleMoves[] = [$rowIndex, $colIndex];
                }
            }
        }

        $chosenMove = $allPossibleMoves[array_rand($allPossibleMoves)];
        $chosenMove[] = $botUnit;

        return $chosenMove;
    }
}