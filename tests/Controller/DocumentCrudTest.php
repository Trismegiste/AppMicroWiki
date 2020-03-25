<?php

use App\Tests\Controller\SecuredClientImpl;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class DocumentCrudTest extends WebTestCase {

    use SecuredClientImpl;

    public function testList() {
        $client = static::getAuthenticatedClient();

        $crawler = $client->request('GET', '/docu/list');
        $this->assertEquals(200, $client->getResponse()->getStatusCode());

        $link = $crawler
                ->filter('a:contains("New document")')
                ->eq(0)
                ->link();

        $this->assertStringEndsWith('/docu/new', $link->getUri());
    }

    public function testNew() {
        $client = static::getAuthenticatedClient();

        $crawler = $client->request('GET', '/docu/new');
        $this->assertEquals(200, $client->getResponse()->getStatusCode());

        $form = $crawler->selectButton('Save')->form();

        $form['document[title]'] = 'TMP';
        $form['document[description]'] = 'YOLO!';
        $crawler = $client->submit($form);
        $this->assertEquals(302, $client->getResponse()->getStatusCode());
        $crawler = $client->followRedirect();

        $result = [];
        $this->assertEquals(1, preg_match('#/docu/show/([0-9a-f]{24})$#', $crawler->getUri(), $result));

        return $result[1];
    }

    /** @depends testNew */
    public function testShow(string $pkDoc) {
        $client = static::getAuthenticatedClient();
        $client->request('GET', "/docu/show/$pkDoc");
        $this->assertEquals(200, $client->getResponse()->getStatusCode());
    }

}
