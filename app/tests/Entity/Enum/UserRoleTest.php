<?php

/**
 * User role enum tests.
 */

namespace App\Tests\Entity\Enum;

use App\Entity\Enum\UserRole;
use PHPUnit\Framework\TestCase;

/**
 * User role enum tests.
 */
class UserRoleTest extends TestCase
{
    /**
     * It maps ROLE_USER to expected translation key.
     */
    public function testLabelForRoleUser(): void
    {
        self::assertSame('label.role_user', UserRole::ROLE_USER->label());
    }

    /**
     * It maps ROLE_ADMIN to expected translation key.
     */
    public function testLabelForRoleAdmin(): void
    {
        self::assertSame('label.role_admin', UserRole::ROLE_ADMIN->label());
    }
}
