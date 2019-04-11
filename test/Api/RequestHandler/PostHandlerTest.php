<?php
declare(strict_types=1);

namespace TicTacToe\Test\Api\RequestHandler;

use PHPUnit\Framework\TestCase;
use TicTacToe\Api\RequestHandler\PostHandler;
use TicTacToe\Api\ResponseBody\Error;
use TicTacToe\Api\ResponseBody\GameState;
use TicTacToe\Api\Storage\ArrayStorage;

class PostHandlerTest extends TestCase
{
    public function testRequestWithNotBody()
    {
        $requestBody = null;
        $requestHandler = new PostHandler();
        $response = $requestHandler->handleIt($requestBody, new ArrayStorage());

        $this->assertEquals(400, $response->getStatusCode());

        /** @var Error $responseBody */
        $responseBody = $response->getBody();
        $this->assertInstanceOf(Error::class, $responseBody);

        $this->assertEquals('Missing body content.', $responseBody->getDetail());
    }

    public function testRequestMissingTheHumanUnitAttribute()
    {
        $requestBody = (object)[
            'humanUnit-invalid-key' => 'X',
            'botUnit' => 'O',
        ];

        $requestHandler = new PostHandler();
        $response = $requestHandler->handleIt($requestBody, new ArrayStorage());

        $this->assertEquals(400, $response->getStatusCode());

        /** @var Error $responseBody */
        $responseBody = $response->getBody();
        $this->assertInstanceOf(Error::class, $responseBody);

        $this->assertEquals('Missing the "humanUnit" attribute.', $responseBody->getDetail());
    }

    public function testRequestMissingTheBotUnitAttribute()
    {
        $requestBody = (object)[
            'humanUnit' => 'X',
            'botUnit-invalid-key' => 'O',
        ];

        $requestHandler = new PostHandler();
        $response = $requestHandler->handleIt($requestBody, new ArrayStorage());

        $this->assertEquals(400, $response->getStatusCode());

        /** @var Error $responseBody */
        $responseBody = $response->getBody();
        $this->assertInstanceOf(Error::class, $responseBody);

        $this->assertEquals('Missing the "botUnit" attribute.', $responseBody->getDetail());
    }

    public function testRequestWithTheHumanUnitAttributeEmpty()
    {
        $requestBody = (object)[
            'humanUnit' => '',
            'botUnit' => 'O',
        ];

        $requestHandler = new PostHandler();
        $response = $requestHandler->handleIt($requestBody, new ArrayStorage());

        $this->assertEquals(400, $response->getStatusCode());

        /** @var Error $responseBody */
        $responseBody = $response->getBody();
        $this->assertInstanceOf(Error::class, $responseBody);

        $this->assertEquals('Please provide a value for the "humanUnit" attribute.', $responseBody->getDetail());
    }

    public function testRequestWithTheBotUnitAttributeEmpty()
    {
        $requestBody = (object)[
            'humanUnit' => 'X',
            'botUnit' => '',
        ];

        $requestHandler = new PostHandler();
        $response = $requestHandler->handleIt($requestBody, new ArrayStorage());

        $this->assertEquals(400, $response->getStatusCode());

        /** @var Error $responseBody */
        $responseBody = $response->getBody();
        $this->assertInstanceOf(Error::class, $responseBody);

        $this->assertEquals('Please provide a value for the "botUnit" attribute.', $responseBody->getDetail());
    }

    public function testStartNewGameSuccessfully()
    {
        $requestBody = (object)[
            'humanUnit' => 'X',
            'botUnit' => 'O',
        ];

        $requestHandler = new PostHandler();
        $response = $requestHandler->handleIt($requestBody, new ArrayStorage());

        $this->assertEquals(201, $response->getStatusCode());

        /** @var GameState $responseBody */
        $responseBody = $response->getBody();
        $this->assertInstanceOf(GameState::class, $responseBody);

        $this->assertNull($responseBody->getWinner());
        $this->assertEquals($requestBody->humanUnit, $responseBody->getBoard()->getHumanUnit());
        $this->assertEquals($requestBody->botUnit, $responseBody->getBoard()->getBotUnit());
    }

    public function testTryToStartNewGameWhenThereIsAnotherGameStaredAlready()
    {
        $requestBody = (object)[
            'humanUnit' => 'X',
            'botUnit' => 'O',
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
            'There already another game in progress. To start a new game you must delete the one currently in progress.',
            $responseBody->getDetail()
        );
    }

    public function testTryToStartNewGameWithBothUnitsEquals()
    {
        $requestBody = (object)[
            'humanUnit' => 'X',
            'botUnit' => 'X',
        ];
        $requestHandler = new PostHandler();

        $response = $requestHandler->handleIt($requestBody, new ArrayStorage());

        $this->assertEquals(400, $response->getStatusCode());

        /** @var Error $responseBody */
        $responseBody = $response->getBody();
        $this->assertInstanceOf(Error::class, $responseBody);

        $this->assertEquals(
            'The units must be different. You set both to "X".',
            $responseBody->getDetail()
        );
    }

    public function testTryToStartNewGameWithInvalidUnitsEquals()
    {
        $requestBody = (object)[
            'humanUnit' => 'a',
            'botUnit' => 'b',
        ];
        $requestHandler = new PostHandler();

        $response = $requestHandler->handleIt($requestBody, new ArrayStorage());

        $this->assertEquals(400, $response->getStatusCode());

        /** @var Error $responseBody */
        $responseBody = $response->getBody();
        $this->assertInstanceOf(Error::class, $responseBody);

        $this->assertEquals('Please use one of the following units: "X", "O".', $responseBody->getDetail());
    }

}
