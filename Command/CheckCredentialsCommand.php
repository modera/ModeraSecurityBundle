<?php

namespace Modera\SecurityBundle\Command;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Modera\SecurityBundle\Entity\User;

/**
 * @author    Sergei Lissovski <sergei.lissovski@modera.org>
 * @copyright 2019 Modera Foundation
 */
class CheckCredentialsCommand extends Command
{
    private EntityManagerInterface $em;

    private UserPasswordEncoderInterface $encoder;

    public function __construct(EntityManagerInterface $em, UserPasswordEncoderInterface $encoder)
    {
        $this->em = $em;
        $this->encoder = $encoder;

        parent::__construct();
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('modera:security:check-credentials')
            ->setDescription('Check user credentials.')
            ->addOption('username', null, InputOption::VALUE_REQUIRED)
            ->addOption('password', null, InputOption::VALUE_REQUIRED)
            ->addArgument('property', InputArgument::OPTIONAL, '', 'username')
        ;
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $username = $input->getOption('username');
        $password = $input->getOption('password');

        $criteria = array();
        $criteria[$input->getArgument('property')] = $username;

        $user =  $this->em->getRepository(User::class)->findOneBy($criteria);

        if (!$user) {
            $output->writeln(sprintf('<error>User "%s" not found!</error>', $username));
            return 1;
        }

        if (!$this->encoder->isPasswordValid($user, $password)) {
            $output->writeln('<error>Password not valid!</error>');
            return 2;
        }

        $output->writeln('<info>Password is valid!</info>');

        return 0;
    }
}
