<?php

/**
 * Note voter.
 */

namespace App\Security\Voter;

use App\Entity\Note;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * Class NoteVoter.
 */
class NoteVoter extends Voter
{
    /**
     * Edit permission.
     *
     * @const string
     */
    private const EDIT = 'EDIT';

    /**
     * View permission.
     *
     * @const string
     */
    private const VIEW = 'VIEW';

    /**
     * Delete permission.
     *
     * @const string
     */
    private const DELETE = 'DELETE';

    /**
     * Determines if the attribute and subject are supported by this voter.
     *
     * @param string $attribute An attribute
     * @param mixed  $subject   The subject to secure, e.g. an object the user wants to access or any other PHP type
     *
     * @return bool Result
     */
    protected function supports(string $attribute, mixed $subject): bool
    {
        return in_array($attribute, [self::EDIT, self::VIEW, self::DELETE])
            && $subject instanceof Note;
    }// end supports()

    /**
     * Perform a single access check operation on a given attribute, subject and token.
     * It is safe to assume that $attribute and $subject already passed the "supports()" method check.
     *
     * @param string         $attribute Permission name
     * @param mixed          $subject   Object
     * @param TokenInterface $token     Security token
     *
     * @return bool Vote result
     */
    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        $user = $token->getUser();
        if (!$user instanceof UserInterface) {
            return false;
        }

        if (!$subject instanceof Note) {
            return false;
        }

        return match ($attribute) {
            self::EDIT => $this->canEdit($subject, $user),
            self::VIEW => $this->canView($subject, $user),
            self::DELETE => $this->canDelete($subject, $user),
            default => false,
        };
    }// end voteOnAttribute()

    /**
     * Checks if user can edit note.
     *
     * @param Note          $note Note entity
     * @param UserInterface $user User
     *
     * @return bool Result
     */
    private function canEdit(Note $note, UserInterface $user): bool
    {
        return $note->getAuthor() === $user;
    }// end canEdit()

    /**
     * Checks if user can view note.
     *
     * @param Note          $note Note entity
     * @param UserInterface $user User
     *
     * @return bool Result
     */
    private function canView(Note $note, UserInterface $user): bool
    {
        return $note->getAuthor() === $user;
    }// end canView()

    /**
     * Checks if user can delete note.
     *
     * @param Note          $note Note entity
     * @param UserInterface $user User
     *
     * @return bool Result
     */
    private function canDelete(Note $note, UserInterface $user): bool
    {
        return $note->getAuthor() === $user;
    }// end canDelete()
}// end class
