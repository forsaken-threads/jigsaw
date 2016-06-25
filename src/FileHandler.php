<?php namespace TightenCo\Jigsaw;


abstract class FileHandler extends PuzzlePiece
{
    abstract public function canHandle($file);

    abstract public function handle($file, $data);
}