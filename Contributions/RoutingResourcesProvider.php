<?php

namespace Modera\SecurityBundle\Contributions;

use Modera\ExpanderBundle\Ext\ContributorInterface;

/**
 * @author    Sergei Vizel <sergei.vizel@modera.org>
 * @copyright 2014 Modera Foundation
 */
class RoutingResourcesProvider implements ContributorInterface
{
    public function getItems(): array
    {
        return [
            [
                'resource' => '@ModeraSecurityBundle/Controller/SecurityController.php',
                'type' => 'annotation',
            ],
        ];
    }
}
