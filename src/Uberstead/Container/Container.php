<?php
namespace Uberstead\Container;

use Pimple\Container as PimpleContainer;
use Uberstead\Service\Validator;
use Uberstead\Service\ConfigManager;
use Uberstead\Service\SiteManager;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Helper\HelperSet;
use Uberstead\Service\VagrantManager;

class Container extends PimpleContainer
{
    /**
     * @return VagrantManager
     */
    public function getVagrantManager()
    {
        return $this['vagrant_manager'];
    }

    /**
     * @return HelperSet
     */
    public function getHelperSet()
    {
        return $this['helper_set'];
    }

    /**
     * @return InputInterface
     */
    public function getInputInterface()
    {
        return $this['input_interface'];
    }

    /**
     * @return OutputInterface
     */
    public function getOutputInterface()
    {
        return $this['output_interface'];
    }

    /**
     * @return Validator
     */
    public function getValidator()
    {
        return $this['validator'];
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
}
