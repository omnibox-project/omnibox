<?php
namespace Uberstead\Service;

use Symfony\Component\Console\Question\Question;
use Uberstead\Service\ConfigManagerService;

class ValidatorService
{
    /**
     * @var ConfigManagerService
     */
    var $configManager;

    function __construct($configManager)
    {
        $this->configManager = $configManager;
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
    public function setConfigManager($configManager)
    {
        $this->configManager = $configManager;
    }

    /**
     * @return Question
     */
    public function createSiteNameQuestion()
    {
        $siteNames = $this->getConfigManager()->getSiteAttributeList('name');

        $question = new Question('Assign a name for this project: ');
        $validator = $this;
        $question->setValidator(function ($answer) use ($siteNames, $validator) {
                return $validator->validateName($answer, $siteNames);
            });

        return $question;
    }

    public function validateName($name, $siteNames)
    {
        if (strlen(trim($name)) === 0) {
            throw new \RuntimeException(
                'You need to provide a name for this site!'
            );
        } elseif (in_array($name, $siteNames)) {
            throw new \RuntimeException(
                'There is already a site with this name!'
            );
        }

        $name = str_replace(" ", "-", $name);
        $name = strtolower(preg_replace("/[^a-zA-Z0-9-]+/", "", $name));

        return $name;
    }

    /**
     * @return Question
     */
    public function createDomainQuestion($projectName)
    {
        $domains = $this->getConfigManager()->getSiteAttributeList('domain');

        $question = new Question('Choose a domain: [www.'.$projectName.'.dev] ', 'www.'.$projectName.'.dev');
        $question->setAutocompleterValues(array(
                'www.'.$projectName.'.dev',
                $projectName.'.dev',
                'local.'.$projectName.'.com',
                'www.'.$projectName.'.com',
                $projectName.'.com',
            ));
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
    public function createDirectoryQuestion($projectName)
    {
        $directories = $this->getConfigManager()->getSiteAttributeList('directory');

//        if (isset($_SERVER['HOME'])) {
//            $defaultDirectory = $_SERVER['HOME'] . DIRECTORY_SEPARATOR . $name;
//        } else {
//            $defaultDirectory = DIRECTORY_SEPARATOR . $name;
//        }

        $question = new Question('Choose a project directory - it will be created if it doesn\'t exist: ['.$_SERVER['HOME'] . DIRECTORY_SEPARATOR . 'Projects' . DIRECTORY_SEPARATOR . $projectName.'] ', $_SERVER['HOME'] . DIRECTORY_SEPARATOR . 'Projects' . DIRECTORY_SEPARATOR . $projectName);
        $question->setAutocompleterValues(array(
                $_SERVER['HOME'] . DIRECTORY_SEPARATOR . $projectName,
                $_SERVER['HOME'] . DIRECTORY_SEPARATOR . 'Projects' . DIRECTORY_SEPARATOR . $projectName,
            ));
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
        if (in_array($directory, $directories)) {
            throw new \RuntimeException(
                'There is already a site with this directory!'
            );
        } elseif (file_exists($directory)) {
            if (!(count(scandir($directory)) == 2)) {
//                Directory is not empty
//                throw new \RuntimeException(
//                    'This directory is not empty. Try again.'
//                );
            }
        }
        exec('su $SUDO_USER -c "mkdir -p '.$directory.'"');

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

    public function createDistributionChoiceQuestion($choices)
    {
        $question = new Question('Choose your preferred Symfony2 flavor: [1] ', '1');
        $question->setValidator(
            function ($answer) use ($choices) {
                if (!array_key_exists($answer, $choices)) {
                    throw new \RuntimeException(
                        'Enter the choice number of your preferred Symfony2 distribution'
                    );
                }

                return $answer;
            }
        );

        return $question;
    }
}
