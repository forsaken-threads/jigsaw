<?php namespace TightenCo\Jigsaw;

abstract class BuildDecorator extends PuzzlePiece
{
    protected $currentPass = 0;
    protected $pass = 1;

    abstract public function decorate($currentPass, $source);

    public function getPass()
    {
        return $this->pass;
    }
}