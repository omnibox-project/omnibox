<?php
namespace Uberstead\Command;

use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ChoiceQuestion;

class SitesDeleteCommand extends BaseCommand
{
    protected function configure()
    {
        $this
            ->setName('sites:delete')
            ->setDescription('Deletes a site config')
        ;
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $this->getContainer()->getSiteManager()->listSites($input, $output, $this->getHelper('table'), true);
        $this->getContainer()->getSiteManager()->deleteSite($input, $output, $this->getHelper('question'));
        $this->getContainer()->getProvisionService()->reload($input, $output, $this);
        $this->getContainer()->getProvisionService()->provision($input, $output, $this);
    }
}
