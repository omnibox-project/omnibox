<?php
namespace Uberstead;

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
        $this->checkConfig($input, $output);
        $this->addSite($input, $output);
        $this->updateNfsShares($input, $output);
        $this->runProvision($input, $output);
    }
}
