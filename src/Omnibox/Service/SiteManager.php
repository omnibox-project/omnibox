<?php
namespace Omnibox\Service;

use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Helper\TableHelper;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;
use Omnibox\Model\Site;
use Omnibox\Service\ConfigManager;
use Omnibox\Service\VagrantManager;
use Omnibox\Helper\CliHelper;

class SiteManager
{
    /**
     * @var CliHelper
     */
    private $cliHelper;

    /**
     * @var VagrantManager
     */
    private $vagrantManager;

    /**
     * @var ConfigManager
     */
    private $configManager;

    function __construct(CliHelper $cliHelper, VagrantManager $vagrantManager, ConfigManager $configManager)
    {
        $this->configManager = $configManager;
        $this->cliHelper = $cliHelper;
        $this->vagrantManager = $vagrantManager;
    }

    public function deleteSite(InputInterface $input, OutputInterface $output, QuestionHelper $questionHelper)
    {
        $sites = $this->configManager->getConfig()->getSitesArray(true);
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
        $this->configManager->deleteSiteByName($sites[$id]['name']);
        $this->vagrantManager->provision();
        $this->vagrantManager->reload();
    }

    public function listSites(InputInterface $input, OutputInterface $output, TableHelper $tableHelper, $showIds = false)
    {
        $siteList = $this->configManager->getConfig()->getSitesArray($showIds);

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
     * @param Site $site
     * @return Site
     */
    public function addSite(Site $site = null)
    {
        if ($site === null) {
            $site = new Site();
        }

        $qh = $this->cliHelper->getQuestionHelper();

        if ($site->getName() === null) {
            $siteNames = $this->configManager->getSiteAttributeList('name');
            $site->setName($qh->promptSiteName($siteNames));
        }

        $domains = $this->configManager->getSiteAttributeList('domain');
        $site->setDomain($qh->promptSiteDomain($site, $domains));

        if ($site->getDirectory() === null) {
            $directories = $this->configManager->getSiteAttributeList('directory');
            $site->setDirectory($qh->promptSiteDirectory($site, $directories));
        }

        if ($site->getWebroot() === null) {
            $site->setWebroot($qh->promptSiteWebroot($site));
        }

        $this->configManager->addSite($site);
        $this->configManager->dumpConfig();
        $this->configManager->setDbHintInParametersYml($site);

        $this->vagrantManager->provision();
        $this->vagrantManager->reload();

        return $site;
    }
}
