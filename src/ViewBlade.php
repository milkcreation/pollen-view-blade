<?php

declare(strict_types=1);

namespace Pollen\ViewBlade;

use Pollen\Support\Concerns\BootableTrait;
use Pollen\Support\Proxy\ContainerProxy;
use Pollen\Support\Proxy\ViewProxy;
use Pollen\View\ViewManagerInterface;
use Psr\Container\ContainerInterface as Container;

class ViewBlade implements ViewBladeInterface
{
    use BootableTrait;
    use ContainerProxy;
    use ViewProxy;

    /**
     * @param ViewManagerInterface $viewManager
     * @param Container|null $container
     */
    public function __construct(ViewManagerInterface $viewManager, ?Container $container = null)
    {
        $this->setViewManager($viewManager);

        if ($container !== null) {
            $this->setContainer($container);
        }

        $this->boot();
    }

    /**
     * @inheritDoc
     */
    public function boot(): void
    {
        if (!$this->isBooted()) {
            $this->viewManager()->registerEngine('blade', BladeViewEngine::class);

            $this->setBooted();
        }
    }
}