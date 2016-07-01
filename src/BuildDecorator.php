<?php namespace TightenCo\Jigsaw;

use Illuminate\View\Compilers\BladeCompiler;

abstract class BuildDecorator extends PuzzlePiece
{
    protected $currentPass = 0;
    public $decoratedFiles = [];
    protected $pass = 1;

    abstract public function decorate($source);

    public function boot()
    {
        /** @var BladeCompiler $blade */
        $blade = $this->container[BladeCompiler::class];

        $blade->directive('decorate', function ($pass) {
            $pass = ($pass ? substr($pass, 1, -1) : 1) - $this->currentPass;
            if ($pass) {
                $this->setDecorated(Jigsaw::getCurrentFile());
                return "@decorate($pass)";
            }
            $this->removeDecoration(Jigsaw::getCurrentFile());
            return '';
        });
    }

    public function getPass()
    {
        return $this->pass;
    }

    public function isDecorated($filename)
    {
        return in_array($filename, $this->decoratedFiles);
    }

    public function removeDecoration($filename)
    {
        if ($this->isDecorated($filename)) {
            unset($this->decoratedFiles[array_search($filename, $this->decoratedFiles)]);
        }
    }

    public function setCurrentPass($currentPass)
    {
        $this->currentPass = $currentPass;
    }

    public function setDecorated($filename)
    {
        if (!$this->isDecorated($filename)) {
            $this->decoratedFiles[] = $filename;
        }
    }
}