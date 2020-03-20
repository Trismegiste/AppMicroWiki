<?php

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class LandingTest extends WebTestCase {

    public function testHome() {
        $client = static::createClient();
        $crawler = $client->request('GET', '/');
        $this->assertEquals(302, $client->getResponse()->getStatusCode());
    }

}
