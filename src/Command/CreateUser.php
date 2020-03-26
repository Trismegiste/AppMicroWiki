<?php

namespace App\Command;

use App\Security\User;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Security\Core\Encoder\EncoderFactoryInterface;
use Trismegiste\Toolbox\MongoDb\Repository;

/**
 * Description of CreateUserCommand
 */
class CreateUser extends Command {

    protected static $defaultName = 'app:create-user';
    private $encoderFactory;
    private $repository;

    public function __construct(EncoderFactoryInterface $encoderFactory, Repository $userRepo) {
        $this->encoderFactory = $encoderFactory;
        $this->repository = $userRepo;
        parent::__construct();
    }

    protected function configure() {
        $this->setDescription('Create a new admin user')
                ->addArgument('user', InputArgument::REQUIRED, 'username');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int {
        $io = new SymfonyStyle($input, $output);
        $username = $input->getArgument('user');
        $io->title($this->getDescription() . ' : ' . $username);

        $password = $io->ask('Password');
        $encoder = $this->encoderFactory->getEncoder(User::class);
        $encodedPwd = $encoder->encodePassword($password, $this->generateSalt());

        $user = new User($username, $encodedPwd);
        // save to a mongo collection
        $this->repository->save($user);

        return 0;
    }

    private function generateSalt(): string {
        return base64_encode(random_bytes(30));
    }

}
