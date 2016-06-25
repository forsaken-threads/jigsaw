<?php namespace TightenCo\Jigsaw\Handlers;

use Illuminate\View\Factory;
use TightenCo\Jigsaw\FileHandler;
use TightenCo\Jigsaw\ProcessedFile;

class BladeHandler extends FileHandler
{
    protected $viewFactory;

    public function boot(Factory $viewFactory)
    {
        $this->viewFactory = $viewFactory;
    }

    public function canHandle($file)
    {
        return ends_with($file->getFilename(), '.blade.php');
    }

    public function handle($file, $data)
    {
        $filename = $file->getBasename('.blade.php') . '.html';
        return new ProcessedFile($filename, $file->getRelativePath(), $this->render($file, $data));
    }

    public function render($file, $data)
    {
        return $this->viewFactory->file($file->getRealPath(), $data)->render();
    }
}
