# ElasticaBundle

[![Build Status](https://travis-ci.org/gbprod/elastica-bundle.svg?branch=master)](https://travis-ci.org/gbprod/elastica-bundle)
[![codecov](https://codecov.io/gh/gbprod/elastica-bundle/branch/master/graph/badge.svg)](https://codecov.io/gh/gbprod/elastica-bundle)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/gbprod/elastica-bundle/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/gbprod/elastica-bundle/?branch=master)
[![Dependency Status](https://www.versioneye.com/user/projects/574a9ca0ce8d0e004130d342/badge.svg)](https://www.versioneye.com/user/projects/574a9ca0ce8d0e004130d342)

[![Latest Stable Version](https://poser.pugx.org/gbprod/elastica-bundle/v/stable)](https://packagist.org/packages/gbprod/elastica-bundle)
[![Total Downloads](https://poser.pugx.org/gbprod/elastica-bundle/downloads)](https://packagist.org/packages/gbprod/elastica-bundle)
[![Latest Unstable Version](https://poser.pugx.org/gbprod/elastica-bundle/v/unstable)](https://packagist.org/packages/gbprod/elastica-bundle)
[![License](https://poser.pugx.org/gbprod/elastica-bundle/license)](https://packagist.org/packages/gbprod/elastica-bundle)

Really simple bundle to use [elastica](http://elastica.io/) within Symfony applications.
Allows you to create elastica service in Symfony application.
The aim is to create a ligthweigth alternative to [FOSElasticaBundle](https://github.com/FriendsOfSymfony/FOSElasticaBundle), because sometimes, we don't need all that stuffs.

## Installation

Download bundle using [composer](https://getcomposer.org/) :

```bash
composer require gbprod/elastica-bundle
```

Declare in your `app/AppKernel.php` file:

```php
<?php
// app/AppKernel.php

public function registerBundles()
{
    $bundles = array(
        // ...
        new GBProd\ElasticaBundle\ElasticaBundle(),
        // ...
    );
}
```

## Configuration

### Clients

```yaml
# app/config/config.yml
elastica:
    clients:
        default:
            host: 127.0.0.1
            port: 9200
        other_client:
            host: 127.0.0.1
            port: 9201

```

If using a cluster:

```yaml
# app/config/config.yml
elastica:
    clients:
        default:
            connections:
                - { host: localhost, port: 9200 }
                - { host: localhost, port: 9201 }
```

Available options: `host`, `port`, `path`, `url`, `proxy`, `transport`, `persistent`, `timeout` and `proxy`

### Custom logger

By default, this bundle logs queries using the Symfony's default logger (`@logger`) into an `elastica` channel.

You can use a customized logger with the `logger` configuration option:   


```yaml
# app/config/config.yml
elastica:
    logger: my_custom_logger_service_id
    clients:
        default:
            host: 127.0.0.1
            port: 9200
```

## Usage

You can now use service `elastica.default_client` or `elastica.my_other_client`

```php
$client = $container->get('elastica.default_client');
```

### Services autowiring support

Symfony 3.3 have introduced support for [services autowiring](https://symfony.com/doc/3.3/service_container/3.3-di-changes.html). To be able to autowire Elastica connection into your services you need to setup your [client configuration](#clients) with a name `default`. In a case if you have multiple connections - only `default` connection will be enabled for autowiring because services autowiring is resolved by class names. 

Autowiring support is enabled by default, but if you need to disable it for some reason - you can do it by set `autowire: false` parameter:

```yaml
# app/config/config.yml
elastica:
    autowire: false
    clients:
        default:
            host: 127.0.0.1
            port: 9200
```

## Tests

Clone this repository (or a fork). You should have `php>=5.6` and `composer` installed.

```bash
make test-unit
```

## Contributing

Feel free to contribute, see [CONTRIBUTING.md](CONTRIBUTING.md) file for more informations.
