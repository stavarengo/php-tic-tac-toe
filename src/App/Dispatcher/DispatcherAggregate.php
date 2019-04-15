<?php
declare(strict_types=1);


namespace TicTacToe\App\Dispatcher;


use TicTacToe\Api\Storage\StorageInterface;

class DispatcherAggregate
{
    /**
     * @var string
     */
    protected $basePath;

    /**
     * @var string
     */
    private $requestUri;

    /**
     * @var DispatcherInterface[]
     */
    protected $dispatchers;

    /**
     * DispatcherAggregate constructor.
     * @param string $basePath
     * @param string $requestUri
     * @param DispatcherInterface[] $dispatchers
     */
    public function __construct(string $basePath, string $requestUri, array $dispatchers)
    {
        $this->dispatchers = $dispatchers;
        $this->basePath = $basePath;
        $this->requestUri = $requestUri;
    }

    /**
     * @param string $basePath
     * @param string $requestUri
     * @return string
     */
    public static function getRequestRoute(string $basePath, string $requestUri): string
    {
        $basePath = rtrim($basePath, '/');

        $requestUri = parse_url($requestUri, PHP_URL_PATH);
        $requestUri = $requestUri === null ? '' : $requestUri;
        $requestUri = rtrim($requestUri, '/');

        $requestRoute = preg_replace(sprintf('~^%s~', preg_quote($basePath, '~')), '', $requestUri);
        $requestRoute = preg_replace('~^(.*?)index.php$~', '$1', $requestRoute);
        $requestRoute = '/' . ltrim($requestRoute, '/');

        return $requestRoute;
    }

    /**
     * Dispatch the request based on the $requestRoute.
     * If the dispatch does not support the $requestRoute it should return null;
     *
     * @param StorageInterface $storage
     *
     * @return DispatcherResponse|null
     *
     * @throws \Throwable
     *      It can throw any exception during execution.
     */
    public function dispatch(StorageInterface $storage): ?DispatcherResponse
    {
        $requestRoute = self::getRequestRoute($this->basePath, $this->requestUri);

        $dispatcherResponse = null;
        foreach ($this->dispatchers as $dispatcher) {
            if ($dispatcherResponse = $dispatcher->dispatch($requestRoute, $storage)) {
                return $dispatcherResponse;
            }
        }

        return null;
    }
}