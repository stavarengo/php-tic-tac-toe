<?php
/** @noinspection PhpUnhandledExceptionInspection */
declare(strict_types=1);

namespace TicTacToe\Test\Api\RequestHandler;

use PHPUnit\Framework\TestCase;
use TicTacToe\Api\RequestHandler\PostHandler;
use TicTacToe\Api\RequestHandler\PutHandler;
use TicTacToe\Api\ResponseBody\Error;
use TicTacToe\Api\ResponseBody\GameState;
use TicTacToe\Api\Storage\ArrayStorage;
use TicTacToe\App\Board\Board;
use TicTacToe\App\Bot\DummyBot;

class PutHandlerTest extends TestCase
{
    public function testRequestWithoutBodyPayload()
    {
        $response = (new PutHandler())->handleIt(null, new ArrayStorage());

        $this->assertEquals(422, $response->getStatusCode());

        /** @var Error $responseBody */
        $responseBody = $response->getBody();
        $this->assertInstanceOf(Error::class, $responseBody);

        $this->assertEquals('Missing body content.', $responseBody->getDetail());
    }

    public function testRequestMissingAttributesOfThePayload()
    {
        // Missing the 'row' attribute
        $response = (new PutHandler())->handleIt((object)['column' => 1], new ArrayStorage());
        $this->assertEquals(422, $response->getStatusCode());

        /** @var Error $responseBody */
        $responseBody = $response->getBody();
        $this->assertInstanceOf(Error::class, $responseBody);
        $this->assertEquals('Please provide a value for the "row" attribute.', $responseBody->getDetail());

        // Missing the 'column' attribute
        $response = (new PutHandler())->handleIt((object)['row' => 1], new ArrayStorage());
        $this->assertEquals(422, $response->getStatusCode());
        $responseBody = $response->getBody();
        $this->assertInstanceOf(Error::class, $responseBody);
        $this->assertEquals('Please provide a value for the "column" attribute.', $responseBody->getDetail());
    }

    public function testRequestWithInvalidRowAndColumnValues()
    {
        // First start a game
        $storage = new ArrayStorage();
        $response = (new PostHandler())->handleIt((object)['humanUnit' => 'X', 'botUnit' => 'O'], $storage);
        $this->assertEquals(201, $response->getStatusCode());

        $invalidValue = -1;

        // The row is invalid
        $response = (new PutHandler())->handleIt((object)['row' => $invalidValue, 'column' => 1], $storage);
        $this->assertEquals(400, $response->getStatusCode());
        /** @var Error $responseBody */
        $responseBody = $response->getBody();
        $this->assertInstanceOf(Error::class, $responseBody);
        $this->assertRegExp(sprintf('/^Invalid row "%s"\..+/', $invalidValue), $responseBody->getDetail());

        // The column is invalid
        $response = (new PutHandler())->handleIt((object)['row' => 1, 'column' => $invalidValue], $storage);
        $this->assertEquals(400, $response->getStatusCode());
        $responseBody = $response->getBody();
        $this->assertInstanceOf(Error::class, $responseBody);
        $this->assertRegExp(sprintf('/^Invalid column "%s"\..+/', $invalidValue), $responseBody->getDetail());
    }

    public function testSetAMoveSuccessfully()
    {
        // First start a game
        $storage = new ArrayStorage();
        $response = (new PostHandler())->handleIt((object)['humanUnit' => 'X', 'botUnit' => 'O'], $storage);
        $this->assertEquals(201, $response->getStatusCode());

        $row = 1;
        $column = 1;

        // Set the move
        $response = (new PutHandler())->handleIt((object)['row' => $row, 'column' => $column], $storage);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertSame($storage->get(PostHandler::STORAGE_KEY_GAME_STATE), $response->getBody());
    }

    public function testTryingToMoveToAPlaceAlreadyInUse()
    {
        // First start a game
        $storage = new ArrayStorage();
        $response = (new PostHandler())->handleIt((object)['humanUnit' => 'X', 'botUnit' => 'O'], $storage);
        $this->assertEquals(201, $response->getStatusCode());

        $row = 1;
        $column = 1;

        // Set the move
        $response = (new PutHandler())->handleIt((object)['row' => $row, 'column' => $column], $storage);
        $this->assertEquals(200, $response->getStatusCode());

        // Set the move to the same place again
        $response = (new PutHandler())->handleIt((object)['row' => $row, 'column' => $column], $storage);
        $this->assertEquals(400, $response->getStatusCode());
        /** @var Error $responseBody */
        $responseBody = $response->getBody();
        $this->assertInstanceOf(Error::class, $responseBody);
        $this->assertEquals(sprintf('The position "%s,%s" is already in use.', $row, $column),
            $responseBody->getDetail());
    }

    public function testRequestWhenThereIsNoGameStartedYet()
    {
        $requestBody = (object)['row' => 1, 'column' => 1];
        $response = (new PutHandler())->handleIt($requestBody, new ArrayStorage());

        $this->assertEquals(409, $response->getStatusCode());

        /** @var Error $responseBody */
        $responseBody = $response->getBody();
        $this->assertInstanceOf(Error::class, $responseBody);

        $this->assertEquals('There is no game in progress.', $responseBody->getDetail());
    }

    public function testRequestWhenGameStateHasAnNullBoard()
    {
        // First start a game
        $storage = new ArrayStorage();
        $storage->set(PostHandler::STORAGE_KEY_GAME_STATE, new GameState(null));

        // Set the move
        $requestBody = (object)['row' => 1, 'column' => 1];
        $response = (new PutHandler())->handleIt($requestBody, $storage);

        $this->assertEquals(409, $response->getStatusCode());

        /** @var Error $responseBody */
        $responseBody = $response->getBody();
        $this->assertInstanceOf(Error::class, $responseBody);

        $this->assertEquals('There is no game in progress.', $responseBody->getDetail());
    }

    public function testBotMustMakeItsMoveAfterReceivingTheHumanMove()
    {
        $humanUnit = 'X';
        $botUnit = 'O';
        $storage = new ArrayStorage();

        // First start a game
        $response = (new PostHandler())->handleIt((object)['humanUnit' => $humanUnit, 'botUnit' => $botUnit], $storage);
        $this->assertEquals(201, $response->getStatusCode());
        /** @var GameState $gameState */
        $gameState = $response->getBody();
        $this->assertInstanceOf(GameState::class, $gameState);

        $countMoves = function (string $unitToCount, Board $board): int {
            $count = 0;
            foreach ($board->toArray() as $row) {
                $count += array_count_values($row)[$unitToCount] ?? 0;
            }

            return $count;
        };

        $board = $gameState->getBoard();
        $this->assertEquals(0, $countMoves($board->getHumanUnit(), $board));
        $this->assertEquals(0, $countMoves($board->getBotUnit(), $board));

        // Set the move
        $requestBody = (object)['row' => 2, 'column' => 2];
        $response = (new PutHandler())->handleIt($requestBody, $storage);
        $this->assertEquals(200, $response->getStatusCode());

        $gameState = $response->getBody();
        $this->assertInstanceOf(GameState::class, $gameState);

        $board = $gameState->getBoard();
        $this->assertEquals(1, $countMoves($board->getHumanUnit(), $board));
        $this->assertEquals(1, $countMoves($board->getBotUnit(), $board));
    }

    public function testHumanMakesTheLastMoveLeftInTheBoardSoBotShouldNotTryToMakeAnotherMoveAfterThat()
    {
        $botUnit = 'X';
        $humanUnit = 'O';
        $board = new Board($botUnit, $humanUnit);
        $storage = new ArrayStorage([
            PostHandler::STORAGE_KEY_GAME_STATE => new GameState($board),
        ]);

        $board->set(0, 0, $humanUnit);
        $board->set(0, 1, $botUnit);
        $board->set(0, 2, $humanUnit);
        $board->set(1, 0, $botUnit);
        $board->set(1, 1, $humanUnit);
        $board->set(1, 2, $botUnit);
        $board->set(2, 0, $humanUnit);
        $board->set(2, 1, $botUnit);

        // At this moment there is only one move left: 2, 2.
        // The human is going to choose this position an the bot should not play after that,
        // since there is not place left.
        $response = (new PutHandler())->handleIt((object)['row' => 2, 'column' => 2], $storage);
        $this->assertEquals(200, $response->getStatusCode());
    }

    public function testBotChooseToMakeAnInvalidMove()
    {
        $botUnit = 'X';
        $humanUnit = 'O';
        $board = new Board($botUnit, $humanUnit);
        $storage = new ArrayStorage([
            PostHandler::STORAGE_KEY_GAME_STATE => new GameState($board),
        ]);

        /** @var \MoveInterface $stubBot */
        $stubBot = $this->createMock(DummyBot::class);
        $stubBot->method('makeMove')
            ->willReturn([-1, -1, $botUnit]);

        // Set the move
        $requestBody = (object)['row' => 0, 'column' => 0];
        $response = (new PutHandler($stubBot))->handleIt($requestBody, $storage);

        $this->assertEquals(400, $response->getStatusCode());
        /** @var Error $responseBody */
        $responseBody = $response->getBody();
        $this->assertInstanceOf(Error::class, $responseBody);

        $this->assertRegExp('/The bot choose an invalid move\..+/', $responseBody->getDetail());
    }
}
