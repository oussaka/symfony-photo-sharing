<?php

namespace AppBundle\EventListener;

use Doctrine\Common\Persistence\ObjectManager;
use FOS\UserBundle\Event\FilterUserResponseEvent;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Http\Event\InteractiveLoginEvent;

class UserLocaleListener
{
    private $om;

    private $urlGenerator;

    private $session;

    private $locales = array();

    public function __construct(ObjectManager $om, UrlGeneratorInterface $urlGenerator, Session $session, $locales)
    {
        $this->om = $om;
        $this->urlGenerator = $urlGenerator;
        $this->session = $session;
        $this->locales = explode('|', trim($locales));
    }

    public function onInteractiveLogin(InteractiveLoginEvent $event, $eventName, EventDispatcherInterface $dispatcher)
    {
        $user = $event->getAuthenticationToken()->getUser();

        if (null !== $user->getLocale()) {
            $this->session->set('_locale', $user->getLocale());
            $locale = $user->getLocale();

            $dispatcher->addListener(KernelEvents::RESPONSE,  function(FilterResponseEvent $event) use ($locale) {
                $response = new RedirectResponse($this->urlGenerator->generate("homepage", array('_locale' => $locale)));
                $event->setResponse($response);
            });
        }
    }

    public function onRegistrationCompleted(FilterUserResponseEvent $event)
    {
        $user = $event->getUser();
        $request = $event->getRequest();

        $preferredLanguage = $request->getPreferredLanguage($this->locales);
        $this->session->set('_locale', $preferredLanguage);

        $user->setLocale($preferredLanguage);

        $this->om->persist($user);
        $this->om->flush();
    }

    public function onProfileEditCompleted(FilterUserResponseEvent $event, $eventName, EventDispatcherInterface $dispatcher)
    {
        $user = $event->getUser();
        $locale = $user->getLocale();
        $this->session->set('_locale', $user->getLocale());

        $dispatcher->addListener(KernelEvents::RESPONSE, function(FilterResponseEvent $event) use ($locale) {
            $response = new RedirectResponse($this->urlGenerator->generate("fos_user_profile_show", array('_locale' => $locale)));
            $event->setResponse($response);
        });
    }
}
