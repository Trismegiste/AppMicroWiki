<?php

use App\Tests\Controller\SecuredClientImpl;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class SentenceCrudTest extends WebTestCase {

    use SecuredClientImpl;

    public function testAppend() {
        $client = static::getAuthenticatedClient();
        $crawler = $client->request('GET', '/docu/show/TMP/append/yolo');
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
        $this->assertEquals('Motoko cyborg', $crawler->filter('h2')->text());
    }

    public function testEdit() {
        $client = static::getAuthenticatedClient();
        $crawler = $client->request('GET', '/docu/show/TMP/edit/Motoko');

        $button = $crawler->selectButton('Save');
        $form = $button->form();

        $client->submit($form, [
            'sentence[key]' => 'Kusanagi Motoko',
            'sentence[category]' => 'cyborg',
            'sentence[content]' => '[[Section 9]]',
        ]);
        $this->assertEquals(302, $client->getResponse()->getStatusCode());
        $crawler = $client->followRedirect();
        $this->assertEquals('Kusanagi Motoko cyborg', $crawler->filter('h2')->text());
    }

    public function testDeleteButtonWhenFocus() {
        $client = static::getAuthenticatedClient();
        $crawler = $client->request('GET', '/docu/show/TMP');
        $this->assertCount(1, $crawler->filter('article footer i.icon-trash'));
    }

    public function testXhrLinkAutocomplete() {
        $client = static::getAuthenticatedClient();
        $crawler = $client->request('GET', '/docu/show/TMP/link/find/s');
        $this->assertEquals(['Section 9'], json_decode($client->getResponse()->getContent(), true));
        $crawler = $client->request('GET', '/docu/show/TMP/link/find/k');
        $this->assertEquals(['Kusanagi Motoko'], json_decode($client->getResponse()->getContent(), true));
    }

    public function testXhrCategoryAutocomplete() {
        $client = static::getAuthenticatedClient();
        $crawler = $client->request('GET', '/docu/show/TMP/category/find/cy');
        $this->assertEquals(['cyborg'], json_decode($client->getResponse()->getContent(), true));
    }

    public function testQrCode() {
        $client = static::getAuthenticatedClient();
        $crawler = $client->request('GET', '/docu/show/TMP/qrcode/Kusanagi Motoko');
        $this->assertPageTitleContains('QR Code');
    }

    public function testDelete() {
        $client = static::getAuthenticatedClient();
        $crawler = $client->request('GET', '/docu/show/TMP/delete/Kusanagi Motoko');

        $button = $crawler->selectButton('Delete');
        $form = $button->form();

        $crawler = $client->submit($form);
        $this->assertCount(0, $crawler->filter('h2'));
    }

}
