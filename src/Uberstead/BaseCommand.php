<?php
namespace Uberstead;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\ProgressHelper;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Symfony\Component\Process\Process;
use Symfony\Component\Yaml\Dumper;
use Symfony\Component\Yaml\Parser;
use Symfony\Component\Console\Question\Question;

class BaseCommand extends Command
{
    public function setDbHintInParametersYml($directory, $name, $ip)
    {
        $name = str_replace(" ", "_", $name);
        $name = strtolower(preg_replace("/[^a-zA-Z0-9_]+/", "", $name));

        $username = "homestead";
        $password = "secret";

        $conn = new \mysqli($ip, $username, $password);
        if ($conn->connect_error) {
            die("Connection failed: " . $conn->connect_error);
        }

        $sql = "CREATE DATABASE ".$name;
        if ($conn->query($sql) === TRUE) {
//            echo "Database created successfully";
        } else {
//            echo "Error creating database: " . $conn->error;
        }

        $conn->close();


        $parametersYml = $directory . DIRECTORY_SEPARATOR . 'app' . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR . 'parameters.yml';
        if (file_exists($parametersYml)) {
            $comment = "#Uberstead Config Hint#    ";
            $fileContents = file($parametersYml);
            foreach ($fileContents as $key => $line) {
                if (strpos($line, $comment) !== false) {
                    unset($fileContents[$key]);
                }
            }

            $fileContents[] = $comment . "database_host: 127.0.0.1\n";
            $fileContents[] = $comment . "database_port: 3306\n";
            $fileContents[] = $comment . "database_name: ".$name."\n";
            $fileContents[] = $comment . "database_user: homestead\n";
            $fileContents[] = $comment . "database_password: secret\n";

            $fileContents = implode("", $fileContents);
            $fileContents = trim($fileContents, "\n")."\n";
            file_put_contents($parametersYml, $fileContents);
        }
    }

    public function checkConfig(InputInterface $input, OutputInterface $output)
    {
        $command = new CheckConfigCommand();
        $command->setApplication($this->getApplication());
        $command->run($input, $output);
    }

    protected function getConfig()
    {
        $yaml = new Parser();
        $array = $yaml->parse(file_get_contents('uberstead.yaml'));

        if (!isset($array['sites'])) {
            $array['sites'] = array();
        }

        return $array;
    }

    protected function saveConfig($array)
    {
        $dumper = new Dumper();
        $yaml = $dumper->dump($array, 3);
        file_put_contents('uberstead.yaml', $yaml);
    }

    public function runProvision(InputInterface $input, OutputInterface $output)
    {
        $command = new UbersteadProvisionCommand();
        $command->setApplication($this->getApplication());
        $command->run($input, $output);
    }

    public function updateNfsShares(InputInterface $input, OutputInterface $output)
    {
        $helper = $this->getHelper('question');
        $question = new ConfirmationQuestion('<question>You have changed the shared directories. You need to run "vagrant reload" to apply the changes. Would you like to do it now? [y]</question>', true);
        if ($helper->ask($input, $output, $question)) {
            $output->writeln('<info>Running "vagrant reload"...</info>');
            $this->runCommandWithProgressBar($input, $output, 'su $SUDO_USER -c "vagrant reload"', 30);
        }
    }

    public function runCommandWithProgressBar(InputInterface $input, OutputInterface $output, $command, $expectedLinesNum = 50)
    {
        $isVerbose = (OutputInterface::VERBOSITY_VERBOSE == $output->getVerbosity());

        if (!$isVerbose) {
            /** @var ProgressHelper $progress */
            $progress = $this->getHelper('progress');
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

    public function addSite(InputInterface $input, OutputInterface $output, $name = null, $directory = null, $webroot = null)
    {
        $array = $this->getConfig();

        $sites = $array['sites'];
        $siteNames = array_map(function ($x) { return $x['name']; }, $sites);

        $helper = $this->getHelper('question');

        if ($name === null) {
            $question = new Question('Assign a name for the site (allowed characters a-zA-Z0-9-_): ');
            $question->setValidator(function ($answer) use ($siteNames) {
                    if (strlen(trim($answer)) === 0) {
                        throw new \RuntimeException(
                            'You need to provide a name for this site!'
                        );
                    } elseif (in_array($answer, $siteNames)) {
                        throw new \RuntimeException(
                            'There is already a site with this name!'
                        );
                    }

                    return $answer;
                });
            $name = $helper->ask($input, $output, $question);
        }

        $question = new Question('Domain (www.exampe.dev): ');
        $question->setValidator(function ($answer) {
                if (
                    preg_match('/^([a-z\d](-*[a-z\d])*)(\.([a-z\d](-*[a-z\d])*))*$/i', $answer) && //valid chars check
                    preg_match('/^.{1,253}$/', $answer) && //overall length check
                    preg_match('/^[^\.]{1,63}(\.[^\.]{1,63})*$/', $answer)
                ) {
                    return $answer;
                } else {
                    throw new \RuntimeException(
                        'This is not a valid domain name!'
                    );
                }
            });
        $domain = $helper->ask($input, $output, $question);

        if ($directory === null) {
            if (isset($_SERVER['HOME'])) {
                $defaultDirectory = $_SERVER['HOME'] . DIRECTORY_SEPARATOR . $name;
            } else {
                $defaultDirectory = DIRECTORY_SEPARATOR . $name;
            }
            $question = new Question('Directory (/some/example/folder): ['.$defaultDirectory.'] ', $defaultDirectory);
            $question->setValidator(
                function ($answer) {
                    if (!file_exists($answer)) {
                        throw new \RuntimeException(
                            'The folder does not exist. Try again.'
                        );
                    }

                    return $answer;
                }
            );
            $directory = $helper->ask($input, $output, $question);
        }

        if ($webroot === null) {
            $question = new Question('Web root (relative to the site directory): [web] ', 'web');
            $question->setValidator(
                function ($answer) use ($directory) {
                    if (!file_exists($directory. DIRECTORY_SEPARATOR . $answer)) {
                        throw new \RuntimeException(
                            'The folder does not exist. Try again.'
                        );
                    }

                    return $answer;
                }
            );
            $webroot = $helper->ask($input, $output, $question);
        }

        $array['sites'][] = [
            'name' => $name,
            'domain' => $domain,
            'directory' => $directory,
            'webroot' => $webroot,
        ];

        $this->saveConfig($array);
        $this->setDbHintInParametersYml($directory, $name, $array['ip']);

        $dumper = new Dumper();
        $yaml = $dumper->dump($array, 3);
        file_put_contents('uberstead.yaml', $yaml);
    }
}
