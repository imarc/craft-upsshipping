<?php
namespace Craft;

use Ups\Entity\Service;

/**
 * UpsShippingPlugin requires valid dimensions and weights on products, and
 * uses them to connect to the UPS shipping API and pull back shipping prices.
 *
 * @author Kevin Hamer [kh] <kevin@imarc.com>
 */
class UpsShippingPlugin extends BasePlugin
{
    /**
     * getSettingsToMethods returns an array mapping configuration setting
     * names to UPS shipping method types.
     *
     * @return array
     */
    static private function getSettingsToMethods()
    {
        return [
            'upsEnableGround'      => Service::S_GROUND,
            'upsEnable3Day'        => Service::S_3DAYSELECT,
            'upsEnable2Day'        => Service::S_AIR_2DAY,
            'upsEnable2DayAM'      => Service::S_AIR_2DAYAM,
            'upsEnable1DaySaver'   => Service::S_AIR_1DAYSAVER,
            'upsEnable1Day'        => Service::S_AIR_1DAY,
            'upsEnable1DayEarlyAM' => Service::S_AIR_1DAYEARLYAM,
        ];
    }

    /**
     * init is only specified here to include the composer autoload.
     *
     */
    public function init()
    {
        require_once __DIR__ . '/vendor/autoload.php';
    }

    public function getName()
    {
        return Craft::t('UPS Shipping Rates for Craft Commerce');
    }

    public function getVersion()
    {
        return '1.0';
    }

    public function getDeveloper()
    {
        return 'Imarc';
    }

    public function getDeveloperUrl()
    {
        return 'https://www.imarc.com';
    }

    public function defineSettings()
    {
        return [
            'upsAccessKey' => [AttributeType::String, 'default' => ''],
            'upsUser' => [AttributeType::String, 'default' => ''],
            'upsPassword' => [AttributeType::String, 'default' => ''],
            'upsFromPostalCode' => [AttributeType::String, 'default' => ''],

            'upsEnableGround'      => [AttributeType::Bool, 'default' => true],
            'upsEnable3Day'        => [AttributeType::Bool, 'default' => false],
            'upsEnable2Day'        => [AttributeType::Bool, 'default' => false],
            'upsEnable2DayAM'      => [AttributeType::Bool, 'default' => false],
            'upsEnable1DaySaver'   => [AttributeType::Bool, 'default' => false],
            'upsEnable1Day'        => [AttributeType::Bool, 'default' => false],
            'upsEnable1DayEarlyAM' => [AttributeType::Bool, 'default' => false],
        ];
    }

    public function getSettingsHtml()
    {
        return craft()->templates->render('upsshipping/settings', [
            'settings' => $this->getSettings(),
        ]);
    }

    /**
     * commerce_registerShippingMethods loops through the configuration
     * settings to determine which shiping methods should be enabled.
     *
     * @return array
     */
    public function commerce_registerShippingMethods()
    {
        $settings = craft()->plugins->getPlugin('upsshipping')->getSettings();

        $methods = [];
        foreach (static::getSettingsToMethods() as $setting => $method) {
            if ($settings[$setting]) {
                $methods[] = new UpsShipping_BaseMethodModel($method);
            }
        }

        return $methods;
    }
}
