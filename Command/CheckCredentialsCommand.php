<?php

namespace Modera\SecurityBundle\Command;

use Doctrine\ORM\EntityManagerInterface;
use Modera\SecurityBundle\Entity\User;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

/**
 * @author    Sergei Lissovski <sergei.lissovski@modera.org>
 * @copyright 2019 Modera Foundation
 */
class CheckCredentialsCommand extends Command
{
    private EntityManagerInterface $em;

    private UserPasswordHasherInterface $hasher;

    public function __construct(EntityManagerInterface $em, UserPasswordHasherInterface $hasher)
    {
        $this->em = $em;
        $this->hasher = $hasher;

        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->setName('modera:security:check-credentials')
            ->setDescription('Check user credentials.')
            ->addArgument('property', InputArgument::OPTIONAL, '', 'username')
            ->addOption('identifier', null, InputOption::VALUE_REQUIRED)
            ->addOption('password', null, InputOption::VALUE_REQUIRED)
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        /** @var string $property */
        $property = $input->getArgument('property');

        /** @var string $identifier */
        $identifier = $input->getOption('identifier');

        /** @var string $password */
        $password = $input->getOption('password');

        /** @var array<string, mixed> $criteria */
        $criteria = [
            $property => $identifier,
        ];

        $user = $this->em->getRepository(User::class)->findOneBy($criteria);

        if (!$user) {
            $output->writeln(\sprintf('<error>User with identifier "%s" not found!</error>', $identifier));

            return 1;
        }

        if (!$this->hasher->isPasswordValid($user, $password)) {
            $output->writeln('<error>Password not valid!</error>');

            return 2;
        }

        $output->writeln('<info>Password is valid!</info>');

        return 0;
    }
}
