<?php

namespace GBProd\ElasticaBundle\Logger;

use Psr\Log\LoggerInterface;

/**
 * Logger for Elastica
 *
 * @author GBProd <contact@gb-prod.fr>
 */
class ElasticaLogger implements LoggerInterface
{
    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @var array
     */
    protected $queries = array();

    /**
     * @var boolean
     */
    protected $debug;

    /**
     * Constructor.
     *
     * @param LoggerInterface|null $logger The Symfony logger
     * @param boolean              $debug
     */
    public function __construct(LoggerInterface $logger = null, $debug = false)
    {
        $this->logger = $logger;
        $this->debug  = $debug;
    }

    /**
     * Returns the number of queries that have been logged.
     *
     * @return integer The number of queries logged
     */
    public function getNbQueries()
    {
        return count($this->queries);
    }

    /**
     * Returns a human-readable array of queries logged.
     *
     * @return array An array of queries
     */
    public function getQueries()
    {
        return $this->queries;
    }

    /**
     * {@inheritdoc}
     */
    public function debug($message, array $context = array())
    {
        if ($this->debug) {
            $this->queries[] = $context;
        }

        if ($this->logger) {
            $this->logger->debug($message, $context);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function emergency($message, array $context = array())
    {
        if ($this->logger) {
            $this->logger->emergency($message, $context);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function alert($message, array $context = array())
    {
        if ($this->logger) {
            $this->logger->alert($message, $context);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function critical($message, array $context = array())
    {
        if ($this->logger) {
            $this->logger->critical($message, $context);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function error($message, array $context = array())
    {
        if ($this->logger) {
            $this->logger->error($message, $context);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function warning($message, array $context = array())
    {
        if ($this->logger) {
            $this->logger->warning($message, $context);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function notice($message, array $context = array())
    {
        if ($this->logger) {
            $this->logger->notice($message, $context);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function info($message, array $context = array())
    {
        if ($this->logger) {
            $this->logger->info($message, $context);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function log($level, $message, array $context = array())
    {
        if ($this->logger) {
            $this->logger->log($level, $message, $context);
        }
    }
}
