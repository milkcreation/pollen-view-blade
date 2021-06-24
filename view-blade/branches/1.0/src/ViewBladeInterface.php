<?php

declare(strict_types=1);

namespace Pollen\ViewBlade;

use Pollen\Support\Concerns\BootableTraitInterface;
use Pollen\Support\Proxy\ContainerProxyInterface;

interface ViewBladeInterface extends BootableTraitInterface, ContainerProxyInterface
{
    /**
     * Booting.
     *
     * @return void
     */
    public function boot(): void;
}