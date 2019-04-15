<?php
declare(strict_types=1);


namespace TicTacToe\App\Board;


use TicTacToe\App\Board\Exception\CoordinateAlreadyInUse;
use TicTacToe\App\Board\Exception\InvalidBoardColumn;
use TicTacToe\App\Board\Exception\InvalidBoardRow;
use TicTacToe\App\Board\Exception\InvalidBoardUnit;

interface BoardInterface
{
    /**
     * @param int $row
     *      The row index (starting with zero)
     * @param int $col
     *      The column index (starting with zero)
     * @param string $unit
     * @return BoardInterface
     * @throws InvalidBoardRow
     * @throws InvalidBoardColumn
     * @throws InvalidBoardUnit
     * @throws CoordinateAlreadyInUse
     */
    public function set(int $row, int $col, string $unit): BoardInterface;

    /**
     * Return the current unit of a position in the board.
     * It returns null if the position is empty or invalid.
     *
     * @param int $row
     * @param int $col
     * @return string|null
     */
    public function get(int $row, int $col): ?string;

    /**
     * Make a place empty.
     * If the coordinate is invalid, nothing will happens.
     *
     * @param int $row
     * @param int $col
     * @return BoardInterface
     */
    public function clear(int $row, int $col): BoardInterface;

    /**
     * Return the unit used by the bot player.
     *
     * @return string
     */
    public function getBotUnit(): string;

    /**
     * Return the unit used by the human player.
     *
     * @return string
     */
    public function getHumanUnit(): string;

    /**
     * Convert the board to a 2D array of the 3x3 board.
     *
     * @return array
     */
    public function toArray(): array;
}