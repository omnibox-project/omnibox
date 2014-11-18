<?php
namespace Uberstead\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\Process;
use Symfony\Component\Yaml\Parser;
use Symfony\Component\Console\Helper\ProgressHelper;

class UbersteadProvisionCommand extends BaseCommand
{
    protected function configure()
    {
        $this
            ->setName('uberstead:provision')
            ->setDescription('Update hosts file and nginx config inside Uberstead')
        ;
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $this->checkConfig($input, $output);

        $array = $this->getConfig();

        $ip = $array['ip'];
        $comment = "# Uberstead Sites";

        # Create the sites row that will be inserted in the hosts file
        $sites = array();
        $sites[] = $ip;
        foreach ($array['sites'] as $key => $site) {
            $sites[] = $site['domain'];
        }
        $sites[] = $comment;
        $sites = implode(" ", $sites);

        # Remove old Uberstead configs and add the current config
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

        # Flush cache
        exec('dscacheutil -flushcache');
        $output->writeln('<info>Running "vagrant provision"...</info>');
        $this->runCommandWithProgressBar($input, $output, 'su $SUDO_USER -c "vagrant provision"', 35);
    }
}
