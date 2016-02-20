<?php
namespace Omnibox\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class UpCommand extends BaseCommand
{
    protected function configure()
    {
        $this
            ->setName('up')
            ->setDescription('Start and provision Omnibox')
        ;
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $this->getContainer()->getVagrantManager()->up();
    }
}
