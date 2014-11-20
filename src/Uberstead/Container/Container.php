<?php
namespace Uberstead\Container;

use Pimple\Container as PimpleContainer;
use Uberstead\Service\Validator;
use Uberstead\Service\ConfigManager;
use Uberstead\Service\SiteManager;

class Container extends PimpleContainer
{
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
}
