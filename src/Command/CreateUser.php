<?php

namespace App\Command;

use App\Security\User;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * Description of CreateUserCommand
 */
class CreateUser extends Command {

    protected static $defaultName = 'app:create-user';

    protected function configure() {
        $this->setDescription('Create a new admin user')
                ->addArgument('user', InputArgument::REQUIRED, 'username');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int {
        $io = new SymfonyStyle($input, $output);
        $username = $input->getArgument('user');
        $io->title($this->getDescription() . ' : ' . $username);

        new User($username, 'toto');

        return 0;
    }

}
