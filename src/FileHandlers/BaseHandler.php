<?php

namespace Katana\FileHandlers;

use Katana\Markdown;
use Symfony\Component\Finder\SplFileInfo;
use Illuminate\Filesystem\Filesystem;
use Illuminate\View\Factory;

class BaseHandler
{
    protected $filesystem;
    protected $viewFactory;

    /**
     * The view file.
     *
     * @var SplFileInfo
     */
    protected $file;

    /**
     * The path to the blade view.
     *
     * @var string
     */
    protected $viewPath;

    /**
     * Data to be passed to every view.
     *
     * @var array
     */
    public $viewsData = [];

    /**
     * AbstractHandler constructor.
     *
     * @param Filesystem $filesystem
     */
    public function __construct(Filesystem $filesystem, Factory $viewFactory)
    {
        $this->filesystem = $filesystem;
        $this->viewFactory = $viewFactory;
    }

    /**
     * Convert a blade view into a site page.
     *
     * @param SplFileInfo $file
     *
     * @return void
     */
    public function handle(SplFileInfo $file)
    {
        $this->file = $file;

        $this->viewPath = $this->getViewPath();

        if (@$this->viewsData['enableBlog'] && @$this->viewsData['postsListView'] == $this->viewPath) {
            $this->prepareBlogIndexViewData();
        }

        $content = $this->getFileContent();

        $this->filesystem->put(
            sprintf(
                '%s/%s',
                $this->prepareAndGetDirectory(),
                in_array($file->getExtension(), ['php', 'md']) ? 'index.html' : $file->getFilename()
            ),
            $content
        );
    }

    /**
     * Get the content of the file after rendering.
     *
     * @param SplFileInfo $file
     *
     * @return string
     */
    private function getFileContent()
    {
        if (ends_with($this->file->getFilename(), '.blade.php')) {
            return $this->renderBlade();
        } elseif (ends_with($this->file->getFilename(), '.md')) {
            return $this->renderMarkdown();
        }

        return $this->file->getContents();
    }

    /**
     * Render the blade file.
     *
     * @return string
     */
    protected function renderBlade()
    {
        return $this->viewFactory->make($this->viewPath, $this->viewsData)->render();
    }

    /**
     * Render the markdown file.
     *
     * @return string
     */
    protected function renderMarkdown()
    {
        return Markdown::parseWithYAML($this->file->getContents());
    }

    /**
     * Prepare and get the directory name for pretty URLs.
     *
     * @return string
     */
    private function prepareAndGetDirectory()
    {
        $directory = $this->getDirectoryPrettyName();

        if (! $this->filesystem->isDirectory($directory)) {
            $this->filesystem->makeDirectory($directory, 0755, true);
        }

        return $directory;
    }

    /**
     * Generate directory path to be used for the file pretty name.
     *
     * @return string
     */
    protected function getDirectoryPrettyName()
    {
        $fileBaseName = $this->file->getBasename('.'.$this->file->getExtension());

        $fileRelativePath = $this->file->getRelativePath();

        if (in_array($this->file->getExtension(), ['php', 'md']) && $fileBaseName != 'index') {
            $fileRelativePath .= $fileRelativePath ? "/$fileBaseName" : $fileBaseName;
        }

        return KATANA_PUBLIC_DIR.($fileRelativePath ? "/$fileRelativePath" : '');
    }

    /**
     * Get the path of the view.
     *
     * @return string
     */
    private function getViewPath()
    {
        return str_replace(['.blade.php', '.md'], '', $this->file->getRelativePathname());
    }

    /**
     * Prepare the data for the blog landing page.
     *
     * We will pass only the first n posts and a next page path.
     *
     * @return void
     */
    private function prepareBlogIndexViewData()
    {
        $postsPerPage = @$this->viewsData['postsPerPage'] ?: 5;

        $this->viewsData['nextPage'] = count($this->viewsData['blogPosts']) > $postsPerPage ? '/blog-page/2' : null;

        $this->viewsData['previousPage'] = null;

        $this->viewsData['paginatedBlogPosts'] = array_slice($this->viewsData['blogPosts'], 0, $postsPerPage, true);
    }
}