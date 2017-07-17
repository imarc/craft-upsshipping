UPS Shipping for Craft Commerce
===============================

This is a plugin for Craft Commerce that provides UPS shipping methods and pricing through the UPS API. It uses [gabrielbull/php-ups-api](https://github.com/gabrielbull/php-ups-api).


**This plugin has not been used in production.** This plugin was built but ultimately deemed unnecessary, so we are open sourcing the plugin in its current state to the community.


Requirements
------------

* All products must have their weight (pounds) and dimensions (inches) defined
* All shipments will be in boxes (not envelopes, etc.)
* The plugin depends on at least PHP 5.5


Installation
------------

After either method, you will need to install this plugin via the Craft control panel as well:

### Via Composer

Installation requires [composer](https://getcomposer.org/). This plugin also is makes use of [composer/installers](https://github.com/composer/installers) to make the plugin composer compatible.

You should be able to just run `composer require imarc/craft-upsshipping`

### Manually

You will need to put this plugin with a `upsshipping/` folder within `craft/plugins`. You will still need to use Composer to install this plugins dependencies, by running `composer install` while in that directory.


Configuration
-------------

Once installed, you can access the plugins settings via the Control Panel.

A **UPS Developer Kit Access Key**, **UPS Developer Kit Username**, and **UPS Developer Kit Password** are required. You can register and get these from [here](https://www.ups.com/upsdeveloperkit).

You can enable and disable each of the following UPS services. Only Ground Shipping is enabled by default.

* Ground Shipping
* 3 Day Select Shipping
* 2 Day Air Shipping
* 2 Day Air AM Shipping
* 1 Day Air Saver Shipping
* 1 Day Air Shipping
* 1 day Air Early AM Shipping

Usage
-----

These shipping methods will just show up when fetching shipping methods within Craft Commerce.


See Also
--------

While writing this plugin, we referenced [engram-design/AustraliaPost](https://github.com/engram-design/AustraliaPost) on numerous occasions, and they deserve credit.
