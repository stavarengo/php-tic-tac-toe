<?php
declare(strict_types=1);

namespace TicTacToe\Test\Api\RequestHandler;

use PHPUnit\Framework\TestCase;
use TicTacToe\Api\RequestHandler\GetHandler;
use TicTacToe\Api\RequestHandler\PostHandler;
use TicTacToe\Api\ResponseBody\GameState;
use TicTacToe\Api\Storage\ArrayStorage;

class GetHandlerTest extends TestCase
{
    public function testRequestWhenThereIsNotGameStartedYet()
    {
        $response = (new GetHandler())->handleIt(null, new ArrayStorage());

        $this->assertEquals(200, $response->getStatusCode());

        /** @var GameState $responseBody */
        $responseBody = $response->getBody();
        $this->assertInstanceOf(GameState::class, $responseBody);
        $this->assertNull($responseBody->getBoard());
    }

    public function testRequestWhenThereIsGameStarted()
    {
        $storage = new ArrayStorage();

        // First start a game
        $response = (new PostHandler())->handleIt(
            (object)[
                'humanUnit' => 'X',
                'botUnit' => 'O',
            ],
            $storage
        );
        $this->assertEquals(201, $response->getStatusCode());

        // Now test the GetHandler
        $response = (new GetHandler())->handleIt(null, $storage);
        $this->assertEquals(200, $response->getStatusCode());

        /** @var GameState $responseBody */
        $responseBody = $response->getBody();
        $this->assertInstanceOf(GameState::class, $responseBody);
        $this->assertNotNull($responseBody->getBoard());
    }
}
