<?php

/*
 * AppMicroWiki
 */

namespace App\Tests\Command;

use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Console\Tester\CommandTester;

class BulkImportTest extends KernelTestCase
{

    protected function setUp(): void
    {
        static::bootKernel();
    }

    public function testExecute()
    {
        $kernel = static::createKernel();
        $application = new Application($kernel);

        $command = $application->find('app:import');
        $commandTester = new CommandTester($command);
        $commandTester->setInputs(['phpunit', 'empty']);
        $commandTester->execute(['filename' => __DIR__ . '/../fixtures/import.fods']);

        $output = $commandTester->getDisplay();
        $this->assertStringContainsString('successfully', $output);
    }

}
