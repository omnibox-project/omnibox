<?php
namespace Omnibox\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ProvisionCommand extends BaseCommand
{
    protected function configure()
    {
        $this
            ->setName('provision')
            ->setDescription('Provisions Omnibox')
        ;
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $this->getContainer()->getVagrantManager()->provision();
    }
}
