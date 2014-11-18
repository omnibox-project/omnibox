<?php
namespace Uberstead\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\Process;
use Symfony\Component\Yaml\Parser;
use Symfony\Component\Console\Helper\ProgressHelper;

class UbersteadProvisionCommand extends BaseCommand
{
    protected function configure()
    {
        $this
            ->setName('uberstead:provision')
            ->setDescription('Update hosts file and nginx config inside Uberstead')
        ;
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $this->getContainer()->getProvisionService()->provision($input, $output, $this);
    }
}
