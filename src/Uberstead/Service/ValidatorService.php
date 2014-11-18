<?php
namespace Uberstead\Service;

use Symfony\Component\Console\Question\Question;

class ValidatorService
{
    var $configArray = array();

    /**
     * @return array
     */
    public function getConfigArray()
    {
        return $this->configArray;
    }

    /**
     * @param array $configArray
     */
    public function setConfigArray($configArray)
    {
        $this->configArray = $configArray;
    }

    /**
     * @return Question
     */
    public function createSiteNameQuestion()
    {
        $array = $this->getConfigArray();
        $sites = $array['sites'];
        $siteNames = array_map(function ($x) { return $x['name']; }, $sites);

        $question = new Question('Assign a name for the site (allowed characters a-z0-9-_): ');
        $validator = $this;
        $question->setValidator(function ($answer) use ($siteNames, $validator) {
                return $validator->validateSite($answer, $siteNames);
            });

        return $question;
    }

    public function validateSite($site, $siteNames)
    {
        if (strlen(trim($site)) === 0) {
            throw new \RuntimeException(
                'You need to provide a name for this site!'
            );
        } elseif (in_array($site, $siteNames)) {
            throw new \RuntimeException(
                'There is already a site with this name!'
            );
        }

        return $site;
    }

    /**
     * @return Question
     */
    public function createDomainQuestion()
    {
        $array = $this->getConfigArray();
        $sites = $array['sites'];
        $domains = array_map(function ($x) { return $x['domain']; }, $sites);

        $question = new Question('Domain (www.exampe.dev): ');
        $validator = $this;
        $question->setValidator(function ($answer) use ($domains, $validator) {
                return $validator->validateDomain($answer, $domains);
            });

        return $question;
    }

    public function validateDomain($domain, $domains)
    {
        if (!(preg_match('/^([a-z\d](-*[a-z\d])*)(\.([a-z\d](-*[a-z\d])*))*$/i', $domain) && //valid chars check
            preg_match('/^.{1,253}$/', $domain) && //overall length check
            preg_match('/^[^\.]{1,63}(\.[^\.]{1,63})*$/', $domain))) {
            throw new \RuntimeException(
                'This is not a valid domain name!'
            );
        } elseif (in_array($domain, $domains)) {
            throw new \RuntimeException(
                'There is already a site with this domain!'
            );
        }

        return $domain;
    }

    /**
     * @return Question
     */
    public function createDirectoryQuestion()
    {
        $array = $this->getConfigArray();
        $sites = $array['sites'];
        $directories = array_map(function ($x) { return $x['directory']; }, $sites);

//        if (isset($_SERVER['HOME'])) {
//            $defaultDirectory = $_SERVER['HOME'] . DIRECTORY_SEPARATOR . $name;
//        } else {
//            $defaultDirectory = DIRECTORY_SEPARATOR . $name;
//        }

        $question = new Question('Directory (/some/example/folder): ');
        $validator = $this;
        $question->setValidator(
            function ($answer) use ($directories, $validator) {
                return $validator->validateDirectory($answer, $directories);
            }
        );

        return $question;
    }

    public function validateDirectory($directory, $directories)
    {
        if (!file_exists($directory)) {
            throw new \RuntimeException(
                'The folder does not exist. Try again.'
            );
        } elseif (in_array($directory, $directories)) {
            throw new \RuntimeException(
                'There is already a site with this directory!'
            );
        }

        return $directory;
    }

    public function createWebrootQuestion($directory)
    {
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

        return $question;
    }
}
