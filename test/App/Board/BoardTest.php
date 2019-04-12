<?php
/** @noinspection PhpUnhandledExceptionInspection */
declare(strict_types=1);

namespace TicTacToe\Test\App\Board;

use PHPUnit\Framework\TestCase;
use TicTacToe\App\Board\Board;
use TicTacToe\App\Board\Exception\CoordinateAlreadyInUse;
use TicTacToe\App\Board\Exception\InvalidBoardColumn;
use TicTacToe\App\Board\Exception\InvalidBoardRow;
use TicTacToe\App\Board\Exception\InvalidBoardUnit;

class BoardTest extends TestCase
{
    public function testThePlayerUnitsMustBeOnlyOneOfTheLetters_X_or_O()
    {
        $validUnit1 = 'X';
        $validUnit2 = 'O';
        $validUnit1ByteCode = ord($validUnit1);
        $validUnit2ByteCode = ord($validUnit2);

        // First make sure that we can use the letters 'X' and 'O'
        new Board($validUnit1, $validUnit2);
        new Board($validUnit2, $validUnit1);

        $failIfBoardAcceptsThisPairOfUnits = function ($unit1ByteCode, $unit2ByteCode) {
            $unit1 = chr($unit1ByteCode);
            $unit2 = chr($unit2ByteCode);
            try {
                new Board($unit1, $unit2);
                $this->fail(
                    sprintf(
                        'The board accepted the characters "%s" and "%s" (byte code "%s" and "%s") as player unit. It should had thrown the exception "%s".',
                        $unit1,
                        $unit2,
                        $unit1ByteCode,
                        $unit2ByteCode,
                        InvalidBoardUnit::class
                    )
                );
            } catch (InvalidBoardUnit $e) {
                $this->assertRegExp('/^Please use one of the following units:.+/', $e->getMessage());
            }
        };

        // Now try to use invalid letters.
        $invalidUnitByteCode = 0;
        do {
            if (!in_array($invalidUnitByteCode, [$validUnit1ByteCode, $validUnit2ByteCode])) {
                $failIfBoardAcceptsThisPairOfUnits($invalidUnitByteCode, $validUnit1ByteCode);
                $failIfBoardAcceptsThisPairOfUnits($validUnit1ByteCode, $invalidUnitByteCode);

                $failIfBoardAcceptsThisPairOfUnits($invalidUnitByteCode, $validUnit2ByteCode);
                $failIfBoardAcceptsThisPairOfUnits($validUnit2ByteCode, $invalidUnitByteCode);
            }

            $invalidUnitByteCode++;
        } while ($invalidUnitByteCode < 256);
    }

    public function testBoardMustThrowExceptionWhenUnitsAreEquals()
    {
        // First make sure it does not throw exceptions when the units are different.
        new Board('X', 'O');

        try {
            new Board('X', 'X');
            $this->fail('The board accepted that both players choose the same unit.');
        } catch (InvalidBoardUnit $e) {
            $this->assertRegExp('/The units must be different\..+/', $e->getMessage());
        }

    }

    public function testSetMustThrowInvalidUnitException()
    {
        $unit1 = 'X';
        $unit2 = 'O';
        $board = new Board($unit1, $unit2);

        // First make sure it does not throw exceptions when using valid units.
        $board->set(0, 0, $unit1);
        $board->set(0, 1, $unit2);

        $invalidUnit = 'z';
        $this->assertFalse(
            in_array($invalidUnit, [$unit1, $unit2]),
            sprintf(
                'In other to this test to work properly the value "%s" should not be one of "%" and "%s".',
                $invalidUnit,
                $unit1,
                $unit2
            )
        );

        $this->expectException(InvalidBoardUnit::class);
        $board->set(0, 2, $invalidUnit);
    }

