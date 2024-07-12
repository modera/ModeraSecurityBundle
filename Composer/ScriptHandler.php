<?php

namespace Modera\SecurityBundle\Composer;

use Composer\Script\Event;
use Modera\ModuleBundle\Composer\AbstractScriptHandler;

/**
 * @author    Sergei Vizel <sergei.vizel@modera.org>
 * @copyright 2015 Modera Foundation
 */
class ScriptHandler extends AbstractScriptHandler
{
    public static function installPermissions(Event $event): void
    {
        $options = static::getOptions($event);

        /** @var string $binDir */
        $binDir = $options['symfony-bin-dir'];

        echo '>>> ModeraSecurityBundle: Install permissions'.PHP_EOL;

        if (!\is_dir($binDir)) {
            echo 'The symfony-bin-dir ('.$binDir.') specified in composer.json was not found in '.\getcwd().', can not install permissions.'.PHP_EOL;

            return;
        }

        /** @var int $processTimeout */
        $processTimeout = $options['process-timeout'];

        static::executeCommand($event, $binDir, 'modera:security:install-permission-categories', $processTimeout);
        static::executeCommand($event, $binDir, 'modera:security:install-permissions', $processTimeout);

        echo '>>> ModeraSecurityBundle: done'.PHP_EOL;
    }
}
