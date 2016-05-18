<?php

namespace AppBundle\EventListener;

use AppBundle\Entity\Image;
use Doctrine\Common\Persistence\ObjectManager;
use Oneup\UploaderBundle\Event\PostPersistEvent;
use Oneup\UploaderBundle\Event\ValidationEvent;
use Oneup\UploaderBundle\Uploader\Exception\ValidationException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class UploadListener
{
    private $om;

    private $validator;

    private $tokenStorage;


    public function __construct(ObjectManager $om, ValidatorInterface $validator, TokenStorage $tokenStorage)
    {
        $this->om = $om;
        $this->validator = $validator;
        $this->tokenStorage = $tokenStorage;
    }

    public function onUpload(PostPersistEvent $event)
    {
        $name = $this->getFileNameNoExtension($event->getRequest());
        $path = $event->getFile()->getFileName();
        $user = $this->tokenStorage->getToken()->getUser();

        $image = new Image();
        $image->setName($name);
        $image->setPath($path);
        $image->setUser($user);

        $this->om->persist($image);
        $this->om->flush();
    }

    public function onValidate(ValidationEvent $event)
    {
        $name = $this->getFileNameNoExtension($event->getRequest());
        $file = $event->getFile();

        $image = new Image();
        $image->setName($name);
        $image->setFile($file);

        $errors = $this->validator->validate($image, null, array('Image', 'newUpload'));

        if (count($errors) > 0) {

            foreach($errors as $error) {
                $errorList[] = $error->getMessage();
            }

            $errorsString = implode ("\n ", $errorList);

            throw new ValidationException($errorsString);
        }

        return new JsonResponse(array('success' => true));
    }

    private function getFileNameNoExtension($request)
    {
        $name = $request->request->get('qqfilename');
        $info = pathinfo($name);
        $name = (isset($info['extension']) ? basename($name,'.'.$info['extension']) : $name);

        return $name;
    }

}