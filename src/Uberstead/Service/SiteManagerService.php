<?php
namespace Uberstead\Service;

use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Helper\TableHelper;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Yaml\Parser;
use Uberstead\Model\Site;
use Uberstead\Service\ConfigManagerService;
use Uberstead\Service\ValidatorService;

class SiteManagerService
{
    /**
     * @var ConfigManagerService
     */
    var $configManager;

    /**
     * @var ValidatorService
     */
    var $validator;

    public function deleteSite(InputInterface $input, OutputInterface $output, QuestionHelper $questionHelper)
    {
        $sites = $this->getConfigManager()->getConfig()->getSitesArray(true);
        $ids = array();
        foreach ($sites as $id => $site) {
            $ids[] = $id;
        }

        $question = new Question('Enter the ID of the site you would like to delete: ');
        $question->setValidator(
            function ($answer) use ($ids) {
                if (!in_array($answer, $ids)) {
                    throw new \RuntimeException(
                        'Not a valid ID number. Try again.'
                    );
                }

                return $answer;
            }
        );

        $id = $questionHelper->ask($input, $output, $question);
        $this->getConfigManager()->deleteSiteByName($sites[$id]['name']);
    }

    public function listSites(InputInterface $input, OutputInterface $output, TableHelper $tableHelper, $showIds = false)
    {
        $siteList = $this->getConfigManager()->getConfig()->getSitesArray($showIds);

        if ($showIds) {
            $fields = array('ID', 'Name', 'Domain', 'Directory', 'Web root');
        } else {
            $fields = array('Name', 'Domain', 'Directory', 'Web root');
        }

        $tableHelper
            ->setHeaders($fields)
            ->setRows($siteList);
        $tableHelper->render($output);

        if (count($siteList) === 0) {
            $output->writeln("No sites have been added yet");
        }
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @param QuestionHelper $questionHelper
     * @param null $name
     * @param null $directory
     * @param null $webroot
     * @return Site
     */
    public function addSite(InputInterface $input, OutputInterface $output, QuestionHelper $questionHelper, $name = null, $directory = null, $webroot = null)
    {
        $validator = $this->getValidator();

        if ($name === null) {
            $name = $questionHelper->ask($input, $output, $validator->createSiteNameQuestion());
        }

        $domain = $questionHelper->ask($input, $output, $validator->createDomainQuestion($name));

        if ($directory === null) {
            $directory = $questionHelper->ask($input, $output, $validator->createDirectoryQuestion($name));
        }

        if ($webroot === null) {
            $webroot = $questionHelper->ask($input, $output, $validator->createWebrootQuestion($directory));
        }

        $site = new Site($name, $domain, $directory, $webroot);
        $this->getConfigManager()->addSite($site);
        $this->getConfigManager()->dumpConfig();
        $this->getConfigManager()->setDbHintInParametersYml($site);

        return $site;
    }

    function __construct($configManager, $validator)
    {
        $this->configManager = $configManager;
        $this->validator = $validator;
    }

    /**
     * @return ConfigManagerService
     */
    public function getConfigManager()
    {
        return $this->configManager;
    }

    /**
     * @param ConfigManagerService $configManager
     */
    public function setConfigManager(ConfigManagerService $configManager)
    {
        $this->configManager = $configManager;
    }

    /**
     * @return ValidatorService
     */
    public function getValidator()
    {
        return $this->validator;
    }

    /**
     * @param ValidatorService $validator
     */
    public function setValidator($validator)
    {
        $this->validator = $validator;
    }
}
