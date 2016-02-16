<?php

namespace AppBundle\Security;

use AppBundle\Entity\Image;
use AppBundle\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class ImageVoter extends Voter
{
    // these strings are just invented: you can use anything
    const VIEW = 'view';
    const EDIT = 'edit';

    protected function supports($attribute, $subject)
    {
        // if the attribute isn't one we support, return false
        if (!in_array($attribute, array(self::VIEW, self::EDIT))) {
            return false;
        }

        // only vote on Post objects inside this voter
        if (!$subject instanceof Image) {
            return false;
        }

        return true;
    }

    protected function voteOnAttribute($attribute, $subject, TokenInterface $token)
    {
        $user = $token->getUser();

        if (!$user instanceof User) {
            // the user must be logged in; if not, deny access
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
        // if they can edit, they can view
        if ($this->canEdit($image, $user)) {
            return true;
        }

        // the Post object could have, for example, a method isPrivate()
        // that checks a boolean $private property
        return !$image->isPrivate();
    }

    private function canEdit(Image $image, User $user)
    {
        // this assumes that the data object has a getOwner() method
        // to get the entity of the user who owns this data object
        return $user === $image->getUser();
    }
}