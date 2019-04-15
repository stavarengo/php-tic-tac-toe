<?php
/** @noinspection PhpUnhandledExceptionInspection */
declare(strict_types=1);

namespace TicTacToe\Test\WebUi;

use PHPUnit\Framework\TestCase;
use TicTacToe\WebUi\Exception\UnableToRenderView;
use TicTacToe\WebUi\Exception\ViewFileNotFound;
use TicTacToe\WebUi\View;

class ViewTest extends TestCase
{
    /**
     * @var string
     */
    private $testViewsDir;
    /**
     * @var string
     */
    private $templateFile;
    /**
     * @var string
     */
    private $viewFile;

    public function testBasePathMethod()
    {
        $this->assertEquals('/', (new View('/', $this->testViewsDir))->basePath());
        $this->assertEquals('/app.css', (new View('/', $this->testViewsDir))->basePath('app.css'));

        $this->assertEquals('/public', (new View('/public', $this->testViewsDir))->basePath());
        $this->assertEquals('/public/app.css', (new View('/public', $this->testViewsDir))->basePath('app.css'));
    }

    public function testGetViewVariables()
    {
        $view = new View('', $this->testViewsDir);

        $value = 'value-' . __METHOD__;

        $this->assertEquals('View value: ' . $value, $view->render($this->viewFile, ['value' => $value]));

        $this->assertEquals('View value: __DEFAULT_VIEW_VALUE__', $view->render($this->viewFile, []));
    }

    public function testGetTemplateVariables()
    {
        $view = new View('', $this->testViewsDir);

        $this->assertNull($view->get('value'));
        $this->assertEquals('Undefined ' . __LINE__, $view->get('value', 'Undefined ' . __LINE__));

        $viewValue = 'value-for-view-' . __METHOD__;
        $templateValue = 'value-for-template-' . __METHOD__;

        $this->assertEquals(
            sprintf('Template content [%s]: View value: %s', $templateValue, $viewValue),
            $view->render($this->viewFile, ['value' => $viewValue], $this->templateFile, ['value' => $templateValue])
        );

        $this->assertEquals(
            sprintf('Template content [__DEFAULT_TEMPLATE_VALUE__]: View value: %s', $viewValue),
            $view->render($this->viewFile, ['value' => $viewValue], $this->templateFile, [])
        );

        $this->assertEquals(
            'Template content [__DEFAULT_TEMPLATE_VALUE__]: View value: __DEFAULT_VIEW_VALUE__',
            $view->render($this->viewFile, [], $this->templateFile, [])
        );
    }

    public function testRenderViewFileDoesNotExists()
    {
        $fakeViewName = 'fake.phtml';
        $absolutePathToFakeViewFile = $this->testViewsDir . DIRECTORY_SEPARATOR . $fakeViewName;
        $this->assertFileNotExists($absolutePathToFakeViewFile);

        $view = new View('', $this->testViewsDir);

        try {
            $view->render($fakeViewName);
            $this->fail(
                sprintf(
                    'It did not throw the exception "%s" when the view file does not exists.',
                    ViewFileNotFound::class
                )
            );
        } catch (ViewFileNotFound $e) {
            $this->assertEquals(
                sprintf('The view file "%s" does not exists.', $absolutePathToFakeViewFile),
                $e->getMessage()
            );
        }
    }

    public function testRenderViewFileExistsButTemplateFileDoesNot()
    {
        $fakeTemplateName = 'fake.phtml';
        $absolutePathToFakeTemplateFile = $this->testViewsDir . DIRECTORY_SEPARATOR . $fakeTemplateName;
        $this->assertFileNotExists($absolutePathToFakeTemplateFile);

        $view = new View('', $this->testViewsDir);

        try {
            $view->render($this->viewFile, [], $fakeTemplateName);
            $this->fail(
                sprintf(
                    'It did not throw the exception "%s" when the template file does not exists.',
                    ViewFileNotFound::class
                )
            );
        } catch (ViewFileNotFound $e) {
            $this->assertEquals(
                sprintf('The template file "%s" does not exists.', $absolutePathToFakeTemplateFile),
                $e->getMessage()
            );
        }
    }

    public function testRenderViewSuccessfully()
    {
        $view = new View('', $this->testViewsDir);

        $value = 'value-for-' . __METHOD__;

        $this->assertEquals('View value: ' . $value, $view->render($this->viewFile, ['value' => $value]));
    }

    public function testRenderViewAndTemplateSuccessfully()
    {
        $view = new View('', $this->testViewsDir);

        $viewValue = 'value-for-view-' . __METHOD__;
        $templateValue = 'value-for-template-' . __METHOD__;

        $this->assertEquals(
            sprintf('Template content [%s]: View value: %s', $templateValue, $viewValue),
            $view->render($this->viewFile, ['value' => $viewValue], $this->templateFile, ['value' => $templateValue])
        );
    }

    public function testTemplateVarsMustBeCleanAtTheEndOfTheRenderMethod()
    {
        $view = new View('', $this->testViewsDir);

        $this->assertNull($view->get('value'));

        $viewValue = 'value-for-view-' . __METHOD__;
        $templateValue = 'value-for-template-' . __METHOD__;


        $this->assertEquals("View value: $viewValue", $view->render($this->viewFile, ['value' => $viewValue]));
        $this->assertNull($view->get('value'));
        $this->assertEquals('Undefined ' . __LINE__, $view->get('value', 'Undefined ' . __LINE__));

        $this->assertEquals(
            sprintf("Template content [%s]: View value: %s", $templateValue, $viewValue),
            $view->render($this->viewFile, ['value' => $viewValue], $this->templateFile, ['value' => $templateValue])
        );
        $this->assertNull($view->get('value'));
        $this->assertEquals('Undefined ' . __LINE__, $view->get('value', 'Undefined ' . __LINE__));
    }

    public function testFailsToIncludeTheViewFile()
    {
        $view = new View('', $this->testViewsDir);
        $view->failToIncludeDuringTest = true;

        try {
            $view->render($this->viewFile);
            $this->fail(
                sprintf(
                    'It did not throw the exception "%s" when the "include" returns false.',
                    UnableToRenderView::class
                )
            );
        } catch (UnableToRenderView $e) {
            $this->assertRegExp('/Unable to render view file ".+?". File include failed./', $e->getMessage());
        }
    }


    protected function setUp(): void
    {
        $this->testViewsDir = __DIR__ . '/views';
        $this->templateFile = 'template.phtml';
        $this->viewFile = 'index.phtml';
    }
}
