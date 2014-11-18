<?php
namespace Uberstead;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ChoiceQuestion;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Symfony\Component\Console\Question\Question;

class CheckConfigCommand extends BaseCommand
{
    protected function configure()
    {
        $this
            ->setName('test:check_config')
            ->setDescription('Check uberstead.yaml file structure')
        ;
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $this->checkConfig($input, $output);
    }
}
