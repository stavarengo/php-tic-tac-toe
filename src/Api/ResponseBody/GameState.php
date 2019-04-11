<?php
declare(strict_types=1);


namespace TicTacToe\Api\ResponseBody;


use TicTacToe\App\Board\Board;

class GameState implements ResponseBodyInterface
{
    /**
     * @var Board
     */
    protected $board;

    public function __construct(?Board $board)
    {
        $this->board = $board;
    }

    /**
     * @return Board
     */
    public function getBoard(): ?Board
    {
        return $this->board;
    }

    public function toJson(): string
    {
        $gameStateAsArray = [
            'game' => null,
        ];

        if ($this->board) {
            $gameStateAsArray['game'] = [
                "winner" => null,
                "board" => $this->getBoard()->toArray(),
                "units" => [
                    "human" => $this->getBoard()->getHumanUnit(),
                    "bot" => $this->getBoard()->getBotUnit(),
                ],
            ];
        }

        return json_encode($gameStateAsArray);
    }
}