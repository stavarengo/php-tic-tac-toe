# php-tic-tac-toe

## Instructions and notes for anyone evaluating the test

- To see it in action, run the following commands and then open [http://127.0.0.1:4000](http://127.0.0.1:4000) in your 
  browser.
```bash
$ composer install
$ composer run --timeout=0 serve
```
  > You are going to need Internet connection because some CSS and JS files (like [FontAwesome] assets) are loaded from a remote server.
- The `MoveInterface`:
    - It has not been changed in any way. I did not even add a `namespace` to it.
    - Its implementation can be found in the file `src/App/Bot/MinimaxBot.php`. I used the [Minimax Algorithm]
      for this bot to decide its moves.
    - All tests for related to the `MoveInterface` can be found in the directory `test/App/Bot`.
- The application is 100% covered with tests using [PHPUnit]. To run the tests use the following code `$ composer run test`.
    - The test coverage report is ready at `test-coverage/index.html`, but if you want to generate a fresh report, run
      this code `$ composer run --timeout 0 test-coverage` and then open `test-coverage/index.html` (I warn you that
      the coverage report takes several minutes to be done, for all the possibilities the bot has to play in
      order to be sure it will never fail).
- This is a modular application, compound of three modules: 
  1. `App`: Where you can find behaviors not related to an API nor to a Web Interface, as
            the game board and the bots.
  1. `Api`: This is where reside the RESTful API. This module only has behaviors related to API requests. There you will
            find the Requests Handlers (AKA controllers), entities used in the API responses, etc.
  1. `WebUi`: The main responsibility of WebUi is to render views, but not only that. More generally, it is responsible to
              for respond all requests that the API does not know how to handle.
- No dependency on third-party code other than [PHPUnit], [Bootstrap] (CSS library) and [FontAwesome] (icon library). 
  Everything has been written by me, especially for this homework.
- I received another test as an option (refactoring test), and although I chose this project, it also contemplates some
  of the requirements from the other test, such as: OOP structure; to use the principles of S.O.L.I.D. and G.R.A.S.P;
  TDD.

**This is the end of the instruction and notes for who is evaluating the test**, but the rest of this README.md is also
interesting. I invite you to take a quick look, if you have the time.

## Getting Started

Start the project with composer:
```bash
$ composer install
```

#### Running with PHP's Built-in web server

After installing the packages, start PHP's built-in web server:
```bash
$ composer run --timeout=0 serve
```
You can then browse to [http://localhost:4000](http://localhost:4000)

If you want to start the serve using port different of 4000, you can start the server manually:
```bash
$ php -S 0.0.0.0:_YOU_PORT_ -t public/
```

> ##### Linux users
>
> On PHP versions prior to 7.1.14 and 7.2.2, this command might not work as expected due to a bug in PHP that only
> affects linux environments. In such scenarios, you will need to start the
> [built-in web server](http://php.net/manual/en/features.commandline.webserver.php) yourself using the following
> command:
> ```bash
> $ php -S 0.0.0.0:4000 -t public/ public/index.php
> ```

### WebUi Module

Bellow is a list of all the JS API and JS objects this the front-end uses.
- [https://developer.mozilla.org/en-US/docs/Web/JavaScript/Reference/Global_Objects/JSON](https://developer.mozilla.org/en-US/docs/Web/JavaScript/Reference/Global_Objects/JSON)
- [https://developer.mozilla.org/en-US/docs/Web/API/XMLHttpRequest](https://developer.mozilla.org/en-US/docs/Web/API/XMLHttpRequest)
- [https://developer.mozilla.org/en-US/docs/Web/API/Element/classList](https://developer.mozilla.org/en-US/docs/Web/API/Element/classList)
- [https://developer.mozilla.org/en-US/docs/Web/API/Element/scrollIntoView](https://developer.mozilla.org/en-US/docs/Web/API/Element/scrollIntoView)

### Api Module

The `php-tic-tac-toe` provides an RESTful API that allows you to manipulate the game. The operations you can do, through
the API, are:
- Start a new game using [`POST /api/board`];
- Set the human move using [`PUT /api/board`];
- Get the current game state using [`GET /api/board`];
- Reset/delete the current game using [`DELETE /api/board`].

<a id="the-game-state"></a>
#### Layout of the Responses: The Game State
All endpoints (except for [`DELETE /api/board`]), when completed successfully, respond with a JSON representing the 
current game state. The following is the JSON used to represent the game state.
> If the request fails, then it responds with [The Error Response Layout].
```json
{
    // The `game` attribute will be `null` when there is no game started yet.
    "game": {
    
        // The `winner` attribute will be `null` if the game does not have a winner yet.
        "winner": {
        
            // It will be the string `draw` or the unit of the player that won.
            "result":"X",
            
            // The coordinates in the board where the victory was found.
            // It will be null in case of `draw`.
            "coordinates":[[0, 0], [1, 0], [2, 0]]
        }
    },
    
    // The current board status.
    // Each position will be either an empty string or one of the units chosen by the players.
    "board": [
        ['X', '', 'O'],
        ['X', 'O', ''],
        ['X', '', 'O'],
    ],
    
    // The `units` attribute contains the units of each player.
    // For example, the "human" could be "X" and the bot "O".
    "units": { 
        "human": "X",
        "bot": "O"
    }
}
```

<a id="the-error-response-layout"></a>
#### Layout of the Responses: The Error Response
All endpoints (including [`DELETE /api/board`]), when end in failure, respond with a JSON trying to describe why the 
error happens (for example, it would fail if you forget to send a required parameter). The following is the JSON used 
to represent and error response. 
> If the request ends successfully, then it responds with [The Game State].

 ```json
{
  // This attribute will always be `true`
  "error": true,
  
  // An string containing more details about the error.
  "detail": "Missing the \"botUnit\" attribute."
}
 ```

#### API endpoints

<a id="get-board"></a>
##### `GET /api/board`
Returns de current game state.
This endpoint can be consumed even if there is no game has started yet.

**Curl example**
```bash
$ curl -X GET 'http://127.0.0.1:4000/api/board'
```

**Expected responses code**
- `200 - Success`: In this case the response body will be [The Game State].

<a id="post-board"></a>
##### `POST /api/board`
Starts a new game.
It expect that the request body contains a JSON with the following layout:
```json
{
  // The unit the human choose for this game: "X" or "O".
  "humanUnit": "X",
  // The unit the bot should use: "X" or "O".
  "botUnit": "O"
}
```

**Curl example**
```bash
$ curl -X POST 'http://127.0.0.1:4000/api/board' --data-binary '{"humanUnit": "X", "botUnit": "O"}'
```

**Expected responses code**
- `201 - Created`: It means that a new game started. In this case the response body will be [The Game State].
- `422 - Unprocessable Entity`: When there were missing parameters or invalid parameters. In this case the response body will be [The Error Response Layout].
- `409 - Conflict`: If there is already a game started. In this case the response body will be [The Error Response Layout].
- `400 - Bad Request`: If there the request could not be processed for an unexpected reason. In this case the response body will be [The Error Response Layout].

<a id="put-board"></a>
##### `PUT /api/board`
Set the human move.
This endpoint will store the human move and also perform the bot move.
It expect that the request body contains a JSON with the following layout:
```json
{
  // The attributes `row` and `column` indicates the position the human choose do move.
  // It sould be an integer greate or equals to 0, and less or equals to 2.
  "row": 0,
  "column": 2
}
```

**Curl example**  
First we need to start a game with [`POST /api/board`] and get the PHP Session ID where the game were started. Only 
after that we can set a human move using [`PUT /api/board`] endpoint and the same Session ID we get from the POST request.
```bash
$ curl -X POST 'http://127.0.0.1:4000/api/board' --data-binary '{"humanUnit": "X", "botUnit": "O"}' -H 'Cookie: PHPSESSID=1;'
$ curl -X PUT 'http://127.0.0.1:4000/api/board' --data-binary '{"row": 0, "column": 2}' -H 'Cookie: PHPSESSID=1;'
```

**Expected responses code**
- `200 - Success`: In this case the response body will be [The Game State] (already with the human and the bot move).
- `422 - Unprocessable Entity`: When there are missing parameters or invalid parameters. In this case the response body will be [The Error Response Layout].
- `400 - Bad Request`: If The bot chosen an invalid move to perform or if the move you choose can not be performed by any reason (For example, when you choose to move in a place already in use). [The Error Response Layout].
- `409 - Conflict`: If there is no game started yet or if there is a game but it is already done. In this case the response body will be [The Error Response Layout].

<a id="delete-board"></a>
##### DELETE /api/board
Delete the current game. No error will be throw if there is no game to be deleted. You can consume this endpoint even
if no game exists. After this, you will going to need to start a new game if you want to play again.

**Curl example**
```bash
$ curl -X DELETE 'http://127.0.0.1:4000/api/board'
```

**Expected responses code**
- `204 - No Content`: It means that the game was deleted, therefor you need to start a new game if you want to play again.

[Bootstrap]: https://getbootstrap.com/
[FontAwesome]: https://fontawesome.com
[PHPUnit]: https://phpunit.de/
[Minimax Algorithm]: https://en.wikipedia.org/wiki/Minimax
[`POST /api/board`]: #post-board
[`PUT /api/board`]: #put-board
[`GET /api/board`]: #get-board
[`DELETE /api/board`]: #delete-board
[The Game State]: #the-game-state
[The Error Response Layout]: #the-error-response-layout