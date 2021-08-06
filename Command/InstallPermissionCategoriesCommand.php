<?php

namespace Modera\SecurityBundle\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Modera\SecurityBundle\DataInstallation\PermissionAndCategoriesInstaller;

/**
 * @author Sergei Lissovski <sergei.lissovski@modera.org>
 * @copyright 2014 Modera Foundation
 */
class InstallPermissionCategoriesCommand extends ContainerAwareCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('modera:security:install-permission-categories')
            ->setDescription('Installs permission categories.')
        ;
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        // set locale to undefined, then we will receive translations from source code
        if ($this->getContainer()->has('translator')) {
            /* @var TranslatorInterface $translator */
            $translator = $this->getContainer()->get('translator');
            $translator->setLocale('__');
        }

        /* @var PermissionAndCategoriesInstaller $dataInstaller */
        $dataInstaller = $this->getContainer()->get('modera_security.data_installation.permission_and_categories_installer');

        $stats = $dataInstaller->installCategories();

        $output->writeln(' >> Installed: '.$stats['installed']);
        $output->writeln(' >> Removed: '.$stats['removed']);
    }
}
