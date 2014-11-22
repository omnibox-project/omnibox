<?php
namespace Omnibox\Command;

use Symfony\Component\Console\Command\Command;
use Omnibox\DependencyInjection\Container;

class BaseCommand extends Command
{
    private $commandForTerminateEvent = null;

    /**
     * @var array
     */
    protected $subcommands;

    /**
     * @var Container
     */
    protected $container;

    function __construct()
    {
        $this->subcommands = array();
        parent::__construct();
    }

    /**
     * @param $subcommands
     * @return $this
     */
    public function setSubcommands($subcommands)
    {
        $this->subcommands = $subcommands;

        return $this;
    }

    public function hasSubcommands()
    {
        return (count($this->subcommands) > 0);
    }

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

    public function runSubCommand()
    {
        $output = $this->getContainer()->getCliHelper()->getOutputInterface();
        $input = $this->getContainer()->getCliHelper()->getInputInterface();

        if (!array_key_exists($input->getArgument('subcommand'), $this->subcommands)) {
            $output->writeln('');
            $output->writeln('<comment>Usage:</comment>');
            $output->writeln(sprintf('  omnibox %s <subcommand> [<args>]', $this->getName()));
            $output->writeln('');
            $output->writeln('<comment>Available subcommands:</comment>');

            $maxCommandLength = max(array_map('strlen', array_keys($this->subcommands)));

            foreach ($this->subcommands as $command => $description) {
                $spacing = str_repeat (" " , $maxCommandLength - strlen($command) + 2);
                $output->writeln(sprintf('  <info>%s</info>%s %s', $command, $spacing, $description));
            }
            $output->writeln('');
            $output->writeln(sprintf('For help on any individual subcommand run `omnibox %s <subcommand> -h`', $this->getName()));
            die();
        }

        $this->{'_'.$input->getArgument('subcommand')}();
    }
}
