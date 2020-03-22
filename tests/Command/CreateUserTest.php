<?php

use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;

class CreateUserTest extends KernelTestCase {

    public function testExecute() {
        $kernel = static::createKernel();
        $application = new Application($kernel);

        $command = $application->find('app:create-user');
        $commandTester = new CommandTester($command);
        $commandTester->setInputs(['yolo']);

        $commandTester->execute([
            'user' => 'Oshii',
        ]);

        $output = $commandTester->getDisplay();
        $this->assertStringContainsString('Oshii', $output);
    }

}
