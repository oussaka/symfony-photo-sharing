<?php

namespace AppBundle\Security;

use AppBundle\Entity\Image;
use AppBundle\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class ImageVoter extends Voter
{
    const VIEW = 'view';
    const EDIT = 'edit';

    protected function supports($attribute, $subject)
    {
        if (!in_array($attribute, array(self::VIEW, self::EDIT))) {
            return false;
        }

        if (!$subject instanceof Image) {
            return false;
        }

        return true;
    }

    protected function voteOnAttribute($attribute, $subject, TokenInterface $token)
    {
        $user = $token->getUser();

        if (!$user instanceof User) {
            return false;
        }

        // you know $subject is a Image object, thanks to supports
        /** @var Image $image */
        $image = $subject;

        switch($attribute) {
            case self::VIEW:
                return $this->canView($image, $user);
            case self::EDIT:
                return $this->canEdit($image, $user);
        }

        throw new \LogicException('This code should not be reached!');
    }

    private function canView(Image $image, User $user)
    {
        if ($this->canEdit($image, $user)) {
            return true;
        }

        return !$image->isPrivate();
    }

    private function canEdit(Image $image, User $user)
    {
        return $user === $image->getUser();
    }
}