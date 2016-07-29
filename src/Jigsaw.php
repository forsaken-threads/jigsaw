<?php namespace TightenCo\Jigsaw;

use Illuminate\Contracts\View\Factory;
use Illuminate\Support\Str;
use TightenCo\Jigsaw\Collector\Collector;
use TightenCo\Jigsaw\Filesystem;
use TightenCo\Jigsaw\Handlers\DefaultHandler;

class Jigsaw
{
    static protected $currentFile;

    private $files;
    private $cachePath;
    private $defaultHandler;
    private $handlers = [];
    private $options = [
        'pretty' => true
    ];
    private $decorators = [];

    static public function getCurrentFile()
    {
        return static::$currentFile;
    }

    public function __construct(Filesystem $files, $cachePath)
    {
        $this->files = $files;
        $this->cachePath = $cachePath;
    }

    public function registerHandlers($handlers)
    {
        $this->handlers = $handlers;
    }

    public function registerDefaultHandler($handler)
    {
        $this->defaultHandler = $handler;
    }

    public function registerBuildDecorators($decorators)
    {
        foreach ($decorators as $decorator) {
            /** @var BuildDecorator $decorator */
            $this->decorators[$decorator->getPass()][] = $decorator;
        }
        ksort($this->decorators);
    }

    public function build($source, $dest, $config = [])
    {
        $this->prepareDirectories([$this->cachePath, $dest]);
        $this->buildSite($source, $dest, $config, 0);
        foreach ($this->decorators as $pass => $decorators) {
            $this->files->copyDirectory($dest, $source . $pass);
            $this->prepareDirectory($dest, true);
            foreach ($decorators as $decorator) {
                /** @var BuildDecorator $decorator */
                $decorator->setCurrentPass($pass);
                $decorator->decorate($source . $pass);
            }
            $this->buildSite($source . $pass, $dest, $config, $pass);
            $this->files->deleteDirectory($source . $pass);
        }
        $this->cleanup();
    }

    public function setOption($option, $value)
    {
        $this->options[$option] = $value;
    }

    private function prepareDirectories($directories)
    {
        foreach ($directories as $directory) {
            $this->prepareDirectory($directory, true);
        }
    }

    private function prepareDirectory($directory, $clean = false)
    {
        if (! $this->files->isDirectory($directory)) {
            $this->files->makeDirectory($directory, 0755, true);
        }

        if ($clean) {
            $this->files->cleanDirectory($directory);
        }
    }

    private function buildSite($source, $dest, $config, $pass)
    {
        collect($this->files->allFiles($source))->filter(function ($file) {
            return ! $this->shouldIgnore($file);
        })->each(function ($file) use ($dest, $config, $pass) {
            $this->buildFile($file, $dest, $config, $pass);
        });
    }

    private function cleanup()
    {
        $this->files->deleteDirectory($this->cachePath);
    }

    private function shouldIgnore($file)
    {
        return preg_match('/(^_|\/_)/', $file->getRelativePathname()) === 1;
    }

    private function buildFile($file, $dest, $config, $pass)
    {
        // Quick and dirty way to globally store the current file being processed
        static::$currentFile = $file->getFilename();
        $files = $this->handle($file, $config, $pass);
        if (!is_array($files)) {
            $files = [$files];
        }
        foreach ($files as $file) {
            $directory = $this->getDirectory($file);
            $this->prepareDirectory("{$dest}/{$directory}");
            $this->files->put("{$dest}/{$this->getRelativePathname($file)}", $file->contents());
        }
    }

    private function handle($file, $config, $pass)
    {
        return $this->getHandler($file)->handle($file, $config, $pass);
    }

    private function getDirectory($file)
    {
        if ($this->options['pretty']) {
            return $this->getPrettyDirectory($file);
        }

        return $file->relativePath();
    }

    private function getPrettyDirectory($file)
    {
        if ($file->extension() === 'html' && $file->name() !== 'index.html') {
            return "{$file->relativePath()}/{$file->basename()}";
        }

        return $file->relativePath();
    }

    private function getRelativePathname($file)
    {
        if ($this->options['pretty']) {
            return $this->getPrettyRelativePathname($file);
        }

        return $file->relativePathname();
    }

    private function getPrettyRelativePathname($file)
    {
        if ($file->extension() === 'html' && $file->name() !== 'index.html') {
            return $this->getPrettyDirectory($file) . '/index.html';
        }

        return $file->relativePathname();
    }

    private function getHandler($file)
    {
        foreach ($this->handlers as $handler) {
            if ($handler->canHandle($file)) {
                return $handler;
            }
        }
        return $this->defaultHandler;
    }
}
