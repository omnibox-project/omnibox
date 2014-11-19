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
    private $queueVagrantProvision = false;
    private $queueVagrantReload = false;
    private $commandForTerminateEvent = null;

    /**
     * @var Container
     */
    private $container;

    /**
     * @return null
     */
    public function getCommandForTerminateEvent()
    {
        return $this->commandForTerminateEvent;
    }

    /**
     * @param null $commandForTerminateEvent
     */
    public function setCommandForTerminateEvent($commandForTerminateEvent)
    {
        $this->commandForTerminateEvent = $commandForTerminateEvent;
    }

    /**
     * @return boolean
     */
    public function isQueueVagrantProvision()
    {
        return $this->queueVagrantProvision;
    }

    public function queueVagrantProvision()
    {
        $this->queueVagrantProvision = true;
    }

    /**
     * @return boolean
     */
    public function isQueueVagrantReload()
    {
        return $this->queueVagrantReload;
    }

    public function queueVagrantReload()
    {
        $this->queueVagrantReload = true;
    }

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


    public function vagrantProvision(OutputInterface $output)
    {
        // Create the sites row that will be inserted in the hosts file
        $comment = "# Uberstead Sites";

        // Remove old Uberstead configs and add the current config
        $fileContent = file_get_contents('/etc/hosts');
        $fileContent = preg_replace('/\n?.*' . preg_quote($comment) . '.*$/m', '', $fileContent);
        $fileContent = trim($fileContent, "\n");
        $fileContent .= sprintf("\n%s %s\n", $this->getContainer()->getConfigManager()->createRowForHostsFile(), $comment);
        file_put_contents('/etc/hosts', $fileContent);

        // Flush dns cache
        exec('dscacheutil -flushcache');
        $output->writeln('<info>Running "vagrant provision"...</info>');
        $this->runCommandWithProgressBar($output, 'su $SUDO_USER -c "vagrant provision"');
    }

    public function vagrantReload(OutputInterface $output)
    {
        // Remove folders that doesn't exist
        $filename = '/etc/exports';
        $fileContents = file($filename);
        foreach ($fileContents as $key => $line) {
            if (strpos($line, '-alldirs') !== false) {
                preg_match_all('/"([^"]+)"/', $line, $matches);
                if (!file_exists($matches[1][0])) {
                    unset($fileContents[$key]);
                }
            }
        }
        file_put_contents($filename, implode("", $fileContents));

        $output->writeln('<info>Running "vagrant reload"...</info>');
        $this->runCommandWithProgressBar($output, 'su $SUDO_USER -c "vagrant reload"');
    }

    public function runCommandWithProgressBar(OutputInterface $output, $command)
    {
        $process = new Process($command);
        $process->setTimeout(null);

        $isVerbose = (OutputInterface::VERBOSITY_VERBOSE == $output->getVerbosity());

        if ($isVerbose) {
            $process->run(function ($type, $buffer) use (&$progress, &$error, &$bufferArr, &$output) {
                if (Process::ERR === $type) {
                    $output->write('<error>'.$buffer.'</error>');
                } else {
                    $output->write('<info>'.$buffer.'</info>');
                }
            });
        } else {
            /** @var ProgressHelper $progress */
            $progress = $this->getHelper('progress');
            $progress->setBarWidth(15);
            $progress->start($output);

            $hasError = false;
            $bufferArr = array();

            $process->run(function ($type, $buffer) use (&$progress, &$hasError, &$bufferArr, &$output) {
                if (Process::ERR === $type) {
                    $hasError = true;
                    $bufferArr[] = '<error>'.$buffer.'</error>';
                } else {
                    $bufferArr[] = '<info>'.$buffer.'</info>';
                }

                $progress->advance();
            });
            $progress->finish();

            if ($hasError) {
                $output->writeln(implode('', $bufferArr));
                $output->writeln('');
                $output->writeln('<error>Got errors... Aborting!</error>');
            }
        }

    }
}
