<?php

namespace AppBundle\EventListener;

use AppBundle\Entity\Image;
use Doctrine\Common\Persistence\ObjectManager;
use Oneup\UploaderBundle\Event\PostPersistEvent;
use Oneup\UploaderBundle\Event\ValidationEvent;
use Oneup\UploaderBundle\Uploader\Exception\ValidationException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class UploadListener
{
    private $om;

    private $validator;

    public function __construct(ObjectManager $om, ValidatorInterface $validator)
    {
        $this->om = $om;
        $this->validator = $validator;
    }

    public function onUpload(PostPersistEvent $event)
    {
        $file = $event->getFile();
        $response = $event->getResponse();

        $response['files'] = array(
            array(
                'name' => $file->getFilename(),
                'path' => $file->getPath(),
            ),
        );
    }

    public function onValidate(ValidationEvent $event)
    {
        $file    = $event->getFile();

        $image = new Image();
        $image->setFile($file);

        $errors = $this->validator->validate($image, null, array(
            'validation_groups' => 'newUpload'
        ));

        if (count($errors) > 0) {
            $errorsString = (string) $errors;
            throw new ValidationException($errorsString);
        }

        return new JsonResponse(array('success' => true));
    }

}