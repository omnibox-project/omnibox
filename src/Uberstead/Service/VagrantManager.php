<?php
namespace Uberstead\Service;

use Uberstead\Helper\CliHelper;
use Uberstead\Service\ConfigManager;

class VagrantManager
{
    private $provision;

    private $reload;

    /**
     * @var CliHelper
     */
    private $cliHelper;

    /**
     * @var ConfigManager
     */
    private $configManager;

    /**
     * @var array
     */
    protected $parameters;

    function __construct($parameters, CliHelper $cliHelper, ConfigManager $configManager)
    {
        $this->parameters = $parameters;
        $this->configManager = $configManager;
        $this->cliHelper = $cliHelper;
        $this->provision = false;
        $this->reload = false;
    }

    /**
     * @param $parameter
     * @return mixed
     */
    public function getParameter($parameter)
    {
        return $this->parameters[$parameter];
    }

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
        $output = $this->cliHelper->getOutputInterface();

        $output->writeln('<info>Updating hosts file...</info>');
        $this->updateHostsFile();

        $output->writeln('<info>Running "vagrant provision"...</info>');
        $this->cliHelper->getProcessHelper()->runWithProgressBar('su $SUDO_USER -c "vagrant provision"');
    }

    private function runReload()
    {
        $output = $this->cliHelper->getOutputInterface();

        $this->removeInvalidFoldersFromExports();

        $output->writeln('<info>Running "vagrant reload"...</info>');
        $this->cliHelper->getProcessHelper()->runWithProgressBar('su $SUDO_USER -c "vagrant reload"');
    }

    private function updateHostsFile()
    {
        // Create the sites row that will be inserted in the hosts file
        $comment = "# Uberstead Sites";

        // Remove old Uberstead configs and add the current config
        $fileContent = file_get_contents($this->getParameter('path_to_hosts_file'));
        $fileContent = preg_replace('/\n?.*' . preg_quote($comment) . '.*$/m', '', $fileContent);
        $fileContent = trim($fileContent, "\n");
        $fileContent .= sprintf("\n%s %s\n", $this->configManager->createRowForHostsFile(), $comment);
        file_put_contents($this->getParameter('path_to_hosts_file'), $fileContent);

        // Flush dns cache
        exec('dscacheutil -flushcache');
    }

    private function removeInvalidFoldersFromExports()
    {
        // Remove folders in /etc/exports that doesn't exist (causes error)
        $fileContents = file($this->getParameter('path_to_exports_file'));
        foreach ($fileContents as $key => $line) {
            if (strpos($line, '-alldirs') !== false) {
                preg_match_all('/"([^"]+)"/', $line, $matches);
                if (!file_exists($matches[1][0])) {
                    unset($fileContents[$key]);
                }
            }
        }
        file_put_contents($this->getParameter('path_to_exports_file'), implode("", $fileContents));
    }
}
