<?php

namespace Tests\GBProd\ElasticaBundle\DataCollector;

use Symfony\Bundle\FrameworkBundle\Client;
use Symfony\Bundle\WebProfilerBundle\Profiler\TemplateManager;
use Symfony\Component\CssSelector\CssSelectorConverter;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpKernel\Profiler\Profile;
use Tests\GBProd\WebTestCase;

class WebToolbarTest extends WebTestCase
{
    /**
     * @var Client
     */
    private $client;

    /**
     * {inheritdoc}
     */
    public function setUp()
    {
        $this->client = self::createClient();
    }

    public function testElasticaWebToolbarIsRegisteredInDataCollectorTemplates()
    {
        $templates = $this->client->getContainer()->getParameter('data_collector.templates');
        $this->assertArrayHasKey('elastica.data_collector', $templates);
    }

    public function testElasticaWebToolbarIsSelectedForRendering()
    {
        $container = $this->client->getContainer();
        $profile = $this->createProfile();
        $manager = new TemplateManager(
            $container->get('profiler'),
            $container->get('twig'),
            $container->getParameter('data_collector.templates')
        );
        $this->assertNotEmpty($manager->getName($profile, 'elastica'));
    }

    public function testElasticDataIsRenderedInWebProfilerToolbar()
    {
        // Just to be able to get valid request / response objects that are required by data collector
        $this->client->request('GET', '/_wdt/no-token');

        $profile = $this->createProfile();
        $profile->getCollector('elastica')->collect($this->client->getRequest(), $this->client->getResponse());
        $profiler = $this->client->getContainer()->get('profiler');
        $profiler->saveProfile($profile);

        $crawler = $this->client->request('GET', '/_wdt/' . $profile->getToken());

        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());
        $this->assertToolbarButtonExists($crawler);
    }

    private function assertToolbarButtonExists($crawler)
    {
        if (class_exists(CssSelectorConverter::class)) {
            // This is Symfony 2.8+ where we're able to pass CSS selector directly and have v2 of profiler markup
            $this->assertGreaterThan(0, $crawler->filter('.sf-toolbar-block-elastica')->count());
        } else {
            // This is Symfony 2.7 where we need to use XPath and old toolbar markup
            $this->assertGreaterThan(0, $crawler->filterXPath('descendant-or-self::img[@alt="elastica"]')->count());
        }
    }

    /**
     * @return Profile
     */
    private function createProfile()
    {
        $collector = $this->client->getContainer()->get('elastica.data_collector');
        $token = 'test' . mt_rand(100000, 999999);
        $profile = new Profile($token);
        $profile->addCollector($collector);
        return $profile;
    }
}
