<?php

declare(strict_types=1);

namespace Pollen\ViewBlade;

use Illuminate\Filesystem\Filesystem;
use Illuminate\View\Compilers\BladeCompiler as BaseBladeCompiler;

class BladeCompiler extends BaseBladeCompiler
{
    public function __construct(Filesystem $files, ?string $cachePath = null)
    {
        if ($cachePath !== null) {
            parent::__construct($files, $cachePath);
        } else {
            $this->files = $files;
        }
    }

    /**
     * Compile the view at the given path and return contents.
     *
     * @param string|null $path
     *
     * @return string
     *
     * @throws \Illuminate\Contracts\Filesystem\FileNotFoundException
     */
    public function getCompiledContents(?string $path = null): string
    {
        if ($path) {
            $this->setPath($path);
        }

        return $this->compileString($this->files->get($this->getPath()));
    }

    /**
     * Check if cache directory exists.
     *
     * @return bool
     */
    public function hasCacheDir(): bool
    {
        return !is_null($this->cachePath);
    }
}