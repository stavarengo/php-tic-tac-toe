<?php
declare(strict_types=1);

namespace TicTacToe\Test\Api\RequestHandler;

use PHPUnit\Framework\TestCase;
use TicTacToe\Api\RequestHandler\GetHandler;
use TicTacToe\Api\RequestHandler\PostHandler;
use TicTacToe\Api\ResponseBody\GameState;
use TicTacToe\Api\Storage\ArrayStorage;
use TicTacToe\App\Board\Board;

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
                'humanUnit' => Board::VALID_UNITS[0],
                'botUnit' => Board::VALID_UNITS[1],
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
        $this->assertSame($storage->get(PostHandler::STORAGE_KEY_GAME_BOARD), $responseBody->getBoard());
    }
}
