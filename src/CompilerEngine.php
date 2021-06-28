<?php

declare(strict_types=1);

namespace Pollen\ViewBlade;

use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\View\Engines\CompilerEngine as BaseCompilerEngine;

class CompilerEngine extends BaseCompilerEngine
{
    /**
     * @var BladeCompiler
     */
    protected $compiler;

    /**
     * @inheritDoc
     *
     * @throws FileNotFoundException
     */
    public function get($path, array $data = []): string
    {
        if (!$this->compiler->hasCachePath()) {
            $contents =  $this->compiler->getCompiledContents($path);

            ob_get_level();

            ob_start();
            extract($data, EXTR_OVERWRITE);

            eval('?>'.$contents.'<?php');

            return ltrim(ob_get_clean());
        }

        return parent::get($path, $data);
    }
}