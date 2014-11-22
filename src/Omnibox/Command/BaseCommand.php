<?php
namespace Omnibox\Command;

use Symfony\Component\Console\Command\Command;
use Omnibox\DependencyInjection\Container;

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

    public function validateSubcommand($availableSubCommands)
    {
        $output = $this->getContainer()->getCliHelper()->getOutputInterface();
        $input = $this->getContainer()->getCliHelper()->getInputInterface();

        if (!in_array($input->getArgument('subcommand'), $availableSubCommands)) {
            $output->writeln('');
            $output->writeln('<comment>Usage:</comment>');
            $output->writeln(sprintf('  omnibox %s <subcommand> [<args>]', $this->getName()));
            $output->writeln('');
            $output->writeln('<comment>Available subcommands:</comment>');
            foreach ($availableSubCommands as $command) {
                $output->writeln(sprintf('  <info>%s</info>', $command));
            }
            $output->writeln('');
            $output->writeln(sprintf('For help on any individual subcommand run `omnibox %s <subcommand> -h`', $this->getName()));
            die();
        }

        $this->{'_'.$input->getArgument('subcommand')}();
    }
}
