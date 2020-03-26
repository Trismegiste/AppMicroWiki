<?php

namespace App\Tests\Controller;

use App\Tests\Command\CreateUserTest;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Zend\EventManager\Exception\RuntimeException;

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
            'username' => CreateUserTest::username,
            'password' => CreateUserTest::password,
            '_csrf_token' => $loginForm->get('_csrf_token')->getValue(),
        ]);

        if ($client->getResponse()->headers->get('Location') === '/login') {
            throw new RuntimeException("Bad configuration for TEST : authentication failed");
        }

        return $client;
    }

}
