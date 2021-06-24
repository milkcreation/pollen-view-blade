<?php

declare(strict_types=1);

namespace Pollen\ViewBlade;

use Illuminate\Contracts\View\Factory as ViewFactoryContract;
use Illuminate\Events\Dispatcher;
use Illuminate\Filesystem\Filesystem;
use Illuminate\View\Engines\EngineResolver;
use Illuminate\View\Factory as ViewFactory;
use Illuminate\View\FileViewFinder;
use Pollen\Support\Proxy\ContainerProxy;
use Pollen\View\AbstractViewEngine;
use Pollen\View\Exception\MustHaveTemplateDirException;
use Pollen\View\ViewEngineInterface;

/**
 * @mixin ViewFactory
 */
class BladeViewEngine extends AbstractViewEngine
{
    use ContainerProxy;

    protected ?string $cacheDir = null;

    protected ?string $directory = null;

    protected ?string $overrideDir = null;

    private ?ViewFactory $viewFactory = null;

    /**
     * @param string $method
     * @param array $parameters
     *
     * @return mixed
     */
    public function __call(string $method, array $parameters)
    {
        return $this->viewFactory()->$method(...$parameters);
    }

    /**
     * @inheritDoc
     */
    public function addFunction(string $name, callable $function): ViewEngineInterface
    {
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function exists(string $name): bool
    {
        return $this->viewFactory()->exists($name);
    }

    /**
     * @inheritDoc
     */
    public function render(string $name, array $datas = []): string
    {
        return $this->viewFactory()->make($name, $datas)->render();
    }

    /**
     * @inheritDoc
     */
    public function setCacheDir(?string $cacheDir = null): ViewEngineInterface
    {
        $this->cacheDir = $cacheDir;
        $this->viewFactory = null;

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function setDirectory(string $directory): ViewEngineInterface
    {
        $this->directory = $directory;
        $this->viewFactory = null;

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function setOverrideDir(string $overrideDir): ViewEngineInterface
    {
        $this->overrideDir = $overrideDir;
        $this->viewFactory = null;

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function share($key, $value = null): ViewEngineInterface
    {
        $this->viewFactory()->share($key, $value);

        return $this;
    }

    /**
     * Instantiate|Get Illuminate View Factory.
     *
     * @return ViewFactoryContract
     */
    protected function viewFactory(): ViewFactoryContract
    {
        if ($this->viewFactory === null) {
            if ($this->directory === null) {
                throw new MustHaveTemplateDirException(self::class);
            }

            $paths = [];
            if ($this->overrideDir !== null) {
                $paths[] = $this->overrideDir;
            }
            $paths[] = $this->directory;

            $filesystem = new Filesystem();

            $engineResolver = new EngineResolver();

            $engineResolver->register('blade', function () use ($filesystem) {
                $bladeCompiler = new BladeCompiler($filesystem, $this->cacheDir);
                return new CompilerEngine($bladeCompiler, $filesystem);
            });
            $fileViewFinder = new FileViewFinder($filesystem, $paths);
            $eventDispatcher = new Dispatcher();

            $this->viewFactory = new ViewFactory($engineResolver, $fileViewFinder, $eventDispatcher);
        }

        return $this->viewFactory;
    }
}