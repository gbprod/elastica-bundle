<?php

namespace Tests\GBProd\ElasticaBundle\DataCollector;

use GBProd\ElasticaBundle\DataCollector\ElasticaDataCollector;
use Symfony\Bundle\FrameworkBundle\Client;
use Symfony\Bundle\WebProfilerBundle\Profiler\TemplateManager;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpKernel\Profiler\Profile;
use Tests\GBProd\WebTestCase;

class WebToolbarTest extends WebTestCase
{
    /**
     * @var Client
     */
    private $client;

    public function testElasticaWebToolbarIsRegisteredInDataCollectorTemplates()
    {
        $templates = $this->getContainer()->getParameter('data_collector.templates');
        $this->assertArrayHasKey('elastica.data_collector', $templates);
    }

    public function testElasticaWebToolbarIsSelectedForRendering()
    {
        $container = $this->getContainer();
        $profile = $this->createProfile();
        $manager = new TemplateManager(
            $container->get('profiler'),
            $container->get('twig'),
            $container->getParameter('data_collector.templates')
        );
        $templates = $manager->getNames($profile);
        $this->assertArrayHasKey('elastica', $templates);
    }

    public function testElasticDataIsRenderedInWebProfilerToolbar()
    {
        $client = self::createClient();
        // Just to be able to get valid request / response objects that are required by data collector
        $client->request('GET', '/_wdt/no-token' );

        $profile = $this->createProfile();
        $profile->getCollector('elastica')->collect($client->getRequest(), $client->getResponse());
        $profiler = $this->getContainer()->get('profiler');
        $profiler->saveProfile($profile);

        $crawler = $client->request('GET', '/_wdt/' . $profile->getToken());
        /** @noinspection NullPointerExceptionInspection */
        $this->assertEquals(200, $client->getResponse()->getStatusCode());
        $this->assertGreaterThan(0, $crawler->filter('.sf-toolbar-block-elastica')->count());
    }

    /**
     * @return Client
     */
    private function getClient()
    {
        if (!$this->client) {
            $this->client = self::createClient();
        }
        return $this->client;
    }

    /**
     * @return ContainerInterface
     */
    private function getContainer()
    {
        return $this->getClient()->getContainer();
    }

    /**
     * @return Profile
     */
    private function createProfile()
    {
        /** @var ElasticaDataCollector $collector */
        $collector = $this->getContainer()->get('elastica.data_collector');
        $token = 'test' . mt_rand(100000, 999999);
        $profile = new Profile($token);
        $profile->addCollector($collector);
        return $profile;
    }
}
