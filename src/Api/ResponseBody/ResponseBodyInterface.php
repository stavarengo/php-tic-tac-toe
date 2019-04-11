<?php
declare(strict_types=1);


namespace TicTacToe\Api\ResponseBody;


interface ResponseBodyInterface
{
    /**
     * Convert the response to its JSON representation.
     *
     * @return string
     */
    public function toJson(): string;
}