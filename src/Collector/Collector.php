<?php namespace TightenCo\Jigsaw\Collector;

use Illuminate\View\Compilers\BladeCompiler;
use TightenCo\Jigsaw\BuildDecorator;
use TightenCo\Jigsaw\Jigsaw;

class Collector extends BuildDecorator
{
    public function boot()
    {
        parent::boot();
        /** @var BladeCompiler $blade */
        $blade = $this->container[BladeCompiler::class];

        $blade->directive('collectitem', function($meta) {
            $meta = json_decode(substr($meta, 1, -1), true);
            $this->collectItem($meta);
            $meta = $this->makeExtractable($meta);
            $abstract = !empty($meta['collection_type']) ? $meta['collection_type'] : 'collection.posts';
            return "<?php extract($meta, EXTR_SKIP); \$item =& TightenCo\\Jigsaw\\PuzzleBox::getInstance()->make('$abstract')->currentItem(); ?>";
        });

        $blade->directive('collectindex', function($options) {
            if (empty($options)) {
                $name = 'collection';
                $abstract = 'collection.posts';
            } else {
                $options = explode(',', $options, 2);
                $name = trim($options[0]);
                $abstract = !empty(trim($options[1])) ? trim($options[1]) : 'collection.posts';
            }
            return "<?php \$$name = TightenCo\\Jigsaw\\PuzzleBox::getInstance()->make('$abstract'); ?>";
        });

    }

    public function decorate($source)
    {
    }

    public function register()
    {
        $this->container->registerPuzzlePiece(PostsProcessor::class);
        $this->container->singleton('collection.posts', Posts::class);
    }

    protected function collectItem($meta)
    {
        $collection = $this->container->make(!empty($meta['collection_type']) ? $meta['collection_type'] : 'collection.posts');
        $meta = $collection->collect($meta);
        $this->container->bind('collector.item.path.' . Jigsaw::getCurrentFile(), function() use ($meta) { return $meta['slug']; });
    }

    private function makeExtractable(array $array)
    {
        $symbols = [];
        foreach ($array as $key => $value) {
            if (!is_array($value)) {
                $symbols[] = '"' . $key . '" => "' . addslashes($value) . '"';
            } else {
                $symbols[] = $this->makeExtractable($value);
            }
        }
        return '[' . implode(', ', $symbols) . ']';
    }
}