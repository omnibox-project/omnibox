<?php
namespace Uberstead\Service;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\Process;
use Uberstead\Command\BaseCommand;
use Uberstead\Service\ConfigManagerService;

class ProvisionService
{
    /**
     * @var ConfigManagerService
     */
    var $configManager;

    function __construct($configManager)
    {
        $this->configManager = $configManager;
    }

    /**
     * @return ConfigManagerService
     */
    public function getConfigManager()
    {
        return $this->configManager;
    }

    /**
     * @param ConfigManagerService $configManager
     */
    public function setConfigManager($configManager)
    {
        $this->configManager = $configManager;
    }

    public function provision(InputInterface $input, OutputInterface $output, BaseCommand $command)
    {
        $config = $this->getConfigManager()->getConfig();

        $comment = "# Uberstead Sites";

        // Create the sites row that will be inserted in the hosts file
        $sites = array();
        $sites[] = $config->getIp();
        foreach ($config->getSites() as $site) {
            $sites[] = $site->getDomain();
        }
        $sites[] = $comment;
        $sites = implode(" ", $sites);

        // Remove old Uberstead configs and add the current config
        $hostsFile = file('/etc/hosts');
        $prefix = "\n";
        foreach ($hostsFile as $key => $line) {
            if (strpos($line, $comment) !== false) {
                unset($hostsFile[$key]);
                $prefix = "";
            }
        }
        $hostsFile[] = $prefix.$sites;
        $hostsFile = implode("", $hostsFile);
        $hostsFile = trim($hostsFile, "\n")."\n";
        file_put_contents('/etc/hosts', $hostsFile);

        // Flush cache
        exec('dscacheutil -flushcache');
        $output->writeln('<info>Running "vagrant provision"...</info>');
        $this->runCommandWithProgressBar($input, $output, $command, 'su $SUDO_USER -c "vagrant provision"', 35);
    }

    public function reload(InputInterface $input, OutputInterface $output, BaseCommand $command)
    {
        $output->writeln('<info>Running "vagrant reload"...</info>');
        $this->runCommandWithProgressBar($input, $output, $command, 'su $SUDO_USER -c "vagrant reload"', 30);
    }

    public function runCommandWithProgressBar(InputInterface $input, OutputInterface $output, BaseCommand $commandObject, $command, $expectedLinesNum = 50)
    {
        $isVerbose = (OutputInterface::VERBOSITY_VERBOSE == $output->getVerbosity());

        if (!$isVerbose) {
            /** @var ProgressHelper $progress */
            $progress = $commandObject->getHelper('progress');
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
