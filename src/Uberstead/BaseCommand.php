<?php
namespace Uberstead;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\ProgressHelper;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Symfony\Component\Process\Process;
use Symfony\Component\Yaml\Dumper;
use Symfony\Component\Yaml\Parser;

class BaseCommand extends Command
{
    public function checkConfig(InputInterface $input, OutputInterface $output)
    {
        $command = new CheckConfigCommand();
        $command->setApplication($this->getApplication());
        $command->run($input, $output);
    }

    protected function getConfig()
    {
        $yaml = new Parser();
        $array = $yaml->parse(file_get_contents('uberstead.yaml'));

        if (!isset($array['sites'])) {
            $array['sites'] = array();
        }

        return $array;
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

    public function updateNfsShares(InputInterface $input, OutputInterface $output)
    {
        $helper = $this->getHelper('question');
        $question = new ConfirmationQuestion('<question>You have changed the shared directories. You need to run "vagrant reload" to apply the changes. Would you like to do it now? [y]</question>', true);
        if ($helper->ask($input, $output, $question)) {
            $output->writeln('<info>Running "vagrant reload"...</info>');
            $this->runCommandWithProgressBar($input, $output, 'su $SUDO_USER -c "vagrant reload"', 30);
        }
    }

    public function runCommandWithProgressBar(InputInterface $input, OutputInterface $output, $command, $expectedLinesNum = 50)
    {
        $isVerbose = (OutputInterface::VERBOSITY_VERBOSE == $output->getVerbosity());

        if (!$isVerbose) {
            /** @var ProgressHelper $progress */
            $progress = $this->getHelper('progress');
            $progress->setBarWidth(60);
            $progress->start($output, $expectedLinesNum);
        }

        $process = new Process($command);
        $process->setTimeout(null);

        $error = false;
        $bufferArr = array();
        $process->run(function ($type, $buffer) use (&$progress, &$error, &$bufferArr, &$output, &$isVerbose) {
            if ($isVerbose) {
                if (Process::ERR === $type) {
                    $output->write('<error>'.$buffer.'</error>');
                } else {
                    $output->write('<info>'.$buffer.'</info>');
                }
            } else {
                if (Process::ERR === $type) {
                    $error = true;
                    $bufferArr[] = '<error>'.$buffer.'</error>';
                } else {
                    $bufferArr[] = $buffer;
                }
                $progress->advance();
            }
        });

        if (!$isVerbose) {
            $progress->setCurrent($expectedLinesNum);
            $progress->finish();

            if ($error) {
                $output->writeln(implode('', $bufferArr));
            }
        }
    }
}
