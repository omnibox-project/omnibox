<?php
namespace Uberstrap;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Yaml\Parser;
use Symfony\Component\Yaml\Dumper;

class SitesAddCommand extends BaseCommand
{
    protected function configure()
    {
        $this
            ->setName('sites:add')
            ->setDescription('Add a new site config')
        ;
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $this->checkConfig($input, $output);

        $yaml = new Parser();
        $array = $yaml->parse(file_get_contents('uberstead.yaml'));
        $sites = $array['sites'];
        $siteNames = array_map(function ($x) { return $x['name']; }, $sites);

        $helper = $this->getHelper('question');

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

        if (isset($_SERVER['HOME'])) {
            $defaultDirectory = $_SERVER['HOME'] . DIRECTORY_SEPARATOR . $name;
        } else {
            $defaultDirectory = DIRECTORY_SEPARATOR . $name;
        }
        $question = new Question('Directory (/some/example/folder): ['.$defaultDirectory.']', $defaultDirectory);
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

        $question = new Question('Web root (relative to the site directory): [web]', 'web');
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

        $array['sites'][] = [
            'name' => $name,
            'domain' => $domain,
            'directory' => $directory,
            'webroot' => $webroot,
        ];

        $dumper = new Dumper();
        $yaml = $dumper->dump($array, 3);
        file_put_contents('uberstead.yaml', $yaml);

        $this->runProvision($input, $output);
    }
}
