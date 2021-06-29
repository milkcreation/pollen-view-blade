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
use Pollen\View\ViewEngine;
use Pollen\View\Exception\MustHaveTemplateDirException;
use Pollen\View\ViewEngineInterface;
use Pollen\View\ViewExtensionInterface;

/**
 * @mixin ViewFactory
 */
class BladeViewEngine extends ViewEngine
{
    use ContainerProxy;

    protected string $fileExtension = 'blade.php';

    protected ?string $cacheDir = null;

    protected ?string $directory = null;

    protected ?string $overrideDir = null;

    protected ?array $shared = [];

    protected ?BladeCompiler $blade = null;

    protected ?EngineResolver $engineResolver = null;

    protected ?Filesystem $filesystem = null;

    private ?ViewFactory $viewFactory = null;

    public function __construct() {
        $this->filesystem = new Filesystem();

        $this->blade = new BladeCompiler($this->filesystem);

        $this->engineResolver = new EngineResolver();
        $this->engineResolver->register('blade', function () {
            return new CompilerEngine($this->blade, $this->filesystem);
        });
    }

    /**
     * Call View Factory delegate method.
     *
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
    public function addExtension(string $name, $extension): ViewEngineInterface
    {
        if ($extension === null) {
            $extension = $this->viewManager()->getExtension($name);
        }

        if ($extension instanceof ViewExtensionInterface) {
            $extension->register($this);
        } elseif (is_callable($extension)) {
            $this->blade->directive($name, $extension);
        }

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
        $this->blade->setCachePath($cacheDir);

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
    public function setFileExtension(string $fileExtension): ViewEngineInterface
    {
        $this->fileExtension = $fileExtension;
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
        $keys = is_array($key) ? $key : [$key => $value];
        foreach($keys as $k => $v) {
            $this->shared[$k] = $v;
        }
        $this->viewFactory = null;

        return $this;
    }

    public function blade(): BladeCompiler
    {
        return $this->blade;
    }

    /**
     * Create|Get Illuminate View Factory instance.
     *
     * @return ViewFactoryContract
     */
    public function viewFactory(): ViewFactoryContract
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

            $fileViewFinder = new FileViewFinder($this->filesystem, $paths, [$this->fileExtension]);

            $this->viewFactory = new ViewFactory($this->engineResolver, $fileViewFinder, new Dispatcher());

            $this->viewFactory->share($this->shared);
        }

        return $this->viewFactory;
    }
}