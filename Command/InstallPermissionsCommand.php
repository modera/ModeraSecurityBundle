<?php

namespace Modera\SecurityBundle\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use Modera\SecurityBundle\DataInstallation\PermissionAndCategoriesInstaller;

/**
 * @author Sergei Lissovski <sergei.lissovski@modera.org>
 */
class InstallPermissionsCommand extends Command
{
    private TranslatorInterface $translator;

    private PermissionAndCategoriesInstaller $dataInstaller;

    public function __construct(TranslatorInterface $translator, PermissionAndCategoriesInstaller $dataInstaller)
    {
        $this->translator = $translator;
        $this->dataInstaller = $dataInstaller;

        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->setName('modera:security:install-permissions')
            ->setDescription('Installs permissions.')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        // set locale to undefined, then we will receive translations from source code
        $this->translator->setLocale('__');

        $stats = $this->dataInstaller->installPermissions();

        $output->writeln(' >> Installed: '.$stats['installed']);
        $output->writeln(' >> Removed: '.$stats['removed']);

        return 0;
    }
}
