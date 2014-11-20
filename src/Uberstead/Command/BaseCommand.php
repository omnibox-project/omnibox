<?php
namespace Uberstead\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\ProgressHelper;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Symfony\Component\Process\Process;
use Symfony\Component\Yaml\Dumper;
use Symfony\Component\Yaml\Parser;
use Symfony\Component\Console\Question\Question;
use Uberstead\Container\ContainerAwareTrait;

class BaseCommand extends Command
{
    use ContainerAwareTrait;

    private $commandForTerminateEvent = null;

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
