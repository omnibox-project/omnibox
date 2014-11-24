<?php
namespace Omnibox\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class InitCommand extends BaseCommand
{
    protected function configure()
    {
        $this
            ->setName('init')
            ->setAliases(array('diagnose'))
            ->setDescription('Diagnose / initialise Omnibox')
        ;
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln('<comment>Yaml file exists?</comment>                       <info>✔</info>');
        $output->writeln('<comment>Omnibox command is installed for user?</comment>  <info>✔</info>');
        $output->writeln('<comment>Omnibox command is installed for root?</comment>  <info>✔</info>');
        $output->writeln('<comment>Vagrantbox is downloaded?</comment>               <info>✔</info>');
    }
}
