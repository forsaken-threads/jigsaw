<?php namespace TightenCo\Jigsaw\Collector;

use Illuminate\View\Compilers\BladeCompiler;
use Illuminate\View\Factory;
use Symfony\Component\Finder\SplFileInfo;
use TightenCo\Jigsaw\Handlers\BladeHandler;
use TightenCo\Jigsaw\Jigsaw;
use TightenCo\Jigsaw\ProcessedFile;

class Paginator extends BladeHandler
{
    protected $currentFile;
    protected $iterators;
    protected $viewFactory;

    public function boot(Factory $viewFactory)
    {
        $this->viewFactory = $viewFactory;
        $this->viewFactory->addExtension('paginator.process.php', 'blade');

        /** @var BladeCompiler $blade */
        $blade = $this->container[BladeCompiler::class];

        $blade->directive('paginate', function($args) {
            $args = $args ? explode(',', substr($args, 1, -1)) : [];
            $pageMaker = !empty($args[0]) ? trim($args[0]) : 'paginateByTags';
            $abstract = !empty($args[1]) ? trim($args[1]) : 'collection.posts';
            $this->iterators[$this->currentFile] = [$abstract, $pageMaker];
            return '';
        });
    }

    public function canHandle($file)
    {
        return ends_with($file->getFilename(), '.paginator.process.php');
    }

    public function handle($file, $data, $pass = 0)
    {
        $this->currentFile = $file->getFilename();
        // This is the first time around.  We are simply registering which iterator goes with this particular file
        if ($pass == 0) {
            $contents = $this->render($file, $data);
            $filename = $file->getBasename(); //$pass > 0 ? $file->getBasename('.paginator.process.php') . '.html' :
            $path = $file->getRelativePath();
            return new ProcessedFile($filename, $path, $contents);
        }
        // This is the second pass.  Time to iterate!
        $processedFiles = [];
        $collection = $this->container->make($this->iterators[$this->currentFile][0])->{$this->iterators[$this->currentFile][1]}();
        foreach ($collection as $page => $items) {
            $contents = $this->render($file, array_merge($data, ['page' => $page, 'items' => $items]));
            $filename = 'index.html';
            $path = $file->getRelativePath() . '/' . str_slug($page);
            $processedFiles[] = new ProcessedFile($filename, $path, $contents);
        }
        return $processedFiles;
    }

    /**
     * @param SplFileInfo $file
     * @param $data
     * @return string
     */
    public function render($file, $data)
    {
        return $this->viewFactory->file($file->getRealPath(), $data)->render();
    }

}