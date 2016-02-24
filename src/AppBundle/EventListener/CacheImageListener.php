<?php

namespace AppBundle\EventListener;

use Doctrine\ORM\Event\LifecycleEventArgs;
use AppBundle\Entity\Image;

class CacheImageListener
{
    protected $cacheManager;

    public function __construct($cacheManager)
    {
        $this->cacheManager = $cacheManager;
    }

    public function preRemove(LifecycleEventArgs $args)
    {
        $entity = $args->getEntity();

        if ($entity instanceof Image) {
            $this->cacheManager->remove($entity->getWebPath());
        }
    }
}