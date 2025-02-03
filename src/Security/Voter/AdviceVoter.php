<?php
// filepath: /home/user/site/openclassrooms/P12_API/ecogarden/src/Security/Voter/UserVoter.php
namespace App\Security\Voter;

use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Security\Core\User\UserInterface;

class AdviceVoter extends Voter
{
    const VIEW = 'view';
    const EDIT = 'edit';

    private $security;

    public function __construct(Security $security)
    {
        $this->security = $security;
    }

    protected function supports(string $attribute, $subject): bool
    {
        return in_array($attribute, [self::VIEW, self::EDIT])
            && $subject instanceof \App\Entity\Advice;
    }

    protected function voteOnAttribute(string $attribute, $subject, TokenInterface $token): bool
    {
        $user = $token->getUser();
        $subject_author = $subject->getAuthor();

        if (!$user instanceof UserInterface) {
            return false;
        }

        // If the user is an admin, grant access
        if ($this->security->isGranted('ROLE_ADMIN')) {
            return true;
        }

        // Check if the user is the same as the subject
        switch ($attribute) {
            case self::VIEW:
                return true;
            case self::EDIT:
                return $user === $subject_author;
        }

        return false;
    }
}
