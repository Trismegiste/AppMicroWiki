<?php

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class SentenceCrudTest extends WebTestCase {

    public function testRedirectNewKeyAndCreate() {
        $client = static::createClient();
        $client->request('GET', '/docu/show/TMP/yolo');
        $this->assertEquals(302, $client->getResponse()->getStatusCode());
        $crawler = $client->followRedirect();
        $this->assertStringEndsWith('/vertex/append/TMP/yolo', $crawler->getUri());
        $this->assertEquals('yolo', $crawler->filter('#sentence_key')->attr('value'));

        $buttonCrawlerNode = $crawler->selectButton('Save');
        $form = $buttonCrawlerNode->form();

        $client->submit($form, [
            'sentence[key]' => 'Motoko',
            'sentence[category]' => 'cyborg',
            'sentence[content]' => 'Section 9',
        ]);
        $this->assertEquals(302, $client->getResponse()->getStatusCode());
        $crawler = $client->followRedirect();
        $this->assertEquals('Motoko (cyborg)', $crawler->filter('h2')->text());
    }

    public function testEdit() {
        $client = static::createClient();
        $crawler = $client->request('GET', '/vertex/edit/TMP/Motoko');

        $button = $crawler->selectButton('Save');
        $form = $button->form();

        $client->submit($form, [
            'sentence[key]' => 'Kusanagi Motoko',
            'sentence[category]' => 'cyborg',
            'sentence[content]' => '[[Section 9]]',
        ]);
        $this->assertEquals(302, $client->getResponse()->getStatusCode());
        $crawler = $client->followRedirect();
        $this->assertEquals('Kusanagi Motoko (cyborg)', $crawler->filter('h2')->text());
    }

    public function testDeleteButtonWhenFocus() {
        $client = static::createClient();
        $crawler = $client->request('GET', '/docu/show/TMP/Kusanagi Motoko');
        $this->assertCount(1, $crawler->selectLink('Delete'));
    }

    public function testXhrAutocomplete() {
        $client = static::createClient();
        $crawler = $client->request('GET', '/vertex/find/TMP/s');
        $this->assertEquals(['Section 9'], json_decode($client->getResponse()->getContent(), true));
        $crawler = $client->request('GET', '/vertex/find/TMP/k');
        $this->assertEquals(['Kusanagi Motoko'], json_decode($client->getResponse()->getContent(), true));
    }

    public function testDelete() {
        $client = static::createClient();
        $crawler = $client->request('GET', '/vertex/delete/TMP/Kusanagi Motoko');

        $button = $crawler->selectButton('Delete');
        $form = $button->form();

        $crawler = $client->submit($form);
        $this->assertCount(0, $crawler->filter('h2'));
    }

}
