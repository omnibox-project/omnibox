<?php

namespace Uberstead\Container;

use Uberstead\Container\Container;

trait ContainerAwareTrait
{
    /**
     * @var Container
     */
    protected $container;

    /**
     * Returns the container.
     *
     * @return Container
     */
    public function getContainer()
    {
        return $this->container;
    }

    /**
     * Sets the container.
     *
     * @param Container|null $container The container or null
     * @return void
     */
    public function setContainer(Container $container = null)
    {
        $this->container = $container;
    }
}
