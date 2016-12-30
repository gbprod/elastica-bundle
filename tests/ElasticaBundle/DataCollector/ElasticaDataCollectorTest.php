<?php

namespace GBProd\ElasticaBundle\Tests\DataCollector;

use GBProd\ElasticaBundle\DataCollector\ElasticaDataCollector;
use GBProd\ElasticaBundle\Logger\ElasticaLogger;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Tests for ElasticaDataCollector
 *
 * @author GBProd <contact@gb-prod.fr>
 */
class ElasticaDataCollectorTest extends \PHPUnit_Framework_TestCase
{
    private $request;

    private $response;

    private $logger;

    private $collector;

    public function setUp()
    {
        $this->request  = $this->prophesize(Request::class);
        $this->response = $this->prophesize(Response::class);
        $this->logger   = $this->prophesize(ElasticaLogger::class);

        $this->collector = new ElasticaDataCollector($this->logger->reveal());
    }

    public function testCorrectAmountOfQueries()
    {
        $totalQueries = rand();

        $this->logger->getNbQueries()
            ->willReturn($totalQueries)
            ->shouldBeCalled()
        ;

        $this->logger->getQueries()->willReturn([]);

        $this->collector->collect(
            $this->request->reveal(),
            $this->response->reveal()
        );

        $this->assertEquals($totalQueries, $this->collector->getQueryCount());
    }

    public function testCorrectQueriesReturned()
    {
        $queries = array('testQueries');

        $this->logger
            ->getQueries()
            ->willReturn($queries)
            ->shouldBeCalled()
        ;

        $this->logger
            ->getNbQueries()
            ->willReturn(10)
        ;

        $this->collector->collect(
            $this->request->reveal(),
            $this->response->reveal()
        );

        $this->assertEquals($queries, $this->collector->getQueries());
    }

    public function testCorrectQueriesTime()
    {
        $queries = [
            ['response' => ['took' => 15]],
            ['response' => ['took' => 25]],
        ];

        $this->logger
            ->getQueries()
            ->willReturn($queries)
        ;

        $this->logger
            ->getNbQueries()
            ->willReturn(2)
        ;

        $this->collector->collect(
            $this->request->reveal(),
            $this->response->reveal()
        );

        $this->assertEquals(40, $this->collector->getTime());
    }

    public function testQueriesTimeWithResponseWithoutData()
    {
        $queries = [
            ['response' => ['took' => 15]],
            ['response' => ['took' => 25]],
            ['response' => []],
        ];

        $this->logger
            ->getQueries()
            ->willReturn($queries)
        ;

        $this->logger
            ->getNbQueries()
            ->willReturn(2)
        ;

        $this->collector->collect(
            $this->request->reveal(),
            $this->response->reveal()
        );

        $this->assertEquals(40, $this->collector->getTime());
    }

    public function testGetName()
    {
        $this->assertEquals('elastica', $this->collector->getName());
    }
}
