<?php
declare(strict_types=1);


namespace TicTacToe\App\Dispatcher;


use TicTacToe\Api\Storage\StorageInterface;

class DispatcherAggregate
{
    /**
     * @var string
     */
    protected $documentRoot;

    /**
     * @var DispatcherInterface[]
     */
    protected $dispatchers;
    /**
     * @var string
     */
    private $requestUri;

    /**
     * DispatcherAggregate constructor.
     * @param string $documentRoot
     * @param string $requestUri
     * @param DispatcherInterface[] $dispatchers
     */
    public function __construct(string $documentRoot, string $requestUri, array $dispatchers)
    {
        $this->dispatchers = $dispatchers;
        $this->documentRoot = $documentRoot;
        $this->requestUri = $requestUri;
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
        $publicDirectoryPath = realpath(__DIR__ . '/../../../public');
        $basePath = (new \TicTacToe\WebUi\BasePathDetector())->detect($this->documentRoot, $publicDirectoryPath);

        $requestUrlPath = parse_url($this->requestUri, PHP_URL_PATH);
        $requestRoute = preg_replace(sprintf('~^%s~', preg_quote($basePath, '~')), '', rtrim($requestUrlPath, '/'));
        $requestRoute = preg_replace('~^(.*?)index.php$~', '$1', $requestRoute);
        $requestRoute = "/$requestRoute";

        $dispatcherResponse = null;
        foreach ($this->dispatchers as $dispatcher) {
            if ($dispatcherResponse = $dispatcher->dispatch($requestRoute, $storage)) {
                return $dispatcherResponse;
            }
        }

        return null;
    }
}