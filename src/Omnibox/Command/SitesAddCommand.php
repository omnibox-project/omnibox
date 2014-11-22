<?php
namespace Omnibox\Command;

use Omnibox\Model\Site;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

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
        $site = $this->getContainer()->getSiteManager()->addSite();
        $this->getContainer()->getSiteManager()->setDbInParametersYml($site, true);
    }
}
