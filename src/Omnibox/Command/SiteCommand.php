<?php
namespace Omnibox\Command;

use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class SiteCommand extends BaseCommand
{
    protected function configure()
    {
        $this
            ->setName('site')
            ->setDescription('Manages sites: add, remove, list')
            ->addArgument('subcommand')
        ;
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $this->validateSubcommand(array('add', 'remove', 'list'));
    }

    public function _add()
    {
        $output = $this->getContainer()->getCliHelper()->getOutputInterface();
        $output->writeln('<info>>>> Add Site <<<</info>');
        $site = $this->getContainer()->getSiteManager()->addSite();
        $this->getContainer()->getSiteManager()->setDbInParametersYml($site, true);
    }

    public function _remove()
    {
        $output = $this->getContainer()->getCliHelper()->getOutputInterface();
        $input = $this->getContainer()->getCliHelper()->getInputInterface();
        $this->getContainer()->getSiteManager()->listSites($input, $output, $this->getHelper('table'), true);
        $this->getContainer()->getSiteManager()->deleteSite($input, $output, $this->getHelper('question'));
    }

    public function _list()
    {
        $output = $this->getContainer()->getCliHelper()->getOutputInterface();
        $input = $this->getContainer()->getCliHelper()->getInputInterface();
        $this->getContainer()->getSiteManager()->listSites($input, $output, $this->getHelper('table'));
    }
}
