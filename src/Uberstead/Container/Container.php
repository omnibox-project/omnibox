<?php
namespace Uberstead\Container;

use Pimple\Container as PimpleContainer;
use Uberstead\Service\ValidatorService;
use Uberstead\Service\ConfigManagerService;
use Uberstead\Service\SiteManagerService;
use Uberstead\Service\ProvisionService;

class Container extends PimpleContainer
{
    /**
     * @return ValidatorService
     */
    public function getValidator()
    {
        return $this['validator'];
    }

    /**
     * @return ConfigManagerService
     */
    public function getConfigManager()
    {
        return $this['config_manager'];
    }

    /**
     * @return SiteManagerService
     */
    public function getSiteManager()
    {
        return $this['site_manager'];
    }

    /**
     * @return ProvisionService
     */
    public function getProvisionService()
    {
        return $this['provision_service'];
    }
}
