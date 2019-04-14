<?php
declare(strict_types=1);

namespace TicTacToe\Test\Api\RequestHandler;

use PHPUnit\Framework\TestCase;
use TicTacToe\Api\RequestHandler\PostHandler;
use TicTacToe\Api\ResponseBody\Error;
use TicTacToe\Api\ResponseBody\GameState;
use TicTacToe\Api\Storage\ArrayStorage;
use TicTacToe\App\Board\Board;

class PostHandlerTest extends TestCase
{
    public function testRequestWithNotBody()
    {
        $requestBody = null;
        $requestHandler = new PostHandler();
        $response = $requestHandler->handleIt($requestBody, new ArrayStorage());

        $this->assertEquals(422, $response->getStatusCode());

        /** @var Error $responseBody */
        $responseBody = $response->getBody();
        $this->assertInstanceOf(Error::class, $responseBody);

        $this->assertEquals('Missing body content.', $responseBody->getDetail());
    }

    public function testRequestMissingTheHumanUnitAttribute()
    {
        $requestBody = (object)[
            'humanUnit-invalid-key' => Board::VALID_UNITS[0],
            'botUnit' => Board::VALID_UNITS[1],
        ];

        $requestHandler = new PostHandler();
        $response = $requestHandler->handleIt($requestBody, new ArrayStorage());

        $this->assertEquals(422, $response->getStatusCode());

        /** @var Error $responseBody */
        $responseBody = $response->getBody();
        $this->assertInstanceOf(Error::class, $responseBody);

        $this->assertEquals('Missing the "humanUnit" attribute.', $responseBody->getDetail());
    }

    public function testRequestMissingTheBotUnitAttribute()
    {
        $requestBody = (object)[
            'humanUnit' => Board::VALID_UNITS[0],
            'botUnit-invalid-key' => Board::VALID_UNITS[1],
        ];

        $requestHandler = new PostHandler();
        $response = $requestHandler->handleIt($requestBody, new ArrayStorage());

        $this->assertEquals(422, $response->getStatusCode());

        /** @var Error $responseBody */
        $responseBody = $response->getBody();
        $this->assertInstanceOf(Error::class, $responseBody);

        $this->assertEquals('Missing the "botUnit" attribute.', $responseBody->getDetail());
    }

    public function testRequestWithTheHumanUnitAttributeEmpty()
    {
        $requestBody = (object)[
            'humanUnit' => '',
            'botUnit' => Board::VALID_UNITS[0],
        ];

        $requestHandler = new PostHandler();
        $response = $requestHandler->handleIt($requestBody, new ArrayStorage());

        $this->assertEquals(422, $response->getStatusCode());

        /** @var Error $responseBody */
        $responseBody = $response->getBody();
        $this->assertInstanceOf(Error::class, $responseBody);

        $this->assertEquals('Please provide a value for the "humanUnit" attribute.', $responseBody->getDetail());
    }

    public function testRequestWithTheBotUnitAttributeEmpty()
    {
        $requestBody = (object)[
            'humanUnit' => Board::VALID_UNITS[0],
            'botUnit' => '',
        ];

        $requestHandler = new PostHandler();
        $response = $requestHandler->handleIt($requestBody, new ArrayStorage());

        $this->assertEquals(422, $response->getStatusCode());

        /** @var Error $responseBody */
        $responseBody = $response->getBody();
        $this->assertInstanceOf(Error::class, $responseBody);

        $this->assertEquals('Please provide a value for the "botUnit" attribute.', $responseBody->getDetail());
    }

    public function testStartNewGameSuccessfully()
    {
        $requestBody = (object)[
            'humanUnit' => Board::VALID_UNITS[0],
            'botUnit' => Board::VALID_UNITS[1],
        ];

        $requestHandler = new PostHandler();
        $response = $requestHandler->handleIt($requestBody, new ArrayStorage());

        $this->assertEquals(201, $response->getStatusCode());

        /** @var GameState $responseBody */
        $responseBody = $response->getBody();
        $this->assertInstanceOf(GameState::class, $responseBody);

        $this->assertEquals($requestBody->humanUnit, $responseBody->getBoard()->getHumanUnit());
        $this->assertEquals($requestBody->botUnit, $responseBody->getBoard()->getBotUnit());
    }

    public function testTryToStartNewGameWhenThereIsAnotherGameStaredAlready()
    {
        $requestBody = (object)[
            'humanUnit' => Board::VALID_UNITS[0],
            'botUnit' => Board::VALID_UNITS[1],
        ];
        $storage = new ArrayStorage();
        $requestHandler = new PostHandler();

        // First we start a game
        $response = $requestHandler->handleIt($requestBody, $storage);
        $this->assertEquals(201, $response->getStatusCode());

        // Now we try to start another game without deleting the last one
        $response = $requestHandler->handleIt($requestBody, $storage);
        $this->assertEquals(409, $response->getStatusCode());

        /** @var Error $responseBody */
        $responseBody = $response->getBody();
        $this->assertInstanceOf(Error::class, $responseBody);

        $this->assertEquals(
            'There is already another game in progress. To start a new game you must delete the one currently in progress.',
            $responseBody->getDetail()
        );
    }

    public function testTryToStartNewGameWithBothUnitsEquals()
    {
        $requestBody = (object)[
            'humanUnit' => Board::VALID_UNITS[0],
            'botUnit' => Board::VALID_UNITS[0],
        ];
        $requestHandler = new PostHandler();

        $response = $requestHandler->handleIt($requestBody, new ArrayStorage());

        $this->assertEquals(422, $response->getStatusCode());

        /** @var Error $responseBody */
        $responseBody = $response->getBody();
        $this->assertInstanceOf(Error::class, $responseBody);

        $this->assertEquals(
            sprintf('The units must be different. You set both to "%s".', Board::VALID_UNITS[0]),
            $responseBody->getDetail()
        );
    }

    public function testTryToStartNewGameWithInvalidUnits()
    {
        $invalidUnit1 = null;
        $invalidUnit2 = null;

        foreach (['a', 'b', 'c', 'd', 'e', 'f', 'g', 'h', 'i', 'j', 'k', 'l'] as $possibleInvalidUnit) {
            if (!Board::isValidUnit($possibleInvalidUnit)) {
                if (!$invalidUnit1) {
                    $invalidUnit1 = $possibleInvalidUnit;
                } else if (!$invalidUnit2) {
                    $invalidUnit2 = $possibleInvalidUnit;
                }
                if ($invalidUnit1 && $invalidUnit2 && $invalidUnit1 != $invalidUnit1) {
                    break;
                }
            }
        }
        $this->assertNotNull($invalidUnit1, 'Could not figure out an invalid unit to use in this test.');
        $this->assertNotNull($invalidUnit2, 'Could not figure out an invalid unit to use in this test.');
        $this->assertNotEquals($invalidUnit1, $invalidUnit2, 'I need two different invalid units to use in this test.');

        $requestBody = (object)[
            'humanUnit' => $invalidUnit1,
            'botUnit' => $invalidUnit2,
        ];
        $requestHandler = new PostHandler();

        $response = $requestHandler->handleIt($requestBody, new ArrayStorage());

        $this->assertEquals(422, $response->getStatusCode());

        /** @var Error $responseBody */
        $responseBody = $response->getBody();
        $this->assertInstanceOf(Error::class, $responseBody);

        $this->assertEquals(
            sprintf('Please use one of the following units: "%s".', implode('", "', Board::VALID_UNITS)),
            $responseBody->getDetail()
        );
    }

}
