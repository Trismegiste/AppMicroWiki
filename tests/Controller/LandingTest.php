<?php

use App\Tests\Controller\SecuredClientImpl;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class LandingTest extends WebTestCase {

    use SecuredClientImpl;

    public function testDirectLogin() {
        $client = static::getAuthenticatedClient();
        $this->assertResponseRedirects('/', 302);
        $client->followRedirect();
        $this->assertResponseRedirects('/docu/list', 302);
    }

    public function testLandingPage() {
        $client = static::getAuthenticatedClient();
        $client->request('GET', '/docu/list');
        $this->assertPageTitleContains('Listing');
    }

}
