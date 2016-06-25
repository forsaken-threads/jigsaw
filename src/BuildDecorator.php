<?php namespace TightenCo\Jigsaw;

abstract class BuildDecorator extends PuzzlePiece
{
    protected $pass = 1;

    abstract public function decorate($source);

    public function getPass()
    {
        return $this->pass;
    }
}