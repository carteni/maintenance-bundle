<p align="center"><a href="http://www.multimediaexperiencestudio.it" target="_blank">
<img src="http://www.multimediaexperiencestudio.it/_cdn/public/assets/nlogo.svg" />
</a></p>

Show your site in maintenance mode. Allow to see the site _under maintenance_ to a list of given IPs.

[![SensioLabsInsight](https://insight.sensiolabs.com/projects/dd91a63a-bf2b-4db3-b0ad-262f3f6b7dd2/mini.png)](https://insight.sensiolabs.com/projects/dd91a63a-bf2b-4db3-b0ad-262f3f6b7dd2)

Installation
------------

1. Download the Bundle.

```console
$ composer require carteni/maintenance-bundle
```

2. Enable the Bundle in ```AppKernel```.

```php
public function registerBundles()
    {
        $bundles = [
            new \Mes\Misc\MaintenanceBundle\MesMaintenanceBundle(),
        ];
    }
```

3. Configure the Bundle.

```yaml
mes_maintenance:
    enabled: true
    ips_allowed: [10.10.10.0, 10.10.10.1, 10.10.10.2]
    controller: your_custom_controller:controllerAction or leave blank: controller ~.
```

If you prefer xml:

```xml
<?xml version="1.0" ?>
<container xmlns="http://symfony.com/schema/dic/services"
           xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
           xmlns:mes-maintenance="http://multimediaexperiencestudio.it/schema/dic/maintenance"
           xsi:schemaLocation="http://multimediaexperiencestudio.it/schema/dic/maintenance
           http://multimediaexperiencestudio.it/schema/dic/maintenance/maintenance-1.0.xsd">

    <mes-maintenance:config enabled="true">
        <mes-maintenance:ip_allowed>10.10.10.0</mes-maintenance:ip_allowed>
        <mes-maintenance:ip_allowed>10.10.10.1</mes-maintenance:ip_allowed>
        <mes-maintenance:ip_allowed>10.10.10.2</mes-maintenance:ip_allowed>
    </mes-maintenance:config>

</container>
```

The **maintenance template** can be overridden in ```app/Resources/MesMaintenanceBundle/views/index.html.twig```

```twig
# app/Resources/MesMaintenanceBundle/views/index.html.twig
{% extends '::base.html.twig' %}

{% block body %}
    <h1>Custom Template</h1>
    {% include '@MesMaintenance/maintenance.html.twig' %}
{% endblock %}
```

You can also override the ```maintenance.html.twig``` template in ```app/Resources/MesMaintenanceBundle/views/maintenance.html.twig```

Unit tests and check code style
-------------------------------

```sh
$ make
$ make test
$ make cs
```

License
-------

This bundle is under the MIT license. See the complete license [in the bundle](LICENSE)

Reporting an issue
------------------

Issues are tracked in the [Github issue tracker][1].

### Enjoy!

###### ♥ ☕ m|e|s

[1]: https://github.com/Carteni/maintenance-bundle/issues
