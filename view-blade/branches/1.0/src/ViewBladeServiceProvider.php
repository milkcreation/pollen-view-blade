<?php

declare(strict_types=1);

namespace Pollen\ViewBlade;

use Pollen\Container\BootableServiceProvider;
use Pollen\View\ViewManagerInterface;

class ViewBladeServiceProvider extends BootableServiceProvider
{
    protected $provides = [
        ViewBladeInterface::class
    ];

    /**
     * @inheritDoc
     */
    public function boot(): void
    {
        $this->getContainer()->get(ViewBladeInterface::class);
    }

    /**
     * @inheritDoc
     */
    public function register(): void
    {
        $this->getContainer()->share(ViewBladeInterface::class, function () {
           return new ViewBlade($this->getContainer()->get(ViewManagerInterface::class), $this->getContainer());
        });
    }
}
