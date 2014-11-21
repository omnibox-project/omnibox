<?php
namespace Omnibox\Command;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\ProcessBuilder;
use Symfony\Component\Process\ProcessPipes;
use Symfony\Component\Process\ProcessUtils;

class SitesConsoleCommand extends BaseCommand
{
    protected function configure()
    {
        $this
            ->setName('sites:console')
            ->addArgument('site', InputArgument::REQUIRED)
            ->addArgument('cmd', InputArgument::IS_ARRAY)
            ->setDescription('Run app/console commands in a specific project')
        ;
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     */
    public function execute(InputInterface $input, OutputInterface $output)
    {
        $config = $this->getContainer()->getConfigManager()->getConfig();
        $sites = $config->getSites();

        foreach ($sites as $site) {
            if ($site->getName() === $input->getArgument('site')) {
                $process = new ProcessBuilder();
                foreach ($_ENV as $k => $v) {
                    $process->setEnv($k, $v);
                }
                $process->setWorkingDirectory('.');
                $command = implode(' ', $input->getArgument('cmd'));
                $process->setPrefix(
                    array(
                        'ssh',
                        '-t',
                        'vagrant@'.$config->getIp(),
                        '--',
                        'cd /home/vagrant/' . $site['name'] . ' && php app/console ' . $command . ' 2>&1'
                    )
                );
                $proc = $process->getProcess();
                $proc->setCommandLine($proc->getCommandLine().' 2>/dev/null');
                $proc->setTty(true);
                $proc->run();

                return;
            }
        }

        throw new \RuntimeException('Site not found.');
    }
}
