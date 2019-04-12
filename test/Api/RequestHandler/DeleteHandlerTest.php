<?php
/** @noinspection PhpUnhandledExceptionInspection */
declare(strict_types=1);

namespace TicTacToe\Test\Api\RequestHandler;

use PHPUnit\Framework\TestCase;
use TicTacToe\Api\RequestHandler\DeleteHandler;
use TicTacToe\Api\RequestHandler\PostHandler;
use TicTacToe\Api\ResponseBody\GameState;
use TicTacToe\Api\Storage\ArrayStorage;
use TicTacToe\App\Board\Board;

class DeleteHandlerTest extends TestCase
{
    public function testDeleteWhenThereIsNotGameStarted()
    {
        $storage = new ArrayStorage();
        $response = (new DeleteHandler())->handleIt(null, $storage);

        $this->assertFalse($storage->has(PostHandler::STORAGE_KEY_GAME_STATE));

        $this->assertEquals(204, $response->getStatusCode());
        $this->assertNull($response->getBody());

        $this->assertFalse($storage->has(PostHandler::STORAGE_KEY_GAME_STATE));
    }

    public function testDeleteGameSuccessfully()
    {
        $storage = new ArrayStorage([
            PostHandler::STORAGE_KEY_GAME_STATE => new GameState(new Board()),
        ]);

        $this->assertTrue($storage->has(PostHandler::STORAGE_KEY_GAME_STATE));

        $response = (new DeleteHandler())->handleIt(null, $storage);

        $this->assertEquals(204, $response->getStatusCode());
        $this->assertNull($response->getBody());

        $this->assertFalse($storage->has(PostHandler::STORAGE_KEY_GAME_STATE));
    }
}
