<?php
declare(strict_types=1);


namespace TicTacToe\App;


use TicTacToe\App\Board\Board;

class FinalResultChecker
{
    public const DRAW = 'draw';

    public function getWinnerCoordinatesFromBoardArray(array $board): ?array
    {
        if (!Board::isValidBoard($board)) {
            return null;
        }

        // Checking Rows
        for ($row = 0; $row < 3; $row++) {
            if ($board[$row][0] && $board[$row][0] == $board[$row][1] && $board[$row][1] == $board[$row][2]) {
                return [[$row, 0], [$row, 1], [$row, 2]];
            }
        }

        // Checking Columns
        for ($col = 0; $col < 3; $col++) {
            if ($board[0][$col] && $board[0][$col] == $board[1][$col] && $board[1][$col] == $board[2][$col]) {
                return [[0, $col], [1, $col], [2, $col]];
            }
        }

        // Checking Diagonals
        if ($board[0][0] && $board[0][0] == $board[1][1] && $board[1][1] == $board[2][2]) {
            return [[0, 0], [1, 1], [2, 2]];
        }
        if ($board[0][2] && $board[0][2] == $board[1][1] && $board[1][1] == $board[2][0]) {
            return [[0, 2], [1, 1], [2, 0]];
        }

        return null;
    }

    public function getWinnerCoordinates(Board $board): ?array
    {
        return $this->getWinnerCoordinatesFromBoardArray($board->toArray());
    }

    public function getFinalResultFromBoardArray(array $board): ?string
    {
        if (!Board::isValidBoard($board)) {
            return null;
        }

        if ($winnerCoordinates = $this->getWinnerCoordinatesFromBoardArray($board)) {
            $firstRow = $winnerCoordinates[0][0];
            $firstCol = $winnerCoordinates[0][1];

            return $board[$firstRow][$firstCol];
        }

        foreach ($board as $rowIndex => $row) {
            foreach ($row as $colIndex => $col) {
                if (!$col) {
                    // There still has empty place in the board
                    return null;
                }
            }
        }

        // No winner, no spaces lefts
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