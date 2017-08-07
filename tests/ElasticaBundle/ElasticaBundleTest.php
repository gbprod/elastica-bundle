<?php

namespace Tests\GBProd\ElasticaBundle;

use GBProd\ElasticaBundle\ElasticaBundle;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * Tests for Bundle
 *
 * @author gbprod <contact@gb-prod.fr>
 */
class ElasticaBundleTest extends \PHPUnit_Framework_TestCase
{
    public function testConstruct()
    {
        $this->assertInstanceOf(ElasticaBundle::class, new ElasticaBundle());
    }
}