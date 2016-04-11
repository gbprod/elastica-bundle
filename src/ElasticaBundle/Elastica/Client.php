<?php

namespace GBProd\ElasticaBundle\Elastica;

use Elastica\Client as BaseClient;
use Elastica\Request;
use GBProd\ElasticaBundle\Logger\ElasticaLogger;

/**
 * Extends the default Elastica client to provide logging for errors that occur
 * during communication with ElasticSearch.
 *
 * @author Gordon Franke <info@nevalon.de>
 */
class Client extends BaseClient
{
    /**
     * @param string $path
     * @param string $method
     * @param array  $data
     * @param array  $query
     *
     * @return \Elastica\Response
     */
    public function request($path, $method = Request::GET, $data = array(), array $query = array())
    {
        $start = microtime(true);
        $response = parent::request($path, $method, $data, $query);
        $responseData = $response->getData();

        if (isset($responseData['took']) && isset($responseData['hits'])) {
            $this->logQuery($path, $method, $data, $query, $start, $response->getEngineTime(), $responseData['hits']['total']);
        } else {
            $this->logQuery($path, $method, $data, $query, $start, 0, 0);
        }

        return $response;
    }

    /**
     * Log the query if we have an instance of ElasticaLogger.
     *
     * @param string $path
     * @param string $method
     * @param array  $data
     * @param array  $query
     * @param int    $start
     */
    private function logQuery($path, $method, $data, array $query, $start, $engineMS = 0, $itemCount = 0)
    {
        if (!$this->_logger or !$this->_logger instanceof ElasticaLogger) {
            return;
        }

        $time = microtime(true) - $start;
        $connection = $this->getLastRequest()->getConnection();

        $connection_array = array(
            'host' => $connection->getHost(),
            'port' => $connection->getPort(),
            'transport' => $connection->getTransport(),
            'headers' => $connection->hasConfig('headers') ? $connection->getConfig('headers') : array(),
        );

        $this->_logger->logQuery($path, $method, $data, $time, $connection_array, $query, $engineMS, $itemCount);
    }
}
