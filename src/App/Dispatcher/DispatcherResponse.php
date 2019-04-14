<?php
declare(strict_types=1);


namespace TicTacToe\App\Dispatcher;


class DispatcherResponse
{
    /**
     * @var int
     */
    protected $statusCode;
    /**
     * @var string[]
     */
    protected $headers;
    /**
     * @var string
     */
    protected $content;

    /**
     * DispatcherResponse constructor.
     * @param int $statusCode
     * @param string[] $headers
     * @param string $content
     */
    public function __construct(int $statusCode, ?string $content, array $headers = [])
    {
        $this->statusCode = $statusCode;
        $this->headers = $headers;
        $this->content = $content;
    }

    /**
     * @return int
     */
    public function getStatusCode(): int
    {
        return $this->statusCode;
    }

    /**
     * @return string[]
     */
    public function getHeaders(): array
    {
        return $this->headers;
    }

    /**
     * @return string
     */
    public function getContent(): ?string
    {
        return $this->content;
    }

}