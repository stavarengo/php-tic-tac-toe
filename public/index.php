<?php

declare(strict_types=1);

chdir(dirname(__DIR__));

if (!file_exists(__DIR__ . '/../vendor/autoload.php')) {
    $msg = 'Did you forgot to run `composer install`?' . PHP_EOL . 'Unable to load the "./vendor/autoload.php".';
    http_response_code(500);
    echo "<pre>$msg</pre>";
    throw new RuntimeException($msg);
}
require __DIR__ . '/../vendor/autoload.php';

/**
 * I'm using a self-called anonymous function to create its own scope and keep the the variables created here away from
 * the global scope.
 */
(function () {
    $basePath = null;
    $getBasePath = function() use (&$basePath) {
        if (!$basePath) {
            $basePath = (new \TicTacToe\WebUi\BasePathDetector())->detect($_SERVER['DOCUMENT_ROOT'], __DIR__);
        }
        return $basePath;
    };
    try {
        session_start();
        $storage = new \TicTacToe\Api\Storage\PhpSessionStorage();
        $dispatcherAggregate = new \TicTacToe\App\Dispatcher\DispatcherAggregate(
            $getBasePath(),
            $_SERVER['REQUEST_URI'],
            [
                new \TicTacToe\Api\Dispatcher($_SERVER['REQUEST_METHOD']),
                new \TicTacToe\WebUi\Dispatcher($getBasePath()),
            ]
        );

        $dispatcherResponse = $dispatcherAggregate->dispatch($storage);
        if (!$dispatcherResponse) {
            $dispatcherResponse = \TicTacToe\WebUi\Dispatcher::getError404Response($getBasePath());
        }
    } catch (\Throwable $e) {
        $dispatcherResponse = \TicTacToe\WebUi\Dispatcher::getError500Response($getBasePath(), $e);
    }

    http_response_code($dispatcherResponse->getStatusCode());
    foreach ($dispatcherResponse->getHeaders() as $headerName => $hederValue) {
        header("$headerName: $hederValue");
    }
    echo $dispatcherResponse->getContent();
})();
