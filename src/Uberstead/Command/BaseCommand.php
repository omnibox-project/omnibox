<?php
namespace Uberstead\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\ProgressHelper;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Symfony\Component\Process\Process;
use Symfony\Component\Yaml\Dumper;
use Symfony\Component\Yaml\Parser;
use Symfony\Component\Console\Question\Question;
use Uberstead\Container\Container;

class BaseCommand extends Command
{
    /**
     * @var Container
     */
    private $container;

    /**
     * @param Container $container
     */
    public function setContainer($container)
    {
        $this->container = $container;
    }

    /**
     * @return Container
     */
    public function getContainer()
    {
        return $this->container;
    }

    protected function saveConfig($array)
    {
        $dumper = new Dumper();
        $yaml = $dumper->dump($array, 3);
        file_put_contents('uberstead.yaml', $yaml);
    }

    public function runProvision(InputInterface $input, OutputInterface $output)
    {
        $command = new UbersteadProvisionCommand();
        $command->setApplication($this->getApplication());
        $command->run($input, $output);
    }
}
