<?php

namespace Tests\GBProd;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase as BaseWebTestCase;
use Tests\GBProd\Fixtures\TestKernel;

abstract class WebTestCase extends BaseWebTestCase
{
    /**
     * {inheritdoc}
     */
    public function setUp()
    {
        self::bootKernel();
    }

    protected static function getKernelClass()
    {
        return TestKernel::class;
    }
}
