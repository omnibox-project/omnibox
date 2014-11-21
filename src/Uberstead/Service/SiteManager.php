<?php
namespace Uberstead\Service;

use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Helper\TableHelper;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Yaml\Parser;
use Uberstead\DependencyInjection\ContainerAwareTrait;
use Uberstead\Model\Site;

class SiteManager
{
    use ContainerAwareTrait;

    public function deleteSite(InputInterface $input, OutputInterface $output, QuestionHelper $questionHelper)
    {
        $cm = $this->getContainer()->getConfigManager();

        $sites = $cm->getConfig()->getSitesArray(true);
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
        $cm->deleteSiteByName($sites[$id]['name']);
    }

    public function listSites(InputInterface $input, OutputInterface $output, TableHelper $tableHelper, $showIds = false)
    {
        $cm = $this->getContainer()->getConfigManager();

        $siteList = $cm->getConfig()->getSitesArray($showIds);

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
    public function addSite(Site $site = null)
    {
        if ($site === null) {
            $site = new Site();
        }

        $qh = $this->getContainer()->getQuestionHelper();


        if ($site->getName() === null) {
            $site->setName($qh->promptSiteName());
        }

        $site->setDomain($qh->promptSiteDomain($site));

        if ($site->getDirectory() === null) {
            $site->setDirectory($qh->promptSiteDirectory($site));
        }

        if ($site->getWebroot() === null) {
            $site->setWebroot($qh->promptSiteWebroot($site));
        }


        $cm = $this->getContainer()->getConfigManager();
        $cm->addSite($site);
        $cm->dumpConfig();
        $cm->setDbHintInParametersYml($site);

        $vm = $this->getContainer()->getVagrantManager();
        $vm->provision();
        $vm->reload();

        return $site;
    }
}
