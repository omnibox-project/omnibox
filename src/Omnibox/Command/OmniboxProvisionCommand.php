<?php
namespace Omnibox\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class OmniboxProvisionCommand extends BaseCommand
{
    protected function configure()
    {
        $this
            ->setName('omnibox:provision')
            ->setDescription('Update hosts file and nginx config inside Omnibox')
        ;
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $this->getContainer()->getVagrantManager()->provision();
    }
}
