<?php

use App\Repository\DocumentRepo;
use App\Tests\Controller\SecuredClientImpl;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Trismegiste\MicroWiki\Document;

class SentenceCrudTest extends WebTestCase {

    use SecuredClientImpl;

    public function testCreate() {
        static::createClient();
        $doc = new Document('Test', 'Nihil');
        $repo = static::$container->get('app.document.repository');
        $repo->save($doc);
        $this->assertRegExp('/^[0-9a-z]{24}$/', $doc->getPk());

        return '/docu/show/' . $doc->getPk();
    }

    /** @depends testCreate */
    public function testAppend($urlDoc) {
        $client = static::getAuthenticatedClient();
        $crawler = $client->request('GET', $urlDoc . '/append/yolo');
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

        return $urlDoc;
    }

    /** @depends testCreate */
    public function testEdit($urlDoc) {
        $client = static::getAuthenticatedClient();
        $crawler = $client->request('GET', $urlDoc . '/edit/Motoko');

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

    /** @depends testCreate */
    public function testDeleteButtonWhenFocus($urlDoc) {
        $client = static::getAuthenticatedClient();
        $crawler = $client->request('GET', $urlDoc);
        $this->assertCount(1, $crawler->filter('article footer i.icon-trash'));
    }

    /** @depends testCreate */
    public function testXhrLinkAutocomplete($urlDoc) {
        $client = static::getAuthenticatedClient();
        $crawler = $client->request('GET', $urlDoc . '/link/find/s');
        $this->assertEquals(['Section 9'], json_decode($client->getResponse()->getContent(), true));
        $crawler = $client->request('GET', $urlDoc . '/link/find/k');
        $this->assertEquals(['Kusanagi Motoko'], json_decode($client->getResponse()->getContent(), true));
    }

    /** @depends testCreate */
    public function testXhrCategoryAutocomplete($urlDoc) {
        $client = static::getAuthenticatedClient();
        $crawler = $client->request('GET', $urlDoc . '/category/find/cy');
        $this->assertEquals(['cyborg'], json_decode($client->getResponse()->getContent(), true));
    }

    /** @depends testCreate */
    public function testQrCode($urlDoc) {
        $client = static::getAuthenticatedClient();
        $crawler = $client->request('GET', $urlDoc . '/qrcode/Kusanagi Motoko');
        $this->assertPageTitleContains('QR Code');
    }

    /** @depends testCreate */
    public function testPinVertex($urlDoc) {
        $client = static::getAuthenticatedClient();
        // add another vertex :
        $crawler = $client->request('GET', $urlDoc . '/append');

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

    /** @depends testCreate */
    public function testMissingWikiLink($urlDoc) {
        $client = static::getAuthenticatedClient();
        $crawler = $client->request('GET', $urlDoc);
        $newVertex = $crawler->selectLink('Section 9')->link();
        $this->assertNotNull($newVertex);
        $crawler = $client->click($newVertex);
        $this->assertStringEndsWith('/append/Section%209', $crawler->getUri());
        $this->assertEquals('Section 9', $crawler->filter('form #sentence_key')->attr('value'));

        $client->submitForm('Save', [
            'sentence[key]' => 'Section 9',
            'sentence[category]' => 'Japan',
            'sentence[content]' => 'Cyber-counter-terrorism'
        ]);
        $crawler = $client->followRedirect();
        $this->assertEquals('Section 9 Japan', $crawler->filter('.mobile article h2')->eq(0)->text());
    }

    /** @depends testCreate */
    public function testExistingLink($urlDoc) {
        $client = static::getAuthenticatedClient();
        $crawler = $client->request('GET', $urlDoc);
        $link = $crawler->filter('article .content')->selectLink('Section 9')->eq(0)->link();
        $client->click($link);
        $this->assertResponseRedirects($urlDoc, 302);
    }

    /** @depends testCreate */
    public function testDelete($urlDoc) {
        $client = static::getAuthenticatedClient();
        foreach (['Kusanagi Motoko', 'Togusa', 'Section 9']as $key) {
            $client->request('GET', $urlDoc . '/delete/' . $key);
            $client->submitForm('Delete');
        }
        $crawler = $client->followRedirect();

        $this->assertCount(0, $crawler->filter('.mobile article h2'));
    }

}
