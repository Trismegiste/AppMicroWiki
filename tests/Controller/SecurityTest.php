<?php

use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Routing\Router;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Routing\Route;

class SecurityTest extends WebTestCase {

    /** @var KernelBrowser */
    protected $client;

    protected function setUp(): void {
        $this->client = static::createClient();
    }

    protected function tearDown(): void {
        parent::tearDown();
        unset($this->client);
    }

    public function testLogin() {
        $this->client->request('GET', '/login');
        $this->assertResponseIsSuccessful();
    }

    public function testLogout() {
        $this->client->request('GET', '/logout');
        $this->assertResponseRedirects('/login');
    }

    /** Minimum test to check if all routes for a Document are secured (i.e redirect to login when user is not authenticated) */
    public function testAllRoutesUnderDocumentMustBeSecured() {
        /* @var $router Router */
        $router = $this->client->getContainer()->get('router');
        $crud = array_filter(iterator_to_array($router->getRouteCollection()), function(Route $route) {
            return preg_match('#^/docu#', $route->getPath());
        });
        $this->assertCount(10, $crud, "There are new or less Routes to test (conservative test to check if all routes are secured)");

        $url = array_map(function(Route $route) {
            $path = $route->getPath();
            list($meth) = $route->getMethods(); // gets only the first method
            $this->client->request($meth, $path);
            $this->assertResponseRedirects('/login', 302, "$path is not secured");

            return $this->client->getResponse()->getStatusCode();
        }, $crud);

        $this->assertNotContains(200, $url);  // useless
    }

}
