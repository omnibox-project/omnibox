<?php
namespace Uberstead\Container;

use Pimple\Container as PimpleContainer;
use Uberstead\Service\ValidatorService;

class Container extends PimpleContainer
{
    /**
     * @return ValidatorService
     */
    public function getValidator()
    {
        return $this['validator'];
    }
}
