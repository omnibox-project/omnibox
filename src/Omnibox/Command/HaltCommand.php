<?php
namespace Omnibox\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class HaltCommand extends BaseCommand
{
    protected function configure()
    {
        $this
            ->setName('halt')
            ->setDescription('Stop Omnibox')
        ;
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $this->getContainer()->getVagrantManager()->halt();
    }
}
