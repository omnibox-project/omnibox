<?php
namespace Uberstead\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Console\Helper\TableHelper;
use Symfony\Component\Process\Process;

class GenerateSymfonyProjectCommand extends BaseCommand
{
    protected function configure()
    {
        $this
            ->setName('generate:symfony_project')
            ->setDescription('Update hosts file and nginx config inside Uberstead')
        ;
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $array = array(
            '1' => array('  1', 'Symfony Standard Edition', 'https://github.com/symfony/symfony-standard'),
            '2' => array('  2', 'Symfony Bootstrap Edition', 'https://github.com/phiamo/symfony-bootstrap'),
            '3' => array('  3', 'Symfony REST Edition', 'https://github.com/gimler/symfony-rest-edition'),
            '4' => array('  4', 'Symfony CMF Standard Edition', 'https://github.com/symfony-cmf/standard-edition'),
            '5' => array('  5', 'Symfony Sonata Distribution', 'https://github.com/jmather/symfony-sonata-distribution'),
            '6' => array('  6', 'Symfony KnpLabs RAD Edition', 'https://github.com/KnpLabs/rad-edition'),
            '7' => array('  7', 'Symfony Rapid Development Edition', 'https://github.com/rgies/symfony'),
        );

        $validator = $this->getContainer()->getValidator();
        /** @var TableHelper $table */
        $table = $this->getHelper('table');
        $table
            ->setHeaders(array('Choice', 'Distribution', 'Description'))
            ->setRows($array);
        $table->render($output);

        $helper = $this->getHelper('question');
        $choice = $helper->ask($input, $output, $validator->createDistributionChoiceQuestion($array));

        $site = $this->getContainer()->getSiteManager()->addSite($input, $output, $this->getHelper('question'), null, null, 'web');
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
        $this->getContainer()->getProcessHelper()->runWithProgressBar('su $SUDO_USER -c "'.$cmd.'"');

        $this->getContainer()->getVagrantManager()->provision();
        $this->getContainer()->getVagrantManager()->reload();

        $cmd = 'open http://'.$site->getDomain().'/app_dev.php';
        $this->setCommandForTerminateEvent('su $SUDO_USER -c "'.$cmd.'"');
    }
}
