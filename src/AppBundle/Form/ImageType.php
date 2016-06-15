<?php

namespace AppBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;

class ImageType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name', null, array(
                'label' => 'label.name'
            ))
            ->add('file', null, array(
                'label' => 'label.file'
            ))
            ->add('tags', CollectionType::class, array(
                'label' => 'label.tags',
                'entry_type' => TagType::class,
                'allow_add'    => true,
                'by_reference' => false,
                'allow_delete' => true,
            ));
    }
    
    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'AppBundle\Entity\Image',
            'validation_groups' => function(FormInterface $form){
                $data = $form->getData();

                if (null !== $data->getId())
                    return array('Image'); //edit
                else
                    return array('Image', 'newUpload'); //create
            }
        ));
    }
}
