<?php
namespace Omnibox\Service;

use Symfony\Component\Console\Helper\HelperSet;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Yaml\Dumper;
use Symfony\Component\Yaml\Parser;
use Omnibox\Model\Site;
use Omnibox\Model\Config;
use Omnibox\Helper\CliHelper;

class ConfigManager
{
    /**
     * @var array
     */
    protected $parameters;

    /**
     * @var Config
     */
    private $config;

    /**
     * @var CliHelper
     */
    private $cliHelper;

    function __construct($parameters, CliHelper $cliHelper)
    {
        $this->parameters = $parameters;
        $this->cliHelper = $cliHelper;
        $this->config = null;
    }

    /**
     * @param $parameter
     * @return mixed
     */
    public function getParameter($parameter)
    {
        return $this->parameters[$parameter];
    }

    /**
     * @return Config
     */
    public function getConfig()
    {
        if ($this->config === null) {
            $yaml = new Parser();
            if (file_exists($this->getParameter('path_to_config_file'))) {
                $array = $yaml->parse(file_get_contents($this->getParameter('path_to_config_file')));
            } else {
                $array = $this->getParameter('default_config_values');
            }
            $this->config = new Config($array);
        }

        return $this->config;
    }

    /**
     * @param Site $site
     */
    public function addSite(Site $site)
    {
        $this->getConfig()->addSite($site);
    }

    public function getSiteAttributeList($attribute)
    {
        return array_map(function ($x) use ($attribute) { return @$x[$attribute]; }, $this->getConfig()->getSitesArray());
    }

    public function configIsValid()
    {
        if (!file_exists($this->getParameter('path_to_config_file'))) {
            return false;
        }

        // todo: Validate config file contents. invalid? -> ask if it should be set to default values

        return true;
    }

    public function updateConfig()
    {
        $input = $this->cliHelper->getInputInterface();
        $output = $this->cliHelper->getOutputInterface();
        $helperSet = $this->cliHelper->getHelperSet();

        $output->writeln('<info>>>> Configurate Server <<<</info>');

        if (file_exists($this->getParameter('path_to_config_file'))) {
            $ask = '<question>This will update your server settings. Continue? [y]</question>';
        } else {
            $ask = '<question>'.$this->getParameter('path_to_config_file').' does not exist! Would you like to generate it? [y]</question>';
        }

        $helper = $helperSet->get('question');
        $question = new ConfirmationQuestion($ask, true);

        if ($helper->ask($input, $output, $question)) {
            $question = new Question('Which IP would you like to assign to the server? ['.$this->getConfig()->getIp().']: ', $this->getConfig()->getIp());
            $this->getConfig()->setIp($helper->ask($input, $output, $question));
            $question = new Question('Amount of memory ['.$this->getConfig()->getMemory().']: ', $this->getConfig()->getMemory());
            $this->getConfig()->setMemory($helper->ask($input, $output, $question));
            $question = new Question('Number of CPU cores ['.$this->getConfig()->getCpus().']: ', $this->getConfig()->getCpus());
            $this->getConfig()->setCpus($helper->ask($input, $output, $question));
        } else {
            $output->writeln('Aborting.');
            die();
        }

        $this->dumpConfig();
    }

    public function deleteSiteByName($name)
    {
        $sites = $this->getConfig()->getSites();
        foreach ($sites as $i => $site) {
            if ($site->getName() === $name) {
                unset($sites[$i]);
                $this->getConfig()->setSites($sites);
                $this->dumpConfig();
            }
        }
    }

    public function shareSiteByName($name)
    {
        $sites = $this->getConfig()->getSites();
        foreach ($sites as $i => $site) {
            $sites[$i]->setShare(0);
            if ($site->getName() === $name) {
                $sites[$i]->setShare(1);
                $this->getConfig()->setSites($sites);
                $this->dumpConfig();
            }
        }
    }

    private function dumpYml($dataArray, $filepath)
    {
        $dumper = new Dumper();
        $yaml = $dumper->dump($dataArray, 3);
        file_put_contents($filepath, $yaml);

        chmod($filepath, 0664);
        chown($filepath, $this->getParameter('system_user'));
    }

    public function dumpConfig()
    {
        $this->dumpYml($this->getConfig()->toArray(), $this->getParameter('path_to_config_file'));
    }

    public function createRowForHostsFile()
    {
        return implode(" ",
            array_merge(
                array($this->getConfig()->getIp()),
                $this->getSiteAttributeList('domain'),
                $this->getSiteAttributeList('alias')
            )
        );
    }
}
