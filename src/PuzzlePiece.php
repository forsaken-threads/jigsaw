<?php namespace TightenCo\Jigsaw;

abstract class PuzzlePiece
{
    protected $container;

    public function __construct(PuzzleBox $container)
    {
        $this->container = $container;
    }

    abstract public function boot();

    abstract public function register();
}