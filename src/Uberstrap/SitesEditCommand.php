<?php
namespace Uberstrap;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ChoiceQuestion;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Yaml\Parser;
use Symfony\Component\Yaml\Dumper;

class SitesEditCommand extends BaseCommand
{
    protected function configure()
    {
        $this
            ->setName('sites:edit')
            ->setDescription('Edit a site config')
        ;
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $this->checkConfig($input, $output);

        $yaml = new Parser();
        $array = $yaml->parse(file_get_contents('uberstead.yaml'));

        if (count($array['sites']) === 0) {
            $output->writeln("No sites have been added yet");
            die();
        }
        
        $helper = $this->getHelper('question');

        $sites = array();
        foreach ($array['sites'] as $key => $site) {
            $sites[] = $site['domain'];
        }

        $helper = $this->getHelper('question');
        $question = new ChoiceQuestion(
            'Select the site you want to edit:',
            $sites,
            0
        );
        $question->setErrorMessage('The choise %s is invalid.');
        $domain = $helper->ask($input, $output, $question);

        foreach ($array['sites'] as $key => $site) {
            if ($site['domain'] === $domain) {
                $site = $array['sites'][$key];
                $siteKey = $key;
            }
        }

        $question = new Question('Update site name: ['.$site['name'].'] ', $site['name']);
        $question->setValidator(function ($answer) {
                if (strlen(trim($answer)) === 0) {
                    throw new \RuntimeException(
                        'Enter a valid name for this site!'
                    );
                }
                return $answer;
            });
        $array['sites'][$siteKey]['name'] = $helper->ask($input, $output, $question);

        $question = new Question('Update domain: ['.$site['domain'].'] ', $site['domain']);
        $question->setValidator(function ($answer) {
                if (preg_match("/^([a-z\d](-*[a-z\d])*)(\.([a-z\d](-*[a-z\d])*))*$/i", $answer) //valid chars check
                    && preg_match("/^.{1,253}$/", $answer) //overall length check
                    && preg_match("/^[^\.]{1,63}(\.[^\.]{1,63})*$/", $answer)) {
                    return $answer;
                } else {
                    throw new \RuntimeException(
                        'This is not a valid domain name!'
                    );
                }
            });
        $array['sites'][$siteKey]['domain'] = $helper->ask($input, $output, $question);

        $question = new Question('Update directory: ['.$site['directory'].'] ', $site['directory']);
        $question->setValidator(function ($answer) {
                if (!file_exists($answer)) {
                    throw new \RuntimeException(
                        'The folder does not exist. Try again.'
                    );
                }
                return $answer;
            });
        $array['sites'][$siteKey]['directory'] = $helper->ask($input, $output, $question);

        $question = new Question('Update webroot: ['.$site['webroot'].'] ', $site['webroot']);
        $array['sites'][$siteKey]['webroot'] = $helper->ask($input, $output, $question);

        $dumper = new Dumper();
        $yaml = $dumper->dump($array, 3);
        file_put_contents('uberstead.yaml', $yaml);
    }
}
