<?php
declare(strict_types=1);


namespace TicTacToe\App\Bot;

/**
 * This bot will always play in the first empty position it finds.
 * The main motivation of this bot, is for using in tests, but it can also be used in production.
 */
class DummyBot implements \MoveInterface
{

    /**
     * Makes a move using the actual game board state, against the player.
     *
     * $boardState contains a 2D array of the 3x3 board with the 3 possible values:
     * - X and O represents the player or the bot, as defined by $playerUnit
     * - empty string means that the field is not yet taken
     * Example:
     * [['X', 'O', '']
     * ['X', 'O', 'O']
     * [ '', '', '']]
     *
     * Returns an array containing X and Y coordinates for the next move
     * and the unit that should occupy it.
     * Example: [2, 0, 'O'] - upper right corner with O unit
     *
     * @param array $boardState
     * @param string $playerUnit
     *
     * @return array
     */
    public function makeMove(array $boardState, string $playerUnit = 'X'): array
    {
        foreach ($boardState as $rowIndex => $row) {
            foreach ($row as $colIndex => $col) {
                if (!$col) {
                    return [$rowIndex, $colIndex, $playerUnit];
                }
            }
        }

        return [-1, -1, $playerUnit];
    }
}