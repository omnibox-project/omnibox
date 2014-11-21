<?php
namespace Uberstead\DependencyInjection;

use Pimple\Container as PimpleContainer;
use Uberstead\Service\ConfigManager;
use Uberstead\Service\SiteManager;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Helper\HelperSet;
use Uberstead\Service\VagrantManager;
use Uberstead\Helper\CliHelper;

class Container extends PimpleContainer
{
    /**
     * @return CliHelper
     */
    public function getCliHelper()
    {
        return $this['cli_helper'];
    }

    /**
     * @return VagrantManager
     */
    public function getVagrantManager()
    {
        return $this['vagrant_manager'];
    }

    /**
     * @return ConfigManager
     */
    public function getConfigManager()
    {
        return $this['config_manager'];
    }

    /**
     * @return SiteManager
     */
    public function getSiteManager()
    {
        return $this['site_manager'];
    }

    /**
     * @param $parameter
     * @return mixed
     */
    public function getParameter($parameter)
    {
        return $this['parameters'][$parameter];
    }

    public function populateCointainerAwareServices()
    {
        foreach($this->keys() as $key) {
            $object = $this[$key];
            if (method_exists($object, 'setContainer')) {
                $object->setContainer($this);
            }
        }
    }

}
