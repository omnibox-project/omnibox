<?php
namespace Omnibox\Command;

use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\ProcessBuilder;

class SiteCommand extends BaseCommand
{
    protected function configure()
    {
        $this
            ->setName('site')
            ->setDescription('Manages sites: add, remove, list')
            ->addArgument('subcommand')
            ->addArgument('arguments', InputArgument::IS_ARRAY)
            ->requiresRootAccess()
            ->setSubcommands(
                array(
                    'add' => 'Add a site',
                    'remove' => 'Remove a site',
                    'list' => 'List all sites',
                    'ssh' => 'Run ssh commands on a specific site',
                    'console' => 'Run app/console commands in a specific project'
                )
            )
        ;
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $this->runSubCommand();
    }

    public function _add()
    {
        $output = $this->getContainer()->getCliHelper()->getOutputInterface();
        $output->writeln('<info>>>> Add Site <<<</info>');
        $site = $this->getContainer()->getSiteManager()->addSite();
        $this->getContainer()->getSiteManager()->setDbInParametersYml($site, true);
    }

    public function _remove()
    {
        $output = $this->getContainer()->getCliHelper()->getOutputInterface();
        $input = $this->getContainer()->getCliHelper()->getInputInterface();
        $this->getContainer()->getSiteManager()->listSites($input, $output, $this->getHelper('table'), true);
        $this->getContainer()->getSiteManager()->deleteSite($input, $output, $this->getHelper('question'));
    }

    public function _list()
    {
        $output = $this->getContainer()->getCliHelper()->getOutputInterface();
        $input = $this->getContainer()->getCliHelper()->getInputInterface();
        $this->getContainer()->getSiteManager()->listSites($input, $output, $this->getHelper('table'));
    }

    public function _ssh()
    {
        $input = $this->getContainer()->getCliHelper()->getInputInterface();

        $config = $this->getContainer()->getConfigManager()->getConfig();
        $arguments = $input->getArgument('arguments');

        foreach ($config->getSitesArray() as $site) {
            if ($site['name'] === $arguments[0]) {
                unset($arguments[0]);
                $process = new ProcessBuilder();
                foreach ($_ENV as $k => $v) {
                    $process->setEnv($k, $v);
                }
                $process->setWorkingDirectory('.');
                $command = implode(' ', $arguments);
                $process->setPrefix(
                    array(
                        'ssh',
                        '-t',
                        'vagrant@'.$config->getIp(),
                        '--',
                        'cd /home/vagrant/' . $site['name'] . ' && ' . $command . ' 2>&1'
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

    public function _console()
    {
        $input = $this->getContainer()->getCliHelper()->getInputInterface();

        $config = $this->getContainer()->getConfigManager()->getConfig();
        $arguments = $input->getArgument('arguments');

        foreach ($config->getSitesArray() as $site) {
            if ($site['name'] === $arguments[0]) {
                unset($arguments[0]);
                $process = new ProcessBuilder();
                foreach ($_ENV as $k => $v) {
                    $process->setEnv($k, $v);
                }
                $process->setWorkingDirectory('.');
                $command = implode(' ', $arguments);
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
