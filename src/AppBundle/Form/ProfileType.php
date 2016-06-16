<?php

namespace AppBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TimezoneType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Intl\Intl;

class ProfileType extends AbstractType
{
    private $locales;

    public function __construct($locales)
    {
        $this->locales = $locales;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name', null, array(
                'label' => 'label.name'
            ))
            ->add('profilePicture', null, array(
                'label' => 'label.profile_picture'
            ))
            ->add('locale', ChoiceType::class, array(
                'placeholder' => 'Select language..',
                'label' => 'label.locale',
                'choices' => $this->getLocales()
            ))
            ->add('timezone', TimezoneType::class, array(
                'label' => 'label.timezone'
            ));
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'AppBundle\Entity\User'
        ));
    }

    public function getLocales()
    {
        $localeCodes = explode('|', $this->locales);
        $locales = array();
        foreach ($localeCodes as $localeCode) {
            $locales[ucfirst(Intl::getLocaleBundle()->getLocaleName($localeCode, $localeCode))] = $localeCode;
        }

        return $locales;
    }
}
