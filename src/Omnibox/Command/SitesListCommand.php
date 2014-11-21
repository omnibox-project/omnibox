<?php
namespace Omnibox\Command;

use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Yaml\Parser;
use Symfony\Component\Yaml\Dumper;

class SitesListCommand extends BaseCommand
{
    protected function configure()
    {
        $this
            ->setName('sites:list')
            ->setDescription('List all site configs')
        ;
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $this->getContainer()->getSiteManager()->listSites($input, $output, $this->getHelper('table'));
    }
}
