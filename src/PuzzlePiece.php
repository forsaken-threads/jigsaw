<?php namespace TightenCo\Jigsaw;

class PuzzlePiece
{
    protected $container;

    public function __construct(PuzzleBox $container)
    {
        $this->container = $container;
    }

}