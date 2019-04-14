<?php
declare(strict_types=1);


namespace TicTacToe\WebUi;


use TicTacToe\Api\RequestHandler\PostHandler;
use TicTacToe\Api\Storage\StorageInterface;
use TicTacToe\App\Dispatcher\DispatcherInterface;
use TicTacToe\App\Dispatcher\DispatcherResponse;

class Dispatcher implements DispatcherInterface
{
    /**
     * @var string
     */
    protected $documentRoot;

    /**
     * Dispatcher constructor.
     * @param string $documentRoot
     */
    public function __construct(string $documentRoot)
    {
        $this->documentRoot = $documentRoot;
    }

    /**
     * @param string $documentRoot
     * @param \Throwable $e
     * @return DispatcherResponse
     * @throws Exception\DocumentRootIsRequired
     * @throws Exception\PublicDirectoryPathCanNotBeRelative
     * @throws Exception\PublicDirectoryPathIsRequired
     * @throws Exception\UnableToRenderView
     * @throws Exception\UnexpectedErrorWhileRenderingView
     * @throws Exception\ViewFileNotFound
     */
    public static function getError500Response(string $documentRoot, \Throwable $e): DispatcherResponse
    {
        $content = self::getView($documentRoot)->render('500.phtml', ['exception' => $e], '_template.phtml');

        return new \TicTacToe\App\Dispatcher\DispatcherResponse(
            500,
            $content,
            ['Content-Type' => 'text/html; charset=UTF-8']
        );
    }

    /**
     * @param string $documentRoot
     * @return DispatcherResponse
     * @throws Exception\DocumentRootIsRequired
     * @throws Exception\PublicDirectoryPathCanNotBeRelative
     * @throws Exception\PublicDirectoryPathIsRequired
     * @throws Exception\UnableToRenderView
     * @throws Exception\UnexpectedErrorWhileRenderingView
     * @throws Exception\ViewFileNotFound
     */
    public static function getError404Response(string $documentRoot): DispatcherResponse
    {
        $content = self::getView($documentRoot)->render('404.phtml', [], '_template.phtml');

        return new \TicTacToe\App\Dispatcher\DispatcherResponse(
            404,
            $content,
            ['Content-Type' => 'text/html; charset=UTF-8']
        );
    }

    /**
     * @param string $documentRoot
     * @return View
     * @throws Exception\DocumentRootIsRequired
     * @throws Exception\PublicDirectoryPathCanNotBeRelative
     * @throws Exception\PublicDirectoryPathIsRequired
     */
    private static function getView(string $documentRoot): View
    {
        $publicDirectoryPath = realpath(__DIR__ . '/../../public');
        $basePath = (new \TicTacToe\WebUi\BasePathDetector())->detect($documentRoot, $publicDirectoryPath);

        return new \TicTacToe\WebUi\View($basePath, __DIR__ . '/views');
    }

    /**
     * Dispatch the request based on the $requestRoute.
     * It should return a response if, and only if, the $requestRoute is one of its route.
     * If the dispatch does not support the $requestRoute it should return null;
     *
     * @param string $requestRoute
     *      The route requested.
     *
     * @param StorageInterface $storage
     *
     * @return DispatcherResponse|null
     *
     * @throws \Throwable
     *      It can throw any exception during execution.
     */
    public function dispatch(string $requestRoute, StorageInterface $storage): ?DispatcherResponse
    {
        if ($requestRoute != '/') {
            return null;
        }

        $viewVariables = [];

        $board = $storage->get(PostHandler::STORAGE_KEY_GAME_BOARD);
        $templateVariables = [
            'gameState' => $board ? new \TicTacToe\Api\ResponseBody\GameState($board) : null,
        ];

        $content = self::getView($this->documentRoot)->render('index.phtml', $viewVariables, '_template.phtml', $templateVariables);

        return new DispatcherResponse(200, $content, ['Content-Type' => 'text/html; charset=UTF-8']);
    }
}