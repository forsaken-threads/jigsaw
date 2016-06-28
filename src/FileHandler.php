<?php namespace TightenCo\Jigsaw;


use Symfony\Component\Finder\SplFileInfo;

abstract class FileHandler extends PuzzlePiece
{
    /**
     * @param SplFileInfo $file
     * @return mixed
     */
    abstract public function canHandle($file);

    /**
     * @param SplFileInfo $file
     * @param $data
     * @return mixed
     */
    abstract public function handle($file, $data);
}