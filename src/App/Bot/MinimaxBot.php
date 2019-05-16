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
class MinimaxBot implements \TicTacToe\App\Bot\BotInterface
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
        $bestScore = null;
        $bestMove = null;

        foreach ($board as $rowIndex => $row) {
            foreach ($row as $colIndex => $col) {
                if (!$col) {
                    // Make the move
                    $board[$rowIndex][$colIndex] = $botUnit;

                    $nextMoveScore = $this->minimax(
                        $board,
                        0,
                        false,
                        $botUnit,
                        $humanUnit,
                        self::MIN_SCORE,
                        self::MAX_SCORE
                    );
                    if ($bestScore === null || $nextMoveScore > $bestScore) {
                        $bestScore = $nextMoveScore;
                        $bestMove = [$rowIndex, $colIndex];
                        if ($bestScore == self::MAX_SCORE) {
                            break 2;
                        }
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
        string $minimizingPlayer,
        int $alpha,
        int $beta
    ): int {
        $finalResult = $this->finalResultChecker->getFinalResultFromBoardArray($board);
        if ($finalResult == $maximizingPlayer) {
            $finalScore = self::MAX_SCORE - $depth;
            return $finalScore;
        }
        if ($finalResult == $minimizingPlayer) {
            $finalScore = self::MIN_SCORE + $depth;
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
                    $minimizingPlayer,
                    $alpha,
                    $beta
                );

                // Undo the move
                $board[$rowIndex][$colIndex] = '';

                if ($isMaximizingPlayer) {
                    $bestScore = max($bestScore, $nextMoveScore);
                    $alpha = max($alpha, $bestScore);
                    if ($beta <= $alpha) {
                        break 2;
                    }
                } else {
                    $bestScore = min($bestScore, $nextMoveScore);
                    $beta = min($beta, $bestScore);
                    if ($beta <= $alpha) {
                        break 2;
                    }
                }
            }
        }

        return $bestScore;
    }
}