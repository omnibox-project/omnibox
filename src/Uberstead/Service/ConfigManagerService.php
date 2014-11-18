<?php
namespace Uberstead\Service;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Yaml\Dumper;
use Symfony\Component\Yaml\Parser;
use Uberstead\Model\Site;
use Uberstead\Model\Config;

class ConfigManagerService
{
    /**
     * @var Config
     */
    var $config;

    /**
     * @param Site $site
     */
    public function addSite(Site $site)
    {
        $this->getConfig()->addSite($site);
    }

    public function getSiteAttributeList($attribute)
    {
        return array_map(function ($x) use ($attribute) { return $x[$attribute]; }, $this->getConfig()->getSitesArray());
    }

    function __construct()
    {
        $yaml = new Parser();
        $array = $yaml->parse(file_get_contents('uberstead.yaml'));
        $this->config = new Config($array);
    }

    public function checkConfig(InputInterface $input, OutputInterface $output)
    {
        exec('echo ~', $out);
        if (!file_exists($out[0].'/.ssh/id_rsa.pub')) {
            $output->writeln('It seems like you don\'t have any SSH keys. Run <question>ssh-keygen -t rsa -C "your_email@example.com"</question> to generate keys.');
            die();
        }

        $filename = 'uberstead.yaml';
        if (!file_exists($filename)) {

            $helper = $this->getHelper('question');
            $question = new ConfirmationQuestion('<question>uberstead.yaml does not exist! Would you like to generate it? [y]</question>', true);

            $output->writeln('');

            if ($helper->ask($input, $output, $question)) {
                $question = new Question('Which IP would you like to assign to the server? [192.168.10.10]: ', '192.168.10.10');
                $ip = $helper->ask($input, $output, $question);
                $question = new Question('Amount of memory [2048]: ', '2048');
                $memory = $helper->ask($input, $output, $question);
                $question = new Question('Number of CPU cores [1]: ', '1');
                $cpu = $helper->ask($input, $output, $question);
            }

            $yaml = <<<EOF
ip: "{$ip}"
memory: {$memory}
cpus: {$cpu}

authorize: ~/.ssh/id_rsa.pub

keys:
    - ~/.ssh/id_rsa

defaultfoldertype: nfs

sites:
EOF;
            file_put_contents($filename, $yaml);
            chmod($filename, 0664);
            chown($filename, end(explode('/', $out[0])));
        }
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

    public function dumpConfig()
    {
        $dumper = new Dumper();
        $yaml = $dumper->dump($this->getConfig()->toArray(), 3);
        file_put_contents('uberstead.yaml', $yaml);
    }

    public function setDbHintInParametersYml(Site $site)
    {
        $name = str_replace(" ", "_", $site->getName());
        $name = strtolower(preg_replace("/[^a-zA-Z0-9_]+/", "", $name));

        $parametersYml = $site->getDirectory() . DIRECTORY_SEPARATOR . 'app' . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR . 'parameters.yml';
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

    /**
     * @return Config
     */
    public function getConfig()
    {
        return $this->config;
    }

    /**
     * @param Config $config
     */
    public function setConfig($config)
    {
        $this->config = $config;
    }
}
