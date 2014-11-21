<?php
namespace Uberstead\Command;

use Symfony\Component\Console\Command\Command;
use Uberstead\DependencyInjection\Container;

class BaseCommand extends Command
{
    private $commandForTerminateEvent = null;

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

    /**
     * @return null
     */
    public function getCommandForTerminateEvent()
    {
        return $this->commandForTerminateEvent;
    }

    /**
     * @param null $commandForTerminateEvent
     */
    public function setCommandForTerminateEvent($commandForTerminateEvent)
    {
        $this->commandForTerminateEvent = $commandForTerminateEvent;
    }
}
