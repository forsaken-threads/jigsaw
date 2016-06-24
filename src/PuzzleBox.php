<?php namespace TightenCo\Jigsaw;

use Illuminate\Container\Container;

class PuzzleBox extends Container
{

    public function __construct()
    {
        $this->instance(PuzzleBox::class, $this);
    }

    public function bootConfiguredPuzzlePieces()
    {
        foreach ($this->tagged('jigsaw.puzzle-pieces') as $puzzlePiece) {
            /** @var PuzzlePiece $puzzlePiece */
            $puzzlePiece->boot();
        }
    }

    public function registerConfiguredPuzzlePieces(array $puzzlePieces = [])
    {
        $this->tag($puzzlePieces, 'jigsaw.puzzle-pieces');
        foreach ($puzzlePieces as $puzzlePiece) {
            $this->bind($puzzlePiece);
            $this->make($puzzlePiece)->register();
        }
    }
}