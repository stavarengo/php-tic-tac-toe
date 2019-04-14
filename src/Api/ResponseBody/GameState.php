<?php
declare(strict_types=1);


namespace TicTacToe\Api\ResponseBody;


use TicTacToe\App\Board\Board;
use TicTacToe\App\Bot\MinimaxBot;

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
        $winner = null;

        if ($this->board) {
            if ($finalResults = MinimaxBot::getFinalResult($this->getBoard()->toArray())) {
                $winner = [
                    'result' => $finalResults,
                    'coordinates' => MinimaxBot::getWinnerCoordinates($this->getBoard()->toArray()),
                ];
            }
            $gameStateAsArray['game'] = [
                "winner" => $winner,
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