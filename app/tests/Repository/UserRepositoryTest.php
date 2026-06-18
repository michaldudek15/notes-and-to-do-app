<?php

/**
 * User repository tests.
 */

namespace App\Tests\Repository;

use App\Entity\User;
use App\Tests\AbstractWebTestCase;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;

/**
 * User repository integration tests.
 */
class UserRepositoryTest extends AbstractWebTestCase
{
    /**
     * It persists the new hashed password.
     */
    public function testUpgradePasswordUpdatesPersistedPassword(): void
    {
        $user = $this->createUser();
        $userId = $user->getId();

        $this->userRepository->upgradePassword($user, 'rehashed-password');

        $updated = $this->userRepository->find($userId);
        self::assertInstanceOf(User::class, $updated);
        self::assertSame('rehashed-password', $updated->getPassword());
    }

    /**
     * It throws when user implementation is unsupported.
     */
    public function testUpgradePasswordThrowsForUnsupportedUser(): void
    {
        $unsupportedUser = new class() implements PasswordAuthenticatedUserInterface {
            /**
             * Returns a password required by the interface.
             */
            public function getPassword(): ?string
            {
                return 'password';
            }
        };

        $this->expectException(UnsupportedUserException::class);

        $this->userRepository->upgradePassword($unsupportedUser, 'new-password');
    }

    /**
     * It deletes user and all authored dependent data.
     */
    public function testDeleteRemovesUserWithAuthoredData(): void
    {
        $user = $this->createUser();
        $otherUser = $this->createUser();

        $userCategory = $this->createCategory($user, 'User category');
        $otherCategory = $this->createCategory($otherUser, 'Other category');

        $ownedNote = $this->createNote($user, $userCategory, 'Owned note');
        $ownedTask = $this->createTask($user, $userCategory, 'Owned task');

        $noteInUserCategoryByOther = $this->createNote($otherUser, $userCategory, 'Other note in user category');
        $taskInUserCategoryByOther = $this->createTask($otherUser, $userCategory, 'Other task in user category');

        $otherUserNote = $this->createNote($otherUser, $otherCategory, 'Other user note');
        $otherUserTask = $this->createTask($otherUser, $otherCategory, 'Other user task');

        $ownedNoteId = $ownedNote->getId();
        $ownedTaskId = $ownedTask->getId();
        $userCategoryId = $userCategory->getId();
        $noteInUserCategoryByOtherId = $noteInUserCategoryByOther->getId();
        $taskInUserCategoryByOtherId = $taskInUserCategoryByOther->getId();
        $otherUserNoteId = $otherUserNote->getId();
        $otherUserTaskId = $otherUserTask->getId();
        $otherCategoryId = $otherCategory->getId();
        $deletedUserId = $user->getId();
        $otherUserId = $otherUser->getId();

        $this->userRepository->delete($user);

        self::assertNull($this->userRepository->find($deletedUserId));
        self::assertNull($this->categoryRepository->find($userCategoryId));
        self::assertNull($this->noteRepository->find($ownedNoteId));
        self::assertNull($this->taskRepository->find($ownedTaskId));
        self::assertNull($this->noteRepository->find($noteInUserCategoryByOtherId));
        self::assertNull($this->taskRepository->find($taskInUserCategoryByOtherId));

        self::assertNotNull($this->userRepository->find($otherUserId));
        self::assertNotNull($this->categoryRepository->find($otherCategoryId));
        self::assertNotNull($this->noteRepository->find($otherUserNoteId));
        self::assertNotNull($this->taskRepository->find($otherUserTaskId));
    }
}
