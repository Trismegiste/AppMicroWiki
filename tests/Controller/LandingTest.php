<?php

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class LandingTest extends WebTestCase {

    public function testDirectLogin() {
        $client = static::createClient();
        $crawler = $client->request('GET', '/login');
        $loginForm = $crawler->selectButton('Sign in')->form();
        $client->submit($loginForm, [
            'username' => 'admin',
            'password' => 'toto',
            '_csrf_token' => $loginForm->get('_csrf_token')->getValue(),
        ]);
        $this->assertResponseRedirects('/', 302);
        $client->followRedirect();
        $this->assertResponseRedirects('/docu/list', 302);
    }

}
