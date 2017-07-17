<?php
namespace Craft;

use Commerce\Interfaces\ShippingRule;
use Ups\AddressValidation;
use Ups\Entity\Address;
use Ups\Entity\Dimensions;
use Ups\Entity\Package;
use Ups\Entity\PackagingType;
use Ups\Entity\Service;
use Ups\Entity\ShipFrom;
use Ups\Entity\Shipment;
use Ups\Entity\UnitOfMeasurement;
use Ups\Rate;

/**
 * UpsShipping_BaseRuleModel is used for all UPS shipping rules. It takes a UPS
 * Service Code and passes it through to the API.
 *
 * @see ShippingRule
 */
class UpsShipping_BaseRuleModel implements ShippingRule
{
    protected $address = null;
    protected $options = [];
    protected $service = null;

    /**
     * __construct
     *
     * @param mixed $service
     *     This should be a UPS Service Code.
     */
    public function __construct($service)
    {
        $this->service = $service;
    }

    public function getHandle()
    {
        return 'upsBaseRule';
    }

    public function getDescription()
    {
        return '';
    }

    public function getIsEnabled()
    {
        return true;
    }

    public function getOptions()
    {
        return $this->options;
    }

    public function getPercentageRate()
    {
        return 0;
    }

    public function getPerItemRate()
    {
        return 0;
    }

    public function getWeightRate()
    {
        return 0;
    }

    /**
     * buildShipment returns a Ups\Entity\Shipment object populated as best it can.
     *
     * @return Shipment
     */
    protected function buildShipment()
    {
        $settings = craft()->plugins->getPlugin('upsshipping')->getSettings();

        $shipment = new Shipment();

        // Address shipment is coming from
        $address = new Address();
        $address->setPostalCode($settings->upsFromPostalCode);

        $ship_from = new ShipFrom();
        $ship_from->setAddress($address);
        $shipment->setShipFrom($ship_from);

        // Address shipment is going to
        $ship_to = $shipment->getShipTo();
        $ship_to->setCompanyName($this->options['company_name']);
        $ship_to->setAddress($this->address);

        // Package type, weight, and dimensions
        $package = new Package();
        $package->getPackagingType()->setCode(PackagingType::PT_PACKAGE);
        $package->getPackageWeight()->setWeight($this->options['weight']);

        $dimensions = new Dimensions();
        $dimensions->setHeight((float) $this->options['height']);
        $dimensions->setWidth((float) $this->options['width']);
        $dimensions->setLength((float) $this->options['length']);

        $unit = new UnitOfMeasurement();
        $unit->setCode(UnitOfMeasurement::UOM_IN);
        $dimensions->setUnitOfMeasurement($unit);
        $package->setDimensions($dimensions);

        // UPS Service to use
        $service = new Service();
        $service->setCode($this->service);
        $service->setDescription($service->getName());
        $shipment->setService($service);

        $shipment->addPackage($package);

        return $shipment;
    }

    /**
     * getBaseRate is called externall, and returns the cost associated with
     * this shipping Method and rule.
     *
     * return Number
     */
    public function getBaseRate()
    {
        $settings = craft()->plugins->getPlugin('upsshipping')->getSettings();

        $rate = new Rate(
            $settings->upsAccessKey,
            $settings->upsUser,
            $settings->upsPassword
        );

        $shipment = $this->buildShipment();

        $rate_response = $rate->getRate($shipment);

        return $rate_response->RatedShipment[0]->TotalCharges->MonetaryValue;
    }

    /**
     * Returning 0 disables this.
     *
     */
    public function getMaxRate()
    {
        return 0;
    }

    /**
     * Returning 0 disables this.
     *
     */
    public function getMinRate()
    {
        return 0;
    }

    /**
     * setAddress reads in a Commerce_AddressModel into a \Ups\Entity\Address
     * for use with the API.
     *
     * @param Commerce_AddressModel $addr
     */
    protected function setAddress(Commerce_AddressModel $addr)
    {
        $this->address = new Address();
        $this->address->setAttentionName($addr->attention);
        $this->address->setAddressLine1($addr->firstName . ' ' . $addr->lastName);
        $this->address->setAddressLine2($addr->address1);
        $this->address->setAddressLine3($addr->address2);
        $this->address->setStateProvinceCode($addr->stateName);
        $this->address->setCity($addr->city);
        $this->address->setCountryCode($addr->countryId);
        $this->address->setPostalCode($addr->zipCode);

        //TEMP
        $this->address->setCountryCode('US');
    }

    /**
     * isValidAddress attempts to use UPS's address validation.
     *
     * return boolean
     */
    protected function isValidAddress()
    {
        $settings = craft()->plugins->getPlugin('upsshipping')->getSettings();

        $address_validator = new AddressValidation(
            $settings->upsAccessKey,
            $settings->upsUser,
            $settings->upsPassword
        );

        try {
            $response = $address_validator->validate($this->address);
            return !empty($response);

        } catch (Exception $e) {
            var_dump($e);
            return false;
        }
    }

    /**
     * matchOrder is called externally and determines whether this shipping
     * method/rule is valid for the given Commerce_OrderModel.
     *
     * @param Commerce_OrderModel $order
     *
     * @return boolean
     */
    public function matchOrder(Commerce_OrderModel $order)
    {
        if (!$order->shippingAddress) {
            return false;
        }

        $this->setAddress($order->shippingAddress);

        if (!$this->isValidAddress()) {
            return false;
        }

        /**
         * Commerce_OrderModel's default logic adds all three dimensions
         * calculate total order size, which is excessive to say the least.
         *
         * This finds the max width and length, and then just uses the default
         * added height; basically the equivalent of just stacking the items on
         * top of each other.
         */
        $max_width = 0;
        $max_length = 0;
        foreach ($order->getLineItems() as $item) {
            $max_width = (float) max($max_width, $item->width);
            $max_length = (float) max($max_length, $item->length);
        }

        $this->options = [
            'company_name' => $order->getShippingAddress()->businessName,
            'weight' => $order->getTotalWeight(),
            'height' => $order->getTotalHeight(),
            'width' => $max_width,
            'length' => $max_length,
        ];

        // Check whether it exceeds UPS's max size for this API.
        $girth = 2 * ($order->getTotalHeight() + $max_width) + $max_length;
        if ($girth > 165) {
            return false;
        }

        return true;
    }
}
