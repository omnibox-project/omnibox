<?php
namespace Omnibox\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class OmniboxSettingsCommand extends BaseCommand
{
    protected function configure()
    {
        $this
            ->setName('omnibox:settings')
            ->setDescription('Check for ssh keys and update ip, cpu, memory')
        ;
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        // Settings are checked every time through the event dispatcher
    }
}
