<?php namespace TightenCo\Jigsaw;

abstract class PuzzlePiece
{
    protected $container;

    protected $defer = false;

    public function __construct(PuzzleBox $container)
    {
        $this->container = $container;
    }

    abstract public function boot();

    abstract public function register();
}