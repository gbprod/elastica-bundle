<?php

namespace GBProd\ElasticaBundle\DataCollector;

use GBProd\ElasticaBundle\Logger\ElasticaLogger;
use Symfony\Component\HttpKernel\DataCollector\DataCollector;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Data collector collecting elastica statistics.
 *
 * @author gbprod <contact@gb-prod.fr>
 */
class ElasticaDataCollector extends DataCollector
{
    /**
     * @var ElasticaLogger
     */
    protected $logger;

    /**
     * @param ElasticaLogger $logger
     */
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
            if (array_key_exists('took', $query['response'])) {
                $time += $query['response']['took'];
            }
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

    /**
     * {@inheritdoc}
     */
    public function reset()
    {
        $this->data = [
            'nb_queries' => 0,
            'queries' => [],
        ];
    }
}
