<?php
declare(strict_types=1);


namespace TicTacToe\App\Bot;

use TicTacToe\App\Board\Board;
use TicTacToe\App\FinalResultChecker;

/**
 * This bot uses the Minimax Algorithm to decide its next move.
 * @see https://developercoding.com/AI/minimax.php
 * @see https://thimbleby.gitlab.io/algorithm-wiki-site/wiki/minimax_search_with_alpha-beta_pruning/
 * @see https://www.geeksforgeeks.org/minimax-algorithm-in-game-theory-set-1-introduction/
 * @see https://en.wikipedia.org/wiki/Minimax
 */
class MinimaxBot implements \MoveInterface
{
    private const MAX_SCORE = PHP_INT_MAX;

    private const MIN_SCORE = PHP_INT_MIN;

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

    public static function getFinalResult(array $b): ?string
    {
        // Checking for Rows for X or O victory.
        for ($row = 0; $row < 3; $row++) {
            if ($b[$row][0] && $b[$row][0] == $b[$row][1] && $b[$row][1] == $b[$row][2]) {
                return $b[$row][0];
            }
        }

        // Checking for Columns for X or O victory.
        for ($col = 0; $col < 3; $col++) {
            if ($b[0][$col] && $b[0][$col] == $b[1][$col] && $b[1][$col] == $b[2][$col]) {
                return $b[0][$col];
            }
        }

        // Checking for Diagonals for X or O victory.
        if ($b[0][0] && $b[0][0] == $b[1][1] && $b[1][1] == $b[2][2]) {
            return $b[0][0];
        }
        if ($b[0][2] && $b[0][2] == $b[1][1] && $b[1][1] == $b[2][0]) {
            return $b[0][2];
        }

        foreach ($b as $rowIndex => $row) {
            foreach ($row as $colIndex => $col) {
                if (!$col) {
                    // There still has empty place in the board
                    return null;
                }
            }
        }

        return FinalResultChecker::DRAW;
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

        $move = $this->findBestMove($boardState, $botUnit, $playerUnit);

        $finalMove = $move;
        $finalMove[] = $botUnit;

        return $finalMove;
    }

    private function findBestMove(array $board, string $botUnit, string $humanUnit): ?array
    {
        $bestScore = self::MIN_SCORE;
        $bestMove = null;

        foreach ($board as $rowIndex => $row) {
            foreach ($row as $colIndex => $col) {
                if (!$col) {
                    // Make the move
                    $board[$rowIndex][$colIndex] = $botUnit;

                    $nextMoveScore = $this->minimax($board, 0, false, $botUnit, $humanUnit);
                    if ($nextMoveScore > $bestScore) {
                        $bestScore = $nextMoveScore;
                        $bestMove = [$rowIndex, $colIndex];
                    }

                    // Undo the move
                    $board[$rowIndex][$colIndex] = '';
                }
            }
        }

        return $bestMove;
    }

    private function minimax(
        array $board,
        int $depth,
        bool $isMaximizingPlayer,
        string $maximizingPlayer,
        string $minimizingPlayer
    ): int {
        $finalResult = MinimaxBot::getFinalResult($board);
        if ($finalResult == $maximizingPlayer) {
            $finalScore = self::MAX_SCORE;
            return $finalScore;
        }
        if ($finalResult == $minimizingPlayer) {
            $finalScore = self::MIN_SCORE;
            return $finalScore;
        }
        if ($finalResult == FinalResultChecker::DRAW) {
            return 0;
        }

        $bestScore = self::MAX_SCORE;
        if ($isMaximizingPlayer) {
            $bestScore = self::MIN_SCORE;
        }

        foreach ($board as $rowIndex => $row) {
            foreach ($row as $colIndex => $col) {
                if ($col) {
                    continue;
                }

                // Make the move
                $board[$rowIndex][$colIndex] = $isMaximizingPlayer ? $maximizingPlayer : $minimizingPlayer;

                $nextMoveScore = $this->minimax(
                    $board,
                    $depth + 1,
                    !$isMaximizingPlayer,
                    $maximizingPlayer,
                    $minimizingPlayer
                );

                // Undo the move
                $board[$rowIndex][$colIndex] = '';

                if ($isMaximizingPlayer) {
                    $bestScore = max($bestScore, $nextMoveScore);
                } else {
                    $bestScore = min($bestScore, $nextMoveScore);
                }
            }
        }

        return $bestScore;
    }
}