<?php

namespace App\Tests\Command;

use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Console\Tester\CommandTester;

class PdfGeneratorTest extends KernelTestCase
{

    const testfile = 'test.pdf';

    protected function setUp(): void
    {
        static::bootKernel();
    }

    public function testExecute()
    {
        $kernel = static::createKernel();
        $application = new Application($kernel);

        $command = $application->find('pdf:generate');
        $commandTester = new CommandTester($command);
        $commandTester->setInputs([0]);
        $commandTester->execute(['filename' => self::testfile]);

        $this->assertFileExists(self::testfile);
        unlink(self::testfile);
    }

}
