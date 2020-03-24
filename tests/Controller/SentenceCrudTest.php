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

    public function testPinVertex() {
        $client = static::getAuthenticatedClient();
        // add another vertex :
        $crawler = $client->request('GET', '/docu/show/TMP/append');

        $client->submitForm('Save', [
            'sentence[key]' => 'Togusa',
            'sentence[category]' => 'human',
            'sentence[content]' => 'Mateba Unica'
        ]);
        $this->assertEquals(302, $client->getResponse()->getStatusCode());
        $crawler = $client->followRedirect();
        $this->assertEquals('Togusa human', $crawler->filter('.mobile article h2')->eq(0)->text());
        $this->assertEquals('Kusanagi Motoko cyborg', $crawler->filter('.mobile article h2')->eq(1)->text());
        // click on pin :
        $pinIcon = $crawler->filter('i.icon-pin');
        $this->assertCount(1, $pinIcon); // 1 because Togusa has no pin icon since it's the first on the list, the button is hidden
        $pinLink = $pinIcon->parents()->first()->link();
        $client->click($pinLink);
        $crawler = $client->followRedirect();
        $this->assertEquals('Kusanagi Motoko cyborg', $crawler->filter('.mobile article h2')->eq(0)->text(), 'Vertex Motoko is not the first on the list');
    }

    public function testDelete() {
        $client = static::getAuthenticatedClient();
        $client->request('GET', '/docu/show/TMP/delete/Kusanagi Motoko');
        $client->submitForm('Delete');
        $client->request('GET', '/docu/show/TMP/delete/Togusa');
        $client->submitForm('Delete');
        $crawler = $client->followRedirect();

        $this->assertCount(0, $crawler->filter('h2'));
    }

}
