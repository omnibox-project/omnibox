<?php
namespace Omnibox\Command;

use Omnibox\Model\Site;
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
            ->setSubcommands(
                array(
                    'add' => 'Add a site',
                    'remove' => 'Remove a site',
                    'list' => 'List all sites',
                    'share' => 'Share site on VagrantCloud',
                    'generate' => 'Generate a new Symfony2 site',
                    'ssh' => 'Run ssh commands on a specific site',
                    'console' => 'Run app/console commands in a specific project'
                )
            )
            ->requiresRootAccess(
                array(
                    'add',
                    'remove',
                    'generate',
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

    public function _share()
    {
        $output = $this->getContainer()->getCliHelper()->getOutputInterface();
        $input = $this->getContainer()->getCliHelper()->getInputInterface();
        $this->getContainer()->getSiteManager()->listSites($input, $output, $this->getHelper('table'), true);
        $this->getContainer()->getSiteManager()->shareSite($input, $output, $this->getHelper('question'));
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
                        'export XDEBUG_CONFIG="idekey=phpstorm" && export PHP_IDE_CONFIG="serverName='.$site['domain'].'" && cd /home/vagrant/' . $site['name'] . ' && ' . $command . ' 2>&1'
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
                        'export XDEBUG_CONFIG="idekey=phpstorm" && export PHP_IDE_CONFIG="serverName='.$site['domain'].'" && cd /home/vagrant/' . $site['name'] . ' && php -d xdebug.remote_host=192.168.10.1 -d xdebug.remote_enable=1 app/console ' . $command . ' 2>&1'
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

    public function _generate()
    {
        $output = $this->getContainer()->getCliHelper()->getOutputInterface();

        $array = array(
            '1' => array('  1', 'Symfony Standard Edition', 'https://github.com/symfony/symfony-standard'),
            '2' => array('  2', 'Symfony Bootstrap Edition', 'https://github.com/phiamo/symfony-bootstrap'),
            '3' => array('  3', 'Symfony REST Edition', 'https://github.com/gimler/symfony-rest-edition'),
            '4' => array('  4', 'Symfony CMF Standard Edition', 'https://github.com/symfony-cmf/standard-edition'),
            '5' => array('  5', 'Symfony Sonata Distribution', 'https://github.com/jmather/symfony-sonata-distribution'),
            '6' => array('  6', 'Symfony KnpLabs RAD Edition', 'https://github.com/KnpLabs/rad-edition'),
            '7' => array('  7', 'Symfony Rapid Development Edition', 'https://github.com/rgies/symfony'),
        );

        /** @var TableHelper $table */
        $table = $this->getHelper('table');
        $table->setHeaders(array('Choice', 'Distribution', 'Description'))->setRows($array);
        $table->render($output);

        $choice = $this->getContainer()->getCliHelper()->getQuestionHelper()->promptDistributionChoice($array);

        $site = $this->getContainer()->getSiteManager()->addSite(new Site(null, null, null, 'web'));
        $this->getContainer()->getSiteManager()->setDbInParametersYml($site);

        $directory = $site->getDirectory();

        if (!(count(scandir($directory)) == 2)) {
            //Directory is not empty
            throw new \RuntimeException(
                'This directory is not empty. Try again.'
            );
        }

        if (!file_exists('composer.phar')) {
            exec('su $SUDO_USER -c "curl -s https://getcomposer.org/installer | php"');
        }

        if ($choice == 1) { # Install Symfony Standard Edition
            $cmd = "php composer.phar create-project symfony/framework-standard-edition ".$directory." '2.5.*'";
        } elseif ($choice == 2) { # Symfony Bootstrap Edition
            # Needs different installation
            die('Bootstrap edition is not supported yet...');
        } elseif ($choice == 3) { # Install Symfony REST Edition
            $cmd = "php composer.phar create-project gimler/symfony-rest-edition --stability=dev ".$directory;
        } elseif ($choice == 4) { # Install Symfony CMF Standard Edition
            $cmd = "php composer.phar create-project symfony-cmf/symfony-cmf-standard ".$directory;
        } elseif ($choice == 5) { # Install Symfony Sonata Distribution
            $cmd = "php composer.phar create-project -s dev jmather/symfony-sonata-distribution ".$directory;
        } elseif ($choice == 6) { # Install Symfony KnpLabs RAD Edition
            $cmd = "php composer.phar create-project -s dev --prefer-dist --dev knplabs/rad-edition ".$directory;
        } elseif ($choice == 7) { # Install Symfony KnpLabs RAD Edition
            $cmd = "php composer.phar create-project -s dev rgies/symfony ".$directory;
        }

        $output->writeln('<info>Running "'.$cmd.'"...</info>');
        $this->getContainer()->getCliHelper()->getProcessHelper()->runWithProgressBar('su $SUDO_USER -c "'.$cmd.'"');

        $this->getContainer()->getVagrantManager()->provision();
        $this->getContainer()->getVagrantManager()->reload();

        $cmd = 'open http://'.$site->getDomain().'/app_dev.php';
        $this->setCommandForTerminateEvent('su $SUDO_USER -c "'.$cmd.'"');
    }
}
