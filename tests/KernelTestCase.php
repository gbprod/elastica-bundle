<?php

namespace Tests\GBProd;

use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase as BaseKernelTestCase;
use Tests\GBProd\Fixtures\TestKernel;

abstract class KernelTestCase extends BaseKernelTestCase
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
