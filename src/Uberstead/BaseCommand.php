<?php
namespace Uberstead;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\ProgressHelper;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Symfony\Component\Process\Process;

class BaseCommand extends Command
{
    public function checkConfig(InputInterface $input, OutputInterface $output)
    {
        $command = new CheckConfigCommand();
        $command->setApplication($this->getApplication());
        $command->run($input, $output);
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
            $cmd = 'su $SUDO_USER -c "vagrant reload"';

            /** @var ProgressHelper $progress */
            $progress = $this->getHelper('progress');
            $progress->setBarWidth(60);
            $progress->start($output, 30);

            $process = new Process($cmd);
            $process->setTimeout(null);

            $error = false;
            $bufferArr = array();
            $process->run(function ($type, $buffer) use (&$progress, &$error, &$bufferArr) {
                    if (Process::ERR === $type) {
                        $error = true;
                        $bufferArr[] = '<error>'.$buffer.'</error>';
                    } else {
                        $bufferArr[] = $buffer;
                    }
                    $progress->advance();
                });
            $progress->setCurrent(30);
            $progress->finish();

            if ($error) {
                $output->writeln(implode('', $bufferArr));
            }
        }
    }
}
