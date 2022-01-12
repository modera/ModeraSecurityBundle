<?php

namespace Modera\SecurityBundle\Command;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Modera\SecurityBundle\Entity\User;

/**
 * @author    Sergei Lissovski <sergei.lissovski@modera.org>
 * @copyright 2014 Modera Foundation
 */
class CreateUserCommand extends Command
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
            ->setName('modera:security:create-user')
            ->setDescription('Allows to create a user that you can later user to authenticate.')
            ->addOption('no-interactions', null, InputOption::VALUE_NONE)
            ->addOption('username', null, InputOption::VALUE_OPTIONAL)
            ->addOption('email', null, InputOption::VALUE_OPTIONAL)
            ->addOption('password', null, InputOption::VALUE_OPTIONAL)
        ;
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $username = $input->getOption('username');
        $email = $input->getOption('email');
        $password = $input->getOption('password');

        if (false === $input->getOption('no-interactions')) {
            /* @var QuestionHelper $questionHelper */
            $questionHelper = $this->getHelper('question');

            $output->writeln('<info>This command will let you to create a test user that you can user to authenticated to administration interface</info>');
            $output->write(PHP_EOL);

            $username = $questionHelper->ask($input, $output, new Question('<question>Username:</question> '));
            $email = $questionHelper->ask($input, $output, new Question('<question>Email:</question> '));

            do {
                $password = $questionHelper->ask(
                    $input, $output, $this->createHiddenQuestion('<question>Password:</question> ')
                );
                $passwordConfirm = $questionHelper->ask(
                    $input, $output, $this->createHiddenQuestion('<question>Password again:</question> ')
                );

                if ($password != $passwordConfirm) {
                    $output->writeln('<error>Entered passwords do not match, please try again</error>');
                }
            } while ($password != $passwordConfirm);

            $output->write(PHP_EOL);
        }

        $user = new User();
        $user->setEmail($email);
        $user->setUsername($username);
        $user->setPassword($this->encoder->encodePassword($user, $password));

        $this->em->persist($user);
        $this->em->flush();

        $output->writeln(sprintf(
            '<info>Great success! User "%s" has been successfully created!</info>',
            $user->getUsername()
        ));

        return 0;
    }

    private function createHiddenQuestion($text)
    {
        $question = new Question($text);
        $question->setHidden(true);

        return $question;
    }
}