    public function testSetMustThrowExceptionWhenRowIndexIsInvalid()
    {
        $unit1 = 'O';
        $board = new Board($unit1, 'X');

        // First make sure it does not throw exceptions when using valid values.
        $board->set(0, 0, $unit1);
        $board->set(1, 0, $unit1);
        $board->set(2, 0, $unit1);


        try {
            $invalidValue = -1;
            $board->set($invalidValue, 0, $unit1);
            $this->fail(sprintf('Expected exception "%s" was not throw', InvalidBoardRow::class));
        } catch (InvalidBoardRow $e) {
            $this->assertRegExp(sprintf('/Invalid row "%s".+/', $invalidValue), $e->getMessage());
        }

        try {
            $invalidValue = 3;
            $board->set($invalidValue, 0, $unit1);
            $this->fail(sprintf('Expected exception "%s" was not throw', InvalidBoardRow::class));
        } catch (InvalidBoardRow $e) {
            $this->assertRegExp(sprintf('/Invalid row "%s".+/', $invalidValue), $e->getMessage());
        }
    }

    public function testSetMustThrowExceptionWhenColumnIndexIsInvalid()
    {
        $unit1 = 'O';
        $board = new Board($unit1, 'X');

        // First make sure it does not throw exceptions when using valid values.
        $board->set(0, 0, $unit1);
        $board->set(0, 1, $unit1);
        $board->set(0, 2, $unit1);


        try {
            $invalidValue = -1;
            $board->set(0, $invalidValue, $unit1);
            $this->fail(sprintf('Expected exception "%s" was not throw', InvalidBoardColumn::class));
        } catch (InvalidBoardColumn $e) {
            $this->assertRegExp(sprintf('/Invalid column "%s".+/', $invalidValue), $e->getMessage());
        }

        try {
            $invalidValue = 3;
            $board->set(0, $invalidValue, $unit1);
            $this->fail(sprintf('Expected exception "%s" was not throw', InvalidBoardColumn::class));
        } catch (InvalidBoardColumn $e) {
            $this->assertRegExp(sprintf('/Invalid column "%s".+/', $invalidValue), $e->getMessage());
        }
    }

    public function testBoardMustThrowExceptionWhenTryToSetTheSameCoordinateTwice()
    {
        $unit1 = 'O';
        $board = new Board($unit1, 'X');

        // First make sure it does not throw exceptions when set values to empty coordinates.
        for ($row = 0; $row < 3; $row++) {
            for ($column = 0; $column < 3; $column++) {
                $board->set($row, $column, $unit1);
            }
        }

        // Now try to set all the positions again. All of them must throw a exception
        for ($row = 0; $row < 3; $row++) {
            for ($column = 0; $column < 3; $column++) {
                try {
                    $board->set($row, $column, $unit1);
                    $this->fail(sprintf('Expected exception "%s" was not throw', CoordinateAlreadyInUse::class));
                } catch (CoordinateAlreadyInUse $e) {
                    $this->assertEquals(
                        sprintf('The position "%s,%s" is already in use.', $row, $column),
                        $e->getMessage()
                    );
                }
            }
        }
    }

    public function testTheGetMethodMustReturnNullIfThePositionIsInvalid()
    {
        $board = new Board();

        $this->assertNull($board->get(-1, 0));
        $this->assertNull($board->get(0, -1));
        $this->assertNull($board->get(3, 0));
        $this->assertNull($board->get(0, 3));
    }

    public function testTheGetMethodMustReturnNullIfThePositionIsEmpty()
    {
        $board = new Board();

        // First make sure it does not throw exceptions when set values to empty coordinates.
        for ($row = 0; $row < 3; $row++) {
            for ($column = 0; $column < 3; $column++) {
                $this->assertNull($board->get($row, $column));
            }
        }
    }

    public function testBoardMustStoreTheCorrectUnitReceivedInTheParameter()
    {
        $units = ['X', 'O'];
        $board = new Board($units[0], $units[1]);

        // First make sure it does not throw exceptions when set values to empty coordinates.
        for ($row = 0; $row < 3; $row++) {
            for ($column = 0; $column < 3; $column++) {
                $unit = $units[array_rand($units)];

                $board->set($row, $column, $unit);

                $this->assertEquals($unit, $board->get($row, $column));
            }
        }
    }
}
