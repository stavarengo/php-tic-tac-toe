<?php
declare(strict_types=1);


namespace TicTacToe\App\Board;


use TicTacToe\App\Board\Exception\CoordinateAlreadyInUse;
use TicTacToe\App\Board\Exception\InvalidBoardColumn;
use TicTacToe\App\Board\Exception\InvalidBoardRow;
use TicTacToe\App\Board\Exception\InvalidBoardUnit;

class Board implements BoardInterface
{
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
    public function __construct(string $botUnit = 'O', string $humanUnit = 'X')
    {
        $validUnits = ['X', 'O'];
        if (!in_array($botUnit, $validUnits) || !in_array($humanUnit, $validUnits)) {
            throw new InvalidBoardUnit(
                sprintf('Please use one of the following units: "%s".', implode('", "', $validUnits))
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