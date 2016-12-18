<?php

namespace GBProd\ElasticaBundle\DataCollector;

use GBProd\ElasticaBundle\Logger\ElasticaLogger;
use Symfony\Component\HttpKernel\DataCollector\DataCollector;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Data collector collecting elastica statistics.
 *
 * @author Gordon Franke <info@nevalon.de>
 */
class ElasticaDataCollector extends DataCollector
{
    protected $logger;

    public function __construct(ElasticaLogger $logger)
    {
        $this->logger = $logger;
    }

    /**
     * {@inheritdoc}
     */
    public function collect(Request $request, Response $response, \Exception $exception = null)
    {
        $this->data['nb_queries'] = $this->logger->getNbQueries();
        $this->data['queries'] = $this->logger->getQueries();
    }

    /**
     * Nb of queries executed
     *
     * @return integer
     */
    public function getQueryCount()
    {
        return $this->data['nb_queries'];
    }

    /**
     * Queries
     *
     * @return array
     */
    public function getQueries()
    {
        return $this->data['queries'];
    }

    /**
     * Execution time
     *
     * @return integer
     */
    public function getTime()
    {
        $time = 0;
        foreach ($this->data['queries'] as $query) {
            $time += $query['response']['took'];
        }

        return $time;
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'elastica';
    }
}
