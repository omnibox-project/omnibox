<?php
namespace Uberstead\Command;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Yaml\Parser;
use Symfony\Component\Yaml\Dumper;

class SitesAddCommand extends BaseCommand
{
    protected function configure()
    {
        $this
            ->setName('sites:add')
            ->setDescription('Add a new site config')
        ;
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln('<info>>>> Add Site <<<</info>');

        $this->getContainer()->getSiteManager()->addSite($input, $output, $this->getHelper('question'));
        $this->queueVagrantProvision();
        $this->queueVagrantReload();
    }
}
