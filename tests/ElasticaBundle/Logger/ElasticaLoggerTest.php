<?php

namespace Tests\GBProd\ElasticaBundle\Logger;

use GBProd\ElasticaBundle\Logger\ElasticaLogger;
use Psr\Log\LoggerInterface;

/**
 * Tests for ElasticaLogger
 *
 * @author GBProd <contact@gb-prod.fr>
 */
class ElasticaLoggerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider getLevels
     */
    public function testDelegateLogsLevels($level)
    {
        $logger = $this->prophesize(LoggerInterface::class);

        $logger
            ->{$level}('message', ['context'])
            ->shouldBeCalled()
        ;

        $testedInstance = new ElasticaLogger($logger->reveal());

        $testedInstance->{$level}('message', ['context']);
    }

    public function getLevels()
    {
        return [
            ['debug'],
            ['emergency'],
            ['alert'],
            ['critical'],
            ['error'],
            ['warning'],
            ['notice'],
            ['info'],
        ];
    }

    public function testDelegateLog()
    {
        $logger = $this->prophesize(LoggerInterface::class);

        $logger
            ->log('debug', 'message', ['context'])
            ->shouldBeCalled()
        ;

        $testedInstance = new ElasticaLogger($logger->reveal());

        $testedInstance->log('debug', 'message', ['context']);
    }

    /**
     * @dataProvider getLevels
     */
    public function testNotDelegateLogsLevels($level)
    {
        $testedInstance = new ElasticaLogger(null);

        // AssertNoErrorExpected

        $testedInstance->{$level}('message', ['context']);
    }

    public function testGetZeroIfNoQueriesAdded()
    {
        $elasticaLogger = new ElasticaLogger();

        $this->assertEquals(0, $elasticaLogger->getNbQueries());
    }

    public function testCorrectAmountIfRandomNumberOfQueriesAdded()
    {
        $elasticaLogger = new ElasticaLogger(null, true);

        for ($i = 0; $i < 15; $i++) {
            $elasticaLogger->debug('Elastica Request', ['context']);
        }

        $this->assertEquals(15, $elasticaLogger->getNbQueries());
    }

    public function testGetQueries()
    {
        $elasticaLogger = new ElasticaLogger(null, true);

        $elasticaLogger->debug('Elastica Request', ['context']);
        $returnedQueries = $elasticaLogger->getQueries();

        $this->assertEquals(['context'], $returnedQueries[0]);
    }

    public function testNoQueriesStoredIfNoDebug()
    {
        $elasticaLogger = new ElasticaLogger(null, false);

        for ($i = 0; $i < 15; $i++) {
            $elasticaLogger->debug('Elastica Request', ['context']);
        }

        $this->assertEquals(0, $elasticaLogger->getNbQueries());
    }

     public function testNotDelegateLog()
    {
        $testedInstance = new ElasticaLogger(null);

        // AssertNoErrorExpected

        $testedInstance->log('debug', 'message', ['context']);
    }
}
