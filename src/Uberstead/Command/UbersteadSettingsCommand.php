<?php
namespace Uberstead\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class UbersteadSettingsCommand extends BaseCommand
{
    protected function configure()
    {
        $this
            ->setName('uberstead:settings')
            ->setDescription('Check for ssh keys and update ip, cpu, memory')
        ;
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        // Settings are checked every time through the event dispatcher
    }
}
