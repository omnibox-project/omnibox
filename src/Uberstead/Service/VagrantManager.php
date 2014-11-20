<?php
namespace Uberstead\Service;

use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;
use Symfony\Component\Process\Process;

class VagrantManager
{
    use \Uberstead\Container\ContainerAwareTrait;

    private $provision = false;
    private $reload = false;

    public function executeCommands()
    {
        if ($this->reload)
            $this->runReload();

        if ($this->provision)
            $this->runProvision();
    }

    public function provision()
    {
        $this->provision = true;
    }

    public function reload()
    {
        $this->reload = true;
    }

    private function runProvision()
    {
        $output = $this->getContainer()->getOutputInterface();

        $output->writeln('<info>Updating hosts file...</info>');
        $this->updateHostsFile();

        $output->writeln('<info>Running "vagrant provision"...</info>');
        $this->runWithProgressBar('su $SUDO_USER -c "vagrant provision"');
    }

    private function runReload()
    {
        $output = $this->getContainer()->getOutputInterface();

        $this->removeInvalidFoldersFromExports();

        $output->writeln('<info>Running "vagrant reload"...</info>');
        $this->runWithProgressBar('su $SUDO_USER -c "vagrant reload"');
    }

    private function updateHostsFile()
    {
        // Create the sites row that will be inserted in the hosts file
        $comment = "# Uberstead Sites";

        // Remove old Uberstead configs and add the current config
        $fileContent = file_get_contents($this->getContainer()->getParameter('path_to_hosts_file'));
        $fileContent = preg_replace('/\n?.*' . preg_quote($comment) . '.*$/m', '', $fileContent);
        $fileContent = trim($fileContent, "\n");
        $fileContent .= sprintf("\n%s %s\n", $this->getContainer()->getConfigManager()->createRowForHostsFile(), $comment);
        file_put_contents($this->getContainer()->getParameter('path_to_hosts_file'), $fileContent);

        // Flush dns cache
        exec('dscacheutil -flushcache');
    }

    private function removeInvalidFoldersFromExports()
    {
        // Remove folders in /etc/exports that doesn't exist (causes error)
        $fileContents = file($this->getContainer()->getParameter('path_to_exports_file'));
        foreach ($fileContents as $key => $line) {
            if (strpos($line, '-alldirs') !== false) {
                preg_match_all('/"([^"]+)"/', $line, $matches);
                if (!file_exists($matches[1][0])) {
                    unset($fileContents[$key]);
                }
            }
        }
        file_put_contents($this->getContainer()->getParameter('path_to_exports_file'), implode("", $fileContents));
    }

    /*
     * Todo: Rewrite and move to better place
     */
    private function runWithProgressBar($command)
    {
        $output = $this->getContainer()->getOutputInterface();

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
            $progress = $this->getContainer()->getHelperSet()->get('progress');
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
