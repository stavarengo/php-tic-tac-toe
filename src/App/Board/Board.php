<?php
declare(strict_types=1);


namespace TicTacToe\App\Board;


use TicTacToe\App\Board\Exception\CoordinateAlreadyInUse;
use TicTacToe\App\Board\Exception\InvalidBoardColumn;
use TicTacToe\App\Board\Exception\InvalidBoardRow;
use TicTacToe\App\Board\Exception\InvalidBoardUnit;

class Board implements BoardInterface
{
    public const VALID_UNITS = ['O', 'X'];

    /**
     * @var array[]
     */
    protected $boardState = [
        ['', '', ''],
        ['', '', ''],
        ['', '', ''],
    ];

    /**
     * @var string[]
     */
    protected $validUnits;
    /**
     * @var ?string
     */
    private $botUnit;
    /**
     * @var ?string
     */
    private $humanUnit;

    /**
     * Board constructor.
     * @param string $botUnit
     * @param string $humanUnit
     * @throws InvalidBoardUnit
     */
    public function __construct(string $botUnit = self::VALID_UNITS[0], string $humanUnit = self::VALID_UNITS[1])
    {
        if (!self::isValidUnit($botUnit) || !self::isValidUnit($humanUnit)) {
            throw new InvalidBoardUnit(
                sprintf('Please use one of the following units: "%s".', implode('", "', self::VALID_UNITS))
            );
        }

        if ($botUnit == $humanUnit) {
            throw new InvalidBoardUnit(
                sprintf('The units must be different. You set both to "%s".', $botUnit)
            );
        }

        $this->botUnit = $botUnit;
        $this->humanUnit = $humanUnit;
    }

    /**
     * Checks whether the array is a valid board or not.
     * To be valid, the $board must be a 2D array of the 3x3 board, where the keys are always 0, 1 and 2.
     * This is an example of a valid board:
     *      [
     *          ['', '', ''],
     *          ['', '', ''],
     *          ['', '', ''],
     *      ]
     *
     * @param array $board
     * @return bool
     */
    public static function isValidBoard(array $board): bool
    {
        $isValid = isset($board[0][0]) && isset($board[0][1]) && isset($board[0][2])
            && isset($board[1][0]) && isset($board[1][1]) && isset($board[1][2])
            && isset($board[2][0]) && isset($board[2][1]) && isset($board[2][2]);

        return $isValid;
    }

    /**
     * Check whether the $unit can be used as a player unit or not.
     * @param string $unit
     * @return bool
     * @see \TicTacToe\App\Board\Board::VALID_UNITS
     */
    public static function isValidUnit(string $unit): bool
    {
        return in_array($unit, self::VALID_UNITS);
    }

    public function __toString()
    {
        return implode(
            "\n",
            array_map(
                function ($row) {
                    return ($row[0] ?: '_') . ' ' . ($row[1] ?: '_') . ' ' . ($row[2] ?: '_');
                },
                $this->toArray()
            )
        );
    }

    /**
     * @param int $row
     * @param int $col
     * @param string $unit
     * @return BoardInterface
     * @throws InvalidBoardRow
     * @throws InvalidBoardUnit
     * @throws InvalidBoardColumn
     * @throws CoordinateAlreadyInUse
     */
    public function set(int $row, int $col, string $unit): BoardInterface
    {
        if ($unit != $this->botUnit && $unit != $this->humanUnit) {
            throw new InvalidBoardUnit(
                sprintf(
                    'Invalid unit "%s". Please use one of the following values: "%s".',
                    $unit,
                    implode('", "', array_keys($this->boardState))
                )
            );
        }

        if (!array_key_exists($row, $this->boardState)) {
            throw new InvalidBoardRow(
                sprintf(
                    'Invalid row "%s". Please use one of the following values: "%s".',
                    $row,
                    implode('", "', array_keys($this->boardState))
                )
            );
        }

        if (!array_key_exists($col, $this->boardState[$row])) {
            throw new InvalidBoardColumn(
                sprintf(
                    'Invalid column "%s". Please use one of the following values: "%s".',
                    $col,
                    implode('", "', array_keys($this->boardState[$row]))
                )
            );
        }

        if ($this->boardState[$row][$col]) {
            throw new CoordinateAlreadyInUse(sprintf('The position "%s,%s" is already in use.', $row, $col));
        }

        $this->boardState[$row][$col] = $unit;

        return $this;
    }

    public function clear(int $row, int $col): BoardInterface
    {
        $this->boardState[$row][$col] = '';

        return $this;
    }

    public function get(int $row, int $col): ?string
    {
        if (!isset($this->boardState[$row][$col])) {
            return null;
        }

        if (!$this->boardState[$row][$col]) {
            return null;
        }

        return $this->boardState[$row][$col];
    }

    public function toArray(): array
    {
        return $this->boardState;
    }

    public function getBotUnit(): string
    {
        return $this->botUnit;
    }

    public function getHumanUnit(): string
    {
        return $this->humanUnit;
    }
}