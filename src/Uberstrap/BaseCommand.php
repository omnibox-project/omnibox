<?php
namespace Uberstrap;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class BaseCommand extends Command
{
    public function checkConfig(InputInterface $input, OutputInterface $output)
    {
        $command = new CheckConfigCommand();
        $command->setApplication($this->getApplication());
        $command->run($input, $output);
    }

    public function runProvision(InputInterface $input, OutputInterface $output)
    {
        $command = new UbersteadProvisionCommand();
        $command->setApplication($this->getApplication());
        $command->run($input, $output);
    }
}
