<?php
namespace Uberstead;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Yaml\Parser;

class UbersteadSettingsCommand extends BaseCommand
{
    protected function configure()
    {
        $this
            ->setName('uberstead:settings')
            ->setDescription('Check for ssh keys and update ip, cpu, memory')
        ;
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $this->checkConfig($input, $output);
    }
}
