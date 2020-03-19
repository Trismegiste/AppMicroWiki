<?php

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class DocumentCrudTest extends WebTestCase {

    public function testList() {
        $client = static::createClient();

        $crawler = $client->request('GET', '/docu/list');
        $this->assertEquals(200, $client->getResponse()->getStatusCode());

        $link = $crawler
                ->filter('a:contains("New document")')
                ->eq(0)
                ->link();

        $this->assertStringEndsWith('/docu/new', $link->getUri());
    }

    public function testNew() {
        $client = static::createClient();

        $crawler = $client->request('GET', '/docu/new');
        $this->assertEquals(200, $client->getResponse()->getStatusCode());

        $form = $crawler->selectButton('Save')->form();

        $form['document[title]'] = 'TMP';
        $form['document[description]'] = 'YOLO!';
        $crawler = $client->submit($form);
    }

    public function testShow() {
        $client = static::createClient();
        $crawler = $client->request('GET', '/docu/show/TMP');
        $this->assertEquals(200, $client->getResponse()->getStatusCode());
    }

    public function testRedirectNewKey() {
        $client = static::createClient();
        $crawler = $client->request('GET', '/docu/show/TMP/yolo');
        $this->assertEquals(302, $client->getResponse()->getStatusCode());
    }

}
