<?php namespace TightenCo\Jigsaw\Handlers;

use TightenCo\Jigsaw\Filesystem;
use TightenCo\Jigsaw\ProcessedFile;
use TightenCo\Jigsaw\PuzzlePiece;

class DefaultHandler extends PuzzlePiece
{
    private $files;

    public function boot(Filesystem $files)
    {
        $this->files = $files;
    }

    public function handle($file, $data)
    {
        return new ProcessedFile($file->getFilename(), $file->getRelativePath(), $this->files->get($file->getRealPath()));
    }
}
