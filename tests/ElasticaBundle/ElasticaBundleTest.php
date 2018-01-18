<?php

namespace Tests\GBProd\ElasticaBundle;

use GBProd\ElasticaBundle\ElasticaBundle;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * Tests for Bundle
 *
 * @author gbprod <contact@gb-prod.fr>
 */
class ElasticaBundleTest extends TestCase
{
    public function testConstruct()
    {
        $this->assertInstanceOf(ElasticaBundle::class, new ElasticaBundle());
    }
}
