<?php

namespace Modera\SecurityBundle\Command;

use Doctrine\ORM\EntityManager;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Modera\SecurityBundle\Entity\User;

/**
 * @author    Sergei Lissovski <sergei.lissovski@modera.org>
 * @copyright 2019 Modera Foundation
 */
class CheckCredentialsCommand extends ContainerAwareCommand
{
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
        /* @var EntityManager $em */
        $em = $this->getContainer()->get('doctrine.orm.entity_manager');

        $username = $input->getOption('username');
        $password = $input->getOption('password');

        /* @var UserPasswordEncoderInterface $encoder */
        $encoder = $this->getContainer()->get('security.password_encoder');

        $criteria = array();
        $criteria[$input->getArgument('property')] = $username;

        $user = $em->getRepository(User::clazz())->findOneBy($criteria);

        if (!$user) {
            $output->writeln(sprintf('<error>User "%s" not found!</error>', $username));
            return 1;
        }

        if (!$encoder->isPasswordValid($user, $password)) {
            $output->writeln('<error>Password not valid!</error>');
            return 2;
        }

        $output->writeln('<info>Password is valid!</info>');
    }
}
