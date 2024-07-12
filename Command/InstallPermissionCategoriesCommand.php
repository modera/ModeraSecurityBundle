<?php

namespace Modera\SecurityBundle\Command;

use Modera\SecurityBundle\DataInstallation\PermissionAndCategoriesInstaller;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * @author Sergei Lissovski <sergei.lissovski@modera.org>
 * @copyright 2014 Modera Foundation
 */
class InstallPermissionCategoriesCommand extends Command
{
    private TranslatorInterface $translator;

    private PermissionAndCategoriesInstaller $dataInstaller;

    public function __construct(TranslatorInterface $translator, PermissionAndCategoriesInstaller $dataInstaller)
    {
        $this->translator = $translator;
        $this->dataInstaller = $dataInstaller;

        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->setName('modera:security:install-permission-categories')
            ->setDescription('Installs permission categories.')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        // set locale to undefined, then we will receive translations from source code
        if (\method_exists($this->translator, 'setLocale')) {
            $this->translator->setLocale('__');
        }

        $stats = $this->dataInstaller->installCategories();

        $output->writeln(' >> Installed: '.$stats['installed']);
        // $output->writeln(' >> Removed: '.$stats['removed']);

        return 0;
    }
}
