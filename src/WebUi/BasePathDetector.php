<?php
declare(strict_types=1);


namespace TicTacToe\WebUi;


use TicTacToe\WebUi\Exception\DocumentRootIsRequired;
use TicTacToe\WebUi\Exception\PublicDirectoryPathCanNotBeRelative;
use TicTacToe\WebUi\Exception\PublicDirectoryPathIsRequired;

class BasePathDetector
{
    /**
     * The "base path" will point to the "public" folder of the application’s root.
     *
     * You will need the base path to prepend the base URL to the URLs (usually inside an `href` attribute) in order for
     * paths to resources to be correct.
     *
     * Usage Example
     * The following assume that the base URL of the page/application is "/mypage".
     *
     * ```php
     * // Prints: <base href="/mypage/" />
     * <base href="<?= $this->basePath() ?>" />
     *
     * // Prints: <link rel="stylesheet" type="text/css" href="/mypage/css/base.css" />
     * <link rel="stylesheet" type="text/css" href="/mypage/css/base.css" />
     * ```
     *
     * @param string $documentRoot
     *      This usually will be the `$_SERVER['DOCUMENT_ROOT']`.
     *
     *      The root directory of the site defined by the 'DocumentRoot' directive in the General Section
     *      or a section e.g. `DOCUMENT_ROOT=/var/www/example`.
     *
     * @param string $publicDirectoryPath
     *      Absolute path to the public directory.
     *      Does not use relative path as it can result in unexpected behaviors.
     *
     * @return string
     *
     * @throws DocumentRootIsRequired
     * @throws PublicDirectoryPathIsRequired
     * @throws PublicDirectoryPathCanNotBeRelative
     */
    public function detect(string $documentRoot, string $publicDirectoryPath): string
    {
        if (!$documentRoot) {
            throw new DocumentRootIsRequired('The document root is required and can not be empty.');
        }

        if (!$publicDirectoryPath) {
            throw new PublicDirectoryPathIsRequired('The public directory path is required and can not be empty.');
        }

        $patternToMatchRelativePath = sprintf('~%1$s\.{1,2}%1$s~', preg_quote(DIRECTORY_SEPARATOR, '~'));
        if ($publicDirectoryPath[0] !== DIRECTORY_SEPARATOR
            || preg_match($patternToMatchRelativePath, $publicDirectoryPath)
        ) {
            throw new PublicDirectoryPathCanNotBeRelative(
                'The public directory can not be relative path. Please provide the absolute path to the public directory.'
            );
        }

        $documentRoot = rtrim($documentRoot, DIRECTORY_SEPARATOR);
        $basePath = trim(str_replace($documentRoot, '', $publicDirectoryPath), DIRECTORY_SEPARATOR);
        $basePath = preg_replace('~^(.*?)/index.php$~', '$1', $basePath);
        $basePath = trim($basePath, DIRECTORY_SEPARATOR);

        return '/' . $basePath;
    }
}