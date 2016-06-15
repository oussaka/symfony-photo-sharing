<?php

namespace AppBundle\EventListener;

use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class RedirectToPreferredLocaleListener
{
    private $urlGenerator;

    private $locales = array();

    private $defaultLocale = '';

    public function __construct(UrlGeneratorInterface $urlGenerator, $locales, $defaultLocale = null)
    {
        $this->urlGenerator = $urlGenerator;

        $this->locales = explode('|', trim($locales));
        if (empty($this->locales)) {
            throw new \UnexpectedValueException('The list of supported locales must not be empty.');
        }

        $this->defaultLocale = $defaultLocale ?: $this->locales[0];

        if (!in_array($this->defaultLocale, $this->locales)) {
            throw new \UnexpectedValueException(sprintf('The default locale ("%s") must be one of "%s".', $this->defaultLocale, $locales));
        }

        array_unshift($this->locales, $this->defaultLocale);
        $this->locales = array_unique($this->locales);
    }

    public function onKernelRequest(GetResponseEvent $event)
    {
        $request = $event->getRequest();

        if ('/' !== $request->getPathInfo()) {
            return;
        }

        $preferredLanguage = $request->getPreferredLanguage($this->locales);

        $response = new RedirectResponse($this->urlGenerator->generate('homepage', array('_locale' => $preferredLanguage)));
        $event->setResponse($response);
    }
}
