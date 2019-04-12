<?php
declare(strict_types=1);


namespace TicTacToe\App;


use TicTacToe\App\Board\Board;

class FinalResultChecker
{
    public const DRAW = 'draw';

    public function getFinalResultFromBoardArray(array $board): ?string
    {
        if (!Board::isValidBoard($board)) {
            return null;
        }

        // Checking for Rows for X or O victory.
        for ($row = 0; $row < 3; $row++) {
            if ($board[$row][0] && $board[$row][0] == $board[$row][1] && $board[$row][1] == $board[$row][2]) {
                return $board[$row][0];
            }
        }

        // Checking for Columns for X or O victory.
        for ($col = 0; $col < 3; $col++) {
            if ($board[0][$col] && $board[0][$col] == $board[1][$col] && $board[1][$col] == $board[2][$col]) {
                return $board[0][$col];
            }
        }

        // Checking for Diagonals for X or O victory.
        if ($board[0][0] && $board[0][0] == $board[1][1] && $board[1][1] == $board[2][2]) {
            return $board[0][0];
        }
        if ($board[0][2] && $board[0][2] == $board[1][1] && $board[1][1] == $board[2][0]) {
            return $board[0][2];
        }

        foreach ($board as $rowIndex => $row) {
            foreach ($row as $colIndex => $col) {
                if (!$col) {
                    // There still have empty places
                    return null;
                }
            }
        }

        return self::DRAW;
    }

    public function getFinalResult(Board $board): ?string
    {
        return $this->getFinalResultFromBoardArray($board->toArray());
    }

    public function isDrawFromBoardArray(array $board): bool
    {
        return $this->getFinalResultFromBoardArray($board) === self::DRAW;
    }

    public function isDraw(Board $board): bool
    {
        return $this->isDrawFromBoardArray($board->toArray());
    }
}