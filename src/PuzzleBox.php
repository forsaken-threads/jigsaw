<?php namespace TightenCo\Jigsaw;

use Illuminate\Container\Container;

class PuzzleBox extends Container
{
    public function __construct()
    {
        $this->instance(PuzzleBox::class, $this);
    }

    public function bootPuzzlePieces()
    {
        foreach ($this->tagged('jigsaw.puzzle_piece') as $puzzlePiece) {
            if (method_exists($puzzlePiece, 'boot')) {
                $this->call([$puzzlePiece, 'boot']);
            }
            if ($puzzlePiece instanceof BuildDecorator) {
                $this->tag(get_class($puzzlePiece), 'jigsaw.build_decorator');
            }
            if ($puzzlePiece instanceof FileHandler) {
                $this->tag(get_class($puzzlePiece), 'jigsaw.file_handler');
            }
        }
    }

    public function registerPuzzlePieces(array $puzzlePieces = [])
    {
        foreach ($puzzlePieces as $puzzlePiece) {
            $this->registerPuzzlePiece($puzzlePiece);
        }
    }

    public function registerPuzzlePiece($puzzlePiece)
    {
        $this->tag($puzzlePiece, 'jigsaw.puzzle_piece');
        $instance = $this->make($puzzlePiece);
        if (method_exists($instance, 'register')) {
            $instance->register();
        }
        $this->instance($puzzlePiece, $instance);
    }
}