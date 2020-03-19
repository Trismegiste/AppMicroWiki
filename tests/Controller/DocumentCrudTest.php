<?php

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class DocumentCrudTest extends WebTestCase {

    public function testList() {
        $client = static::createClient();

        $client->request('GET', '/docu/list');

        $this->assertEquals(200, $client->getResponse()->getStatusCode());
    }

}
