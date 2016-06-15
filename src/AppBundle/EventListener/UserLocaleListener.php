<?php

namespace AppBundle\EventListener;

use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Http\Event\InteractiveLoginEvent;

class UserLocaleListener
{
    private $urlGenerator;

    private $session;

    public function __construct(UrlGeneratorInterface $urlGenerator, Session $session)
    {
        $this->urlGenerator = $urlGenerator;
        $this->session = $session;
    }

    public function onInteractiveLogin(InteractiveLoginEvent $event, $eventName, $dispatcher)
    {
        $user = $event->getAuthenticationToken()->getUser();

        if (null !== $user->getLocale()) {
            $this->session->set('_locale', $user->getLocale());

            $dispatcher->addListener(KernelEvents::RESPONSE,  [$this, 'onKernelResponse']);
        }
    }

    public function onKernelResponse(FilterResponseEvent $event)
    {
        $response = new RedirectResponse($this->urlGenerator->generate("homepage", array('_locale' => $this->session->get('_locale'))));
        $event->setResponse($response);
    }
}