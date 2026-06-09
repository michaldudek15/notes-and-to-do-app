<?php

/**
 * Task repository tests.
 */

namespace App\Tests\Repository;

use App\Tests\AbstractWebTestCase;

class TaskRepositoryTest extends AbstractWebTestCase
{
    public function testCountByCategoryReturnsNumberOfTasksInGivenCategory(): void
    {
        $user = $this->createUser();
        $category = $this->createCategory($user, 'Counted category');
        $otherCategory = $this->createCategory($user, 'Other category');

        $this->createTask($user, $category, 'Task 1');
        $this->createTask($user, $category, 'Task 2');
        $this->createTask($user, $otherCategory, 'Other task');

        self::assertSame(2, $this->taskRepository->countByCategory($category));
    }

    public function testCountByCategoryReturnsZeroWhenCategoryHasNoTasks(): void
    {
        $user = $this->createUser();
        $category = $this->createCategory($user, 'Empty category');

        self::assertSame(0, $this->taskRepository->countByCategory($category));
    }
}
