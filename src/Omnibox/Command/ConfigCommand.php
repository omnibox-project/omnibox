<?php
namespace Omnibox\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ConfigCommand extends BaseCommand
{
    protected function configure()
    {
        $this
            ->setName('config')
            ->setDescription('Update or create Omnibox configuration')
        ;
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        // Settings are checked every time through the event dispatcher
    }
}
