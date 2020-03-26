<?php

use App\Security\User;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Console\Tester\CommandTester;
use Trismegiste\Toolbox\MongoDb\DefaultRepository;

class CreateUserTest extends KernelTestCase {

    const username = 'Oshii';

    protected function setUp(): void {
        static::bootKernel();
    }

    public function testInitialize() {
        /* @var $repo DefaultRepository */
        $repo = self::$container->get('app.user.repository');
        $users = iterator_to_array($repo->search(['username' => self::username]));
        if (count($users)) {
            $repo->delete($users);
        }
        $this->assertCount(count($users), $users);
    }

    public function testExecute() {
        $kernel = static::createKernel();
        $application = new Application($kernel);

        $command = $application->find('app:create-user');
        $commandTester = new CommandTester($command);
        $commandTester->setInputs(['yolo']);

        $commandTester->execute([
            'user' => self::username,
        ]);

        $output = $commandTester->getDisplay();
        $this->assertStringContainsString(self::username, $output);

        $repo = self::$container->get('app.user.repository');
        $iter = $repo->search(['username' => self::username]);
        $found = iterator_to_array($iter);
        $this->assertCount(1, $found);
        $user = $found[0];
        $this->assertInstanceOf(User::class, $user);
        $this->assertEquals(self::username, $user->getUsername());
    }

}
