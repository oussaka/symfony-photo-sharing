<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Image;
use AppBundle\Entity\Rating;
use AppBundle\Event\ImageUploadEvent;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use AppBundle\Form\ImageType;
use Doctrine\Common\Collections\ArrayCollection;

class ImageController extends Controller
{
    /**
     * @Route("/upload/classic", name="upload_classic")
     */
    public function uploadClassicAction(Request $request)
    {
        $image = new Image();
        $form = $this->createForm(ImageType::class, $image, array(
            'method' => 'POST',
        ));

        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();

            $image->setUser($this->getUser());

            $em->persist($image);
            $em->flush();

            return $this->redirectToRoute('images_user', array('user' => $this->getUser()));
        }

        return $this->render('default/upload_classic.html.twig', array(
            'form' => $form->createView()
        ));
    }

    /**
     * @Route("/explore/", name="explore", defaults={"page" = 1})
     * @Route("/explore/page/{page}", name="explore_paginated", requirements={"page" : "\d+"})
     */
    public function exploreAction($page)
    {
        $entityManager = $this->getDoctrine()->getManager();
        $query = $entityManager->getRepository('AppBundle:Image')->findAll();

        $paginator  = $this->get('knp_paginator');
        $images = $paginator->paginate($query, $page, Image::NUM_ITEMS);
        $images->setUsedRoute('explore_paginated');

        return $this->render('default/explore.html.twig', array(
            'images' => $images
        ));
    }

    /**
     * @Route("/photos/{id}", name="image_detail")
     */
    public function imageDetailAction(Image $image)
    {
        $em = $this->getDoctrine()->getManager();
        $rating = $em->getRepository('AppBundle:Rating')->findOneBy(array(
            'image' => $image->getId(),
            'user' => $this->getUser()
        ));

        return $this->render('default/image_detail.html.twig', array(
            'image' => $image,
            'exif' => exif_read_data($image->getAbsolutePath()),
            'rating' => $rating
        ));
    }

    public function commentFormAction(Image $image)
    {
        $form = $this->createForm('AppBundle\Form\CommentType');

        return $this->render('default/_comment_form.html.twig', array(
            'image' => $image,
            'form' => $form->createView(),
        ));
    }

    /**
     * @Route("/comment/{id}/new", name = "comment_new")
     */
    public function commentNewAction(Request $request, Image $image)
    {
        $form = $this->createForm('AppBundle\Form\CommentType');

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $comment = $form->getData();
            $comment->setImage($image);
            $comment->setUser($this->getUser());

            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($comment);
            $entityManager->flush();

            return $this->redirectToRoute('image_detail', array('id' => $image->getId()));
        }

        return $this->render('default/comment_form_error.html.twig', array(
            'image' => $image,
            'form' => $form->createView(),
        ));
    }

    /**
     * @Route("/photos/{id}/favorite", name = "favorite", options={"expose"=true})
     * @Security("is_granted('IS_AUTHENTICATED_FULLY')")
     */
    public function favoriteAction(Request $request, Image $image)
    {
        if ($request->isXmlHttpRequest()) {

            $em = $this->getDoctrine()->getManager();
            $entity = $em->getRepository('AppBundle:Rating')->findOneBy(array(
                'image' => $image->getId(),
                'user' => $this->getUser()
            ));

            if (null === $entity) {
                $rating = new Rating();
                $rating->setUser($this->getUser());
                $rating->setImage($image);
                $em->persist($rating);
                $em->flush();

                return new JsonResponse(array('stat' => 'add'));
            } else {
                $em->remove($entity);
                $em->flush();

                return new JsonResponse(array('stat' => 'remove'));
            }
        } else {
            return $this->redirectToRoute('explore');
        }
    }

    /**
     * @Route("/photos/tags/{tag}", name="images_tags", defaults={"page" = 1})
     * @Route("/photos/tags/{tag}/page/{page}", name="image_tags_paginated", requirements={"page" : "\d+"})
     */
    public function imagesTagsAction($tag, $page)
    {
        $em = $this->getDoctrine()->getManager();
        $query = $em->getRepository('AppBundle:Image')->findImagesByTags($tag);

        $paginator  = $this->get('knp_paginator');
        $images = $paginator->paginate($query, $page, Image::NUM_ITEMS);
        $images->setUsedRoute('image_tags_paginated');

        return $this->render('default/tags_image.html.twig', array(
            'images' => $images
        ));
    }

    /**
     * @Route("/photos/{id}/edit", name="image_edit")
     */
    public function imageEditAction(Request $request, Image $image)
    {
        $this->denyAccessUnlessGranted('edit', $image);

        $entityManager = $this->getDoctrine()->getManager();

        $originalTags = new ArrayCollection();

        foreach ($image->getTags() as $tag) {
            $originalTags->add($tag);
        }

        $editForm = $this->createForm('AppBundle\Form\ImageType', $image);
        $editForm->remove('file');

        $editForm->handleRequest($request);

        if ($editForm->isSubmitted() && $editForm->isValid()) {

            foreach ($originalTags as $tag) {
                if (false === $image->getTags()->contains($tag)) {
                    $entityManager->persist($tag);
                    $entityManager->remove($tag);
                }
            }

            $entityManager->persist($image);
            $entityManager->flush();

            $translator = $this->get('translator');
            $this->addFlash('success', $translator->trans('flash.image.edited') );

            return $this->redirectToRoute('image_detail', array('id' => $image->getId()));
        }

        return $this->render('default/image_edit.html.twig', array(
            'image' => $image,
            'form' => $editForm->createView()
        ));
    }

    /**
     * @Route("/photos/user/{user}", name="images_user", defaults={"page" = 1})
     * @Route("/photos/user/{user}/page/{page}", name="images_user_paginated", requirements={"page" : "\d+"})
     */
    public function imagesUserAction($user, $page)
    {
        $em = $this->getDoctrine()->getManager();

        $entities = $em->getRepository('AppBundle:User')->findOneBy(array(
            'username' => $user
        ));

        $query = $em->getRepository('AppBundle:Image')->findBy(array(
            'user' => $entities->getId()),
            array('createdAt' => 'DESC')
        );

        $paginator  = $this->get('knp_paginator');
        $images = $paginator->paginate($query, $page, Image::NUM_ITEMS);
        $images->setUsedRoute('images_user_paginated');

        return $this->render('default/users_image.html.twig', array(
            'images' => $images
        ));
    }

    /**
     * @Route("/photos/{id}/remove", name="image_remove")
     */
    public function imageRemoveAction(Request $request, Image $image)
    {
        $this->denyAccessUnlessGranted('edit', $image);

        $em = $this->getDoctrine()->getManager();
        $em->remove($image);
        $em->flush();

        return $this->redirectToRoute('explore');
    }

    /**
     * @Route("/upload", name="upload")
     */
    public function uploadAction(Request $request)
    {
        return $this->render('default/upload.html.twig');
    }

    /**
     * @Route("/remove/{qquuid}", name="remove", defaults={"qquuid" = null})
     */
    public function removeNewAction(Request $request, $qquuid)
    {
        return new JsonResponse();
    }

}