<?php
declare(strict_types=1);


namespace TicTacToe\Api\ResponseBody;


use TicTacToe\App\Board\Board;
use TicTacToe\App\FinalResultChecker;

class GameState implements ResponseBodyInterface
{
    /**
     * @var Board
     */
    protected $board;
    /**
     * @var FinalResultChecker
     */
    protected $finalResultChecker;

    public function __construct(?Board $board)
    {
        $this->board = $board;
        $this->finalResultChecker = new FinalResultChecker();
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
            if ($finalResults = $this->finalResultChecker->getFinalResult($this->getBoard())) {
                $winner = [
                    'result' => $finalResults,
                    'coordinates' => $this->finalResultChecker->getWinnerCoordinates($this->getBoard()),
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