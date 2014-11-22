<?php
namespace Omnibox\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ReloadCommand extends BaseCommand
{
    protected function configure()
    {
        $this
            ->setName('reload')
            ->setDescription('Restarts Omnibox and reloads configuration')
        ;
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $this->getContainer()->getVagrantManager()->reload();
    }
}
