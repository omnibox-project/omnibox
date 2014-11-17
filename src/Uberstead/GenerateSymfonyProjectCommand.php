<?php
namespace Uberstead;

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
        $this->checkConfig($input, $output);

        $array = array(
            '1' => array('  1', 'Symfony Bootstrap Edition', 'https://github.com/phiamo/symfony-bootstrap'),
            '2' => array('  2', 'Symfony REST Edition', 'https://github.com/gimler/symfony-rest-edition'),
            '3' => array('  3', 'Symfony Standard Edition', 'https://github.com/symfony/symfony-standard'),
            '4' => array('  4', 'Symfony CMF Standard Edition', 'https://github.com/symfony-cmf/standard-edition'),
        );

        /** @var TableHelper $table */
        $table = $this->getHelper('table');
        $table
            ->setHeaders(array('Choice', 'Distribution', 'Description'))
            ->setRows($array);
        $table->render($output);

        $question = new Question('Choose your preferred Symfony2 flavor: [1] ', '1');
        $question->setValidator(function ($answer) use ($array) {
                if (!array_key_exists($answer, $array)) {
                    throw new \RuntimeException(
                        'Enter the choice number of your preferred Symfony2 distribution'
                    );
                }
                return $answer;
            });
        $helper = $this->getHelper('question');
        $choice = $helper->ask($input, $output, $question);

        $question = new Question('Choose a project directory - it will be created for you: ');
        $question->setValidator(
            function ($answer) {
                if (file_exists($answer)) {
                    if (!count(scandir($answer)) == 2) {
                        # Directory is not empty
                        throw new \RuntimeException(
                            'This folder is not empty. Try again.'
                        );
                    }
                }

                return $answer;
            }
        );
        $directory = $helper->ask($input, $output, $question);

        exec('su $SUDO_USER -c "mkdir -p '.$directory.'"');
        if (!file_exists('composer.phar')) {
            exec('su $SUDO_USER -c "curl -s https://getcomposer.org/installer | php"');
        }

        $this->addSite($input, $output, null, $directory, 'web');

        if ($choice == 1) { # Symfony Bootstrap Edition
            # Needs different installation
            die('Bootstrap edition is not supported yet...');
        } elseif ($choice == 2) { # Install Symfony REST Edition
            $cmd = "php composer.phar create-project gimler/symfony-rest-edition --stability=dev ".$directory;
        } elseif ($choice == 3) { # Install Symfony Standard Edition
            $cmd = "php composer.phar create-project symfony/framework-standard-edition ".$directory." '2.5.*'";
        }elseif ($choice == 4) { # Install Symfony CMF Standard Edition
            $cmd = "php composer.phar create-project symfony-cmf/symfony-cmf-standard ".$directory;
        }

        $process = new Process('su $SUDO_USER -c "'.$cmd.'"');
        $process->setTimeout(null);

        $process->run(function ($type, $buffer) use (&$output) {
                if (Process::ERR === $type) {
                    $output->write('<error>'.$buffer.'</error>');
                } else {
                    $output->write('<info>'.$buffer.'</info>');
                }
            });

        $this->updateNfsShares($input, $output);
        $this->runProvision($input, $output);
    }
}
