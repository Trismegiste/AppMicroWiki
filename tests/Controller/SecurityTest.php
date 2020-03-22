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

    public function testLogin() {
        $this->client->request('GET', '/login');
        $this->assertResponseIsSuccessful();
    }

    public function testLogout() {
        $this->client->request('GET', '/logout');
        $this->assertResponseRedirects('/login');
    }

    public function testRoutesAreAllSecured() {
        /* @var $router Router */
        $router = $this->client->getContainer()->get('router');
        $crud = array_filter(iterator_to_array($router->getRouteCollection()), function(Route $route) {
            return preg_match('#^/docu#', $route->getPath());
        });
        $this->assertCount(9, $crud);

        $url = array_map(function(Route $route) {
            $path = $route->getPath();
            $path = preg_replace('#(\{title\})#', 'dummy', $path);
            $path = preg_replace('#(\{key\})#', 'dummy', $path);
            $path = preg_replace('#(\{keyword\})#', 'dummy', $path);

            return $path;
        }, $crud);

        foreach ($url as $name => $path) {
            $this->client->request('GET', $path);
            $this->assertResponseRedirects('/login', 302, "$name is not secured");
        }
    }

}
