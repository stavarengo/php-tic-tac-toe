<?php
/** @noinspection PhpUnhandledExceptionInspection */
declare(strict_types=1);

namespace TicTacToe\Test\WebUi;

use PHPUnit\Framework\TestCase;
use TicTacToe\WebUi\BasePathDetector;
use TicTacToe\WebUi\Exception\DocumentRootIsRequired;
use TicTacToe\WebUi\Exception\PublicDirectoryPathCanNotBeRelative;
use TicTacToe\WebUi\Exception\PublicDirectoryPathIsRequired;

class BasePathDetectorTest extends TestCase
{
    public function testDocumentRootIsEmpty()
    {
        try {
            $basePathDetector = new BasePathDetector();
            $basePathDetector->detect('', '/var/www/html/public');
            $this->fail(
                sprintf(
                    'It did not throw the exception "%s" when the document root was a empty string.',
                    DocumentRootIsRequired::class
                )
            );
        } catch (DocumentRootIsRequired $e) {
            $this->assertEquals(
                'The document root is required and can not be empty.',
                $e->getMessage()
            );
        }
    }

    public function testPublicPathIsEmpty()
    {
        $basePathDetector = new BasePathDetector();

        try {
            $basePathDetector->detect('/var/www/html', '');
            $this->fail(
                sprintf(
                    'It did not throw the exception "%s" when the public directory path was a empty string.',
                    PublicDirectoryPathIsRequired::class
                    )
            );
        } catch (PublicDirectoryPathIsRequired $e) {
            $this->assertEquals(
                'The public directory path is required and can not be empty.',
                $e->getMessage()
            );
        }
    }

    public function testPublicPathIsRelative()
    {
        $listOfPublicDirectoryRelativePaths = [
            'var/www/html/public',
            '/var/www/html/../public',
            '/var/www/html/./public',
            './public',
            '../public',
            '/./public',
            '/../public',
            'public',
            '.',
            '..',
        ];

        $basePathDetector = new BasePathDetector();

        foreach ($listOfPublicDirectoryRelativePaths as $publicDirectoryRelativePath) {
            try {
                $basePathDetector->detect('/var/www/html', $publicDirectoryRelativePath);
                $this->fail(
                    sprintf(
                        'It did not throw the exception "%s" when the public directory path were "%s".',
                        PublicDirectoryPathCanNotBeRelative::class,
                        $publicDirectoryRelativePath
                    )
                );
            } catch (PublicDirectoryPathCanNotBeRelative $e) {
                $this->assertEquals(
                    'The public directory can not be relative path. Please provide the absolute path to the public directory.',
                    $e->getMessage()
                );
            }
        }
    }

    public function testGetBasePathSuccessfully()
    {
        $publicDirectoryPath = '/var/www/html/public';
        $possibilities = [
            // DOCUMENT_ROOT       => Base path to the public folder
            '/var/www/html/public' => '/',
            '/var/www/html' => '/public',
            '/var/www' => '/html/public',
            '/var' => '/www/html/public',
            '/' => '/var/www/html/public',
        ];

        $basePathDetector = new BasePathDetector();
        foreach ($possibilities as $documentRoot => $expectedBasePath) {
            $this->assertEquals(
                $expectedBasePath,
                $basePathDetector->detect($documentRoot, $publicDirectoryPath),
                sprintf('Failed when document root was "%s".', $documentRoot)
            );

            $documentRootWithSlashAtTheEnd = "$documentRoot/";
            $this->assertEquals(
                $expectedBasePath,
                $basePathDetector->detect($documentRootWithSlashAtTheEnd, $publicDirectoryPath),
                sprintf('Failed when this document root "%s" had a slash in the end.', $documentRoot)
            );

            $publicDirectoryWithSlashAtThePath = "$publicDirectoryPath/";
            $this->assertEquals(
                $expectedBasePath,
                $basePathDetector->detect($documentRoot, $publicDirectoryWithSlashAtThePath),
                sprintf('Failed when the public directory "%s" had a slash in the end.', $publicDirectoryPath)
            );

            $this->assertEquals(
                $expectedBasePath,
                $basePathDetector->detect($documentRootWithSlashAtTheEnd, $publicDirectoryWithSlashAtThePath),
                sprintf(
                    'Failed when both public directory ("%s") and document root ("%s") had a slash in the end.',
                    $publicDirectoryPath,
                    $documentRoot
                )
            );
        }
    }


    public function testBasePathShouldNotEndWithSlash()
    {
        $basePathDetector = new BasePathDetector();

        $this->assertEquals('/public', $basePathDetector->detect('/var/www/html/', '/var/www/html/public/'));
    }

    public function testTheIndexDotPhpFileMustBeStripOutTheBasePath()
    {
        $basePathDetector = new BasePathDetector();

        $this->assertEquals('/public', $basePathDetector->detect('/var/www/html', '/var/www/html/public/index.php'));
        $this->assertEquals(
            '/public/another-file.php',
            $basePathDetector->detect('/var/www/html', '/var/www/html/public/another-file.php')
        );
    }
}
