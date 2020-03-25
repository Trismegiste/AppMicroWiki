<?php

use App\Repository\DocumentRepo;
use App\Tests\Controller\SecuredClientImpl;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Trismegiste\MicroWiki\Document;

class SentenceCrudTest extends WebTestCase {

    use SecuredClientImpl;

    protected $urlStart;

    public function testCreate() {
        $doc = new Document('Test', 'Nihil');
        $repo = static::$container->get(DocumentRepo::class);
        $repo->save($doc);
        $this->assertRegExp('/^[0-9a-z]{24}$/', $doc->getPk());
        $this->urlStart = '/docu/show/' . $doc->getPk();
    }

    public function testAppend() {
        $client = static::getAuthenticatedClient();
        $crawler = $client->request('GET', $this->urlStart . '/append/yolo');
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
        $crawler = $client->request('GET', $this->urlStart . '/edit/Motoko');

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
        $crawler = $client->request('GET', $this->urlStart);
        $this->assertCount(1, $crawler->filter('article footer i.icon-trash'));
    }

    public function testXhrLinkAutocomplete() {
        $client = static::getAuthenticatedClient();
        $crawler = $client->request('GET', $this->urlStart . '/link/find/s');
        $this->assertEquals(['Section 9'], json_decode($client->getResponse()->getContent(), true));
        $crawler = $client->request('GET', $this->urlStart . '/link/find/k');
        $this->assertEquals(['Kusanagi Motoko'], json_decode($client->getResponse()->getContent(), true));
    }

    public function testXhrCategoryAutocomplete() {
        $client = static::getAuthenticatedClient();
        $crawler = $client->request('GET', $this->urlStart . '/category/find/cy');
        $this->assertEquals(['cyborg'], json_decode($client->getResponse()->getContent(), true));
    }

    public function testQrCode() {
        $client = static::getAuthenticatedClient();
        $crawler = $client->request('GET', $this->urlStart . '/qrcode/Kusanagi Motoko');
        $this->assertPageTitleContains('QR Code');
    }

    public function testPinVertex() {
        $client = static::getAuthenticatedClient();
        // add another vertex :
        $crawler = $client->request('GET', $this->urlStart . '/append');

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

    public function testMissingWikiLink() {
        $client = static::getAuthenticatedClient();
        $crawler = $client->request('GET', $this->urlStart);
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

    public function testExistingLink() {
        $client = static::getAuthenticatedClient();
        $crawler = $client->request('GET', $this->urlStart);
        $link = $crawler->filter('article .content')->selectLink('Section 9')->eq(0)->link();
        $client->click($link);
        $this->assertResponseRedirects('/docu/show/TMP', 302);
    }

    public function testDelete() {
        $client = static::getAuthenticatedClient();
        foreach (['Kusanagi Motoko', 'Togusa', 'Section 9']as $key) {
            $client->request('GET', $this->urlStart . '/delete/' . $key);
            $client->submitForm('Delete');
        }
        $crawler = $client->followRedirect();

        $this->assertCount(0, $crawler->filter('.mobile article h2'));
    }

}
