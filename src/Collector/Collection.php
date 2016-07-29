<?php namespace TightenCo\Jigsaw\Collector;


interface Collection
{
    public function collect($meta);

    public function &currentItem();
}