<?php
namespace Craft;

use Ups\Entity\Service;
use Commerce\Interfaces\ShippingMethod;

/**
 * UpsShipping_BaseMethodModel is used for all UPS shipping methods. It only
 * take the UPS service code, and passes that through when making calls to UPS.
 *
 * @see ShippingMethod
 */
class UpsShipping_BaseMethodModel implements ShippingMethod
{
    protected $service_code = null;
    protected $name = null;

    /**
     * __construct
     *
     * @param mixed $service_code
     *     This should be a UPS Service Code, as you'd find in Ups\Entity\Service;
     *
     * @see Ups\Entity\Service
     */
    public function __construct($service_code = null)
    {
        if ($service_code === null) {
            $service_code = Service::S_GROUND;
        }

        $this->service_code = $service_code;

        $service = new Service();
        $service->setCode($this->service_code);
        $this->name = $service->getName();
    }

    public function getName()
    {
        return Craft::t($this->name);
    }

    public function getHandle()
    {
        return $this->name;
    }

    /**
     * Only one rule is returned - a UpsShipping_BaseRuleModel, and we pass the
     * UPS Service Code through to that.
     *
     * @return array
     */
    public function getRules()
    {
        return [
            new UpsShipping_BaseRuleModel($this->service_code),
        ];
    }

    public function getType()
    {
        return Craft::t('UPS');
    }

    public function getId()
    {
        return null;
    }

    public function getIsEnabled()
    {
        return true;
    }

    public function getCpEditUrl()
    {
        return '';
    }
}
