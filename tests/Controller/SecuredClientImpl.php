<?php

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\KernelBrowser;

trait SecuredClientImpl {

    /**
     * Creates an authenticated KernelBrowser.
     *
     * @param array $options An array of options to pass to the createKernel method
     * @param array $server  An array of server parameters
     *
     * @return KernelBrowser A KernelBrowser instance
     */
    static protected function getAuthenticatedClient(array $options = [], array $server = []) {
        $client = static::createClient($options, $server);
        $crawler = $client->request('GET', '/login');
        $loginForm = $crawler->selectButton('Sign in')->form();
        $client->submit($loginForm, [
            'username' => 'admin',
            'password' => 'toto',
            '_csrf_token' => $loginForm->get('_csrf_token')->getValue(),
        ]);

        return $client;
    }

}
