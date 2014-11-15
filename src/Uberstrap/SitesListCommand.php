<?php
namespace Uberstrap;

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
        $this->checkConfig($input, $output);

        $yaml = new Parser();
        $array = $yaml->parse(file_get_contents('uberstead.yaml'));

        foreach ($array['sites'] as $key => $site) {
            $output->writeln($site['name']);
            $output->writeln("  Domain:    " . $site['domain']);
            $output->writeln("  Directory: " . $site['directory']);
            $output->writeln("  Webroot:   " . $site['webroot']);
            $output->writeln("");
        }

        if (count($array['sites']) === 0) {
            $output->writeln("No sites have been added yet");
        }
    }
}
