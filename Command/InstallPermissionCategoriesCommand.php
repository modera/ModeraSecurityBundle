<?php

namespace Modera\SecurityBundle\Command;

use Modera\SecurityBundle\DataInstallation\PermissionAndCategoriesInstaller;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

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
        /* @var PermissionAndCategoriesInstaller $dataInstaller */
        $dataInstaller = $this->getContainer()->get('modera_security.data_installation.permission_and_categories_installer');

        $stats = $dataInstaller->installCategories();

        $output->writeln(' >> Installed: '.$stats['installed']);
        $output->writeln(' >> Removed: '.$stats['removed']);
    }
}
