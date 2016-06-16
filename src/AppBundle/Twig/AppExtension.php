<?php

namespace AppBundle\Twig;

use Symfony\Component\Intl\Intl;

class AppExtension extends \Twig_Extension
{
    private $locales;

    public function __construct($locales)
    {
        $this->locales = $locales;
    }

    public function getFunctions()
    {
        return array(
            new \Twig_SimpleFunction('locales', array($this, 'getLocales')),
        );
    }

    public function getLocales()
    {
        $localeCodes = explode('|', $this->locales);
        $locales = array();
        foreach ($localeCodes as $localeCode) {
            $locales[] = array('code' => $localeCode, 'name' => Intl::getLocaleBundle()->getLocaleName($localeCode, $localeCode));
        }
        return $locales;
    }

    public function getName()
    {
        return 'app.extension';
    }
}
