<?php

namespace Modera\SecurityBundle\Tests\Unit\Entity;

use Modera\SecurityBundle\Entity\PermissionCategory;

/**
 * @author    Sergei Lissovski <sergei.lissovski@modera.org>
 * @copyright 2014 Modera Foundation
 */
class PermissionCategoryTest extends \PHPUnit\Framework\TestCase
{
    public function testConstructorAndGetters()
    {
        $pc = new PermissionCategory('foo name', 'foo_name');

        $this->assertEquals('foo name', $pc->getName());
        $this->assertEquals('foo_name', $pc->getTechnicalName());
    }
}
