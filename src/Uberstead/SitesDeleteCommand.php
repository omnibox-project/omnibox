<?php
namespace Uberstead;

use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ChoiceQuestion;
use Symfony\Component\Yaml\Parser;
use Symfony\Component\Yaml\Dumper;

class SitesDeleteCommand extends BaseCommand
{
    protected function configure()
    {
        $this
            ->setName('sites:delete')
            ->setDescription('Deletes a site config')
        ;
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $this->checkConfig($input, $output);

        $array = $this->getConfig();

        if (count($array['sites']) === 0) {
            $output->writeln("No sites have been added yet");
            die();
        }

        $sites = array();
        foreach ($array['sites'] as $key => $site) {
            $sites[] = $site['name'];
        }

        $helper = $this->getHelper('question');
        $question = new ChoiceQuestion(
            'Select the site you want to delete:',
            $sites,
            0
        );
        $question->setErrorMessage('The choise %s is invalid.');
        $domain = $helper->ask($input, $output, $question);

        foreach ($array['sites'] as $key => $site) {
            if ($site['name'] === $domain) {
                unset($array['sites'][$key]);
            }
        }

        $this->saveConfig($array);

        $this->updateNfsShares($input, $output);
        $this->runProvision($input, $output);
    }
}
