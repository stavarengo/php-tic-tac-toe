<?php
declare(strict_types=1);


namespace TicTacToe\WebUi;


use TicTacToe\WebUi\Exception\UnableToRenderView;
use TicTacToe\WebUi\Exception\ViewFileNotFound;

class View
{
    /**
     * @var string
     * @see \TicTacToe\WebUi\BasePathDetector for more information.
     */
    protected $basePath;
    /**
     * @var array
     */
    private $viewVariables;
    /**
     * @var string
     */
    private $viewsDirectory;

    public $failToIncludeDuringTest = false;

    /**
     * View constructor.
     * @param string $basePath
     *      The request base path. See {@link \TicTacToe\WebUi\BasePathDetector} for more information.
     * @param string $viewsDirectory
     *      Where are the view stored.
     */
    public function __construct(string $basePath, string $viewsDirectory)
    {
        $this->basePath = $basePath;
        $this->viewsDirectory = $viewsDirectory;
    }

    /**
     * Return a value of the {@link \TicTacToe\WebUi\View::$vars}, if it exists.
     * If not exists, return $default
     *
     * @param $name
     * @param null $default
     * @return mixed|null
     */
    public function get($name, $default = null)
    {
        return $this->viewVariables[$name] ?? $default;
    }

    /**
     * @param string|null $path
     * @return string
     */
    public function basePath(?string $path = null): string
    {
        if (!$path) {
            return $this->basePath;
        }

        return rtrim($this->basePath, '/') . '/' . ltrim($path, '/');
    }

    /**
     * @param string $viewFileName
     *      The view relative file path.
     *      This path should be relative to the directory {@link \TicTacToe\WebUi\View::$viewsDirectory}.
     *
     * @param array $viewVariables
     *      Variables that your $viewFileName file needs.
     *
     * @param string|null $templateViewFileName
     *      The view relative file path that will be used as template for this rendering.
     *      The template view can get the $viewFileName rendering result access the 'content' variable.
     *      For example. Inside your template file, you can write this:
     *      ```html
     *          <!doctype html>
     *          <html lang="en">
     *          <head>
     *              <title>Default Title</title>
     *          </head>
     *          <body>
     *              <?php echo $this->get('content') ?>
     *          </body>
     *          </html>
     *      ```
     *
     * @param array $templateVariables
     *      Any extra variables you want to pass to you template view.
     *      Keep in mind that the variable 'content' is reserved, thus if you create a variable with this name, its
     *      contents will be replace by the $viewFileName render result.
     *
     * @return string
     *      The rendered view already inside the template HTML, if there is a template.
     *
     * @throws UnableToRenderView
     * @throws ViewFileNotFound
     */
    public function render(
        string $viewFileName,
        array $viewVariables = [],
        ?string $templateViewFileName = null,
        array $templateVariables = []
    ): string {
        try {
            $this->viewVariables = $viewVariables;
            $absoluteViewFilePath = $this->viewsDirectory . DIRECTORY_SEPARATOR . $viewFileName;
            $content = $this->getViewContents($absoluteViewFilePath, false);

            if ($templateViewFileName) {
                $this->viewVariables = $templateVariables;
                $this->viewVariables['content'] = $content;
                $absoluteViewFilePath = $this->viewsDirectory . DIRECTORY_SEPARATOR . $templateViewFileName;
                $content = $this->getViewContents($absoluteViewFilePath, true);
            }

            return $content;
        } catch (UnableToRenderView $e) {
            throw $e;
        } catch (ViewFileNotFound $e) {
            throw $e;
        } finally {
            $this->viewVariables = [];
        }
    }

    /**
     * @param string $absoluteViewFilePath
     * @return string
     * @throws UnableToRenderView
     * @throws ViewFileNotFound
     */
    private function getViewContents(string $absoluteViewFilePath, bool $isTemplate): string
    {
        if (!file_exists($absoluteViewFilePath)) {
            throw new ViewFileNotFound(
                sprintf(
                    'The %s file "%s" does not exists.',
                    $isTemplate ? 'template' : 'view',
                    $absoluteViewFilePath
                )
            );
        }

        ob_start();
        $includeReturn = false;
        if (!$this->failToIncludeDuringTest) {
            $includeReturn = include $absoluteViewFilePath;
        }
        $content = ob_get_clean();

        if ($includeReturn === false && empty($content)) {
            throw new UnableToRenderView(sprintf(
                'Unable to render view file "%s". File include failed.',
                $absoluteViewFilePath
            ));
        }

        return $content;
    }
}