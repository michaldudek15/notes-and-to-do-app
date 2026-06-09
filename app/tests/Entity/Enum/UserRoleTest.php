<?php

/**
 * User role enum tests.
 */

namespace App\Tests\Entity\Enum;

use App\Entity\Enum\UserRole;
use PHPUnit\Framework\TestCase;

class UserRoleTest extends TestCase
{
    public function testLabelForRoleUser(): void
    {
        self::assertSame('label.role_user', UserRole::ROLE_USER->label());
    }

    public function testLabelForRoleAdmin(): void
    {
        self::assertSame('label.role_admin', UserRole::ROLE_ADMIN->label());
    }
}
