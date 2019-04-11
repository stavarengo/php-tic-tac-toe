<?php
declare(strict_types=1);

namespace TicTacToe\Test\Api\ResponseBody;

use PHPUnit\Framework\TestCase;

class ErrorTest extends TestCase
{
    public function testGetDetailMethod()
    {
        $detail = 'Test 1';
        $error = new \TicTacToe\Api\ResponseBody\Error($detail);

        $this->assertEquals($detail, $error->getDetail());
    }

    public function testToJsonMethod()
    {
        $detail = 'Test 1';
        $error = new \TicTacToe\Api\ResponseBody\Error($detail);

        $this->assertJsonStringEqualsJsonString(json_encode(['error' => true, 'detail' => $detail]), $error->toJson());
    }
}
