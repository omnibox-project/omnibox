<?php
namespace Omnibox\Helper;

use Symfony\Component\Process\Process;
use Symfony\Component\Console\Output\OutputInterface;
use Omnibox\Helper\CliHelper;

class ProcessHelper
{
    /**
     * @var CliHelper
     */
    private $cliHelper;

    function __construct(CliHelper $cliHelper)
    {
        $this->cliHelper = $cliHelper;
    }

    public function runWithProgressBar($command)
    {
        $output = $this->cliHelper->getOutputInterface();

        $process = new Process($command);
        $process->setTimeout(null);

        $isVerbose = (OutputInterface::VERBOSITY_VERBOSE == $output->getVerbosity());

        if ($isVerbose) {
            $process->run(
                function ($type, $buffer) use (&$progress, &$error, &$bufferArr, &$output) {
                    if (Process::ERR === $type) {
                        $output->write('<error>' . $buffer . '</error>');
                    } else {
                        $output->write('<info>' . $buffer . '</info>');
                    }
                }
            );
        } else {
            $progress = $this->cliHelper->getHelperSet()->get('progress');
            $progress->setFormat('[%bar%]');
            $progress->setBarWidth(15);
            $progress->start($output);

            $hasError = false;
            $bufferArr = array();

            $process->run(
                function ($type, $buffer) use (&$progress, &$hasError, &$bufferArr, &$output) {
                    if (Process::ERR === $type) {
                        $hasError = true;
                        $bufferArr[] = '<error>' . $buffer . '</error>';
                    } else {
                        $bufferArr[] = '<info>' . $buffer . '</info>';
                    }

                    $progress->advance();
                }
            );
            $progress->finish();

            if ($hasError) {
                $output->writeln(implode('', $bufferArr));
                $output->writeln('');
                $output->writeln('<error>Got errors... Aborting!</error>');
            }
        }
    }
}
