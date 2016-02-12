<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Image;
use AppBundle\Entity\Rating;
use AppBundle\Entity\Tag;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use AppBundle\Form\ImageType;

class DefaultController extends Controller
{
    /**
     * @Route("/", name="homepage")
     */
    public function indexAction(Request $request)
    {
        return $this->render('default/index.html.twig');
    }

    /**
     * @Route("/upload", name="upload")
     */
    public function uploadAction(Request $request)
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

            return $this->redirectToRoute('explore');
        }

        return $this->render('default/upload.html.twig', array(
            'form' => $form->createView()
        ));
    }

    /**
     * @Route("/explore", name="explore")
     */
    public function exploreAction(Request $request)
    {
        $entityManager = $this->getDoctrine()->getManager();
        $images = $entityManager->getRepository('AppBundle:Image')->findAll();

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
        if ($request->isXmlHttpRequest()){

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
            }else{
                $em->remove($entity);
                $em->flush();

                return new JsonResponse(array('stat' => 'remove'));
            }
        }else{
            return $this->redirectToRoute('explore');
        }
    }

    /**
     * @Route("/photos/tags/{tag}", name="photos_tags")
     */
    public function photosTagsAction($tag)
    {
        $em = $this->getDoctrine()->getManager();
        $entities = $em->getRepository('AppBundle:Tag')->findBy(array(
            'name' => $tag
        ));

        return $this->render('default/tags_image.html.twig', array(
            'tags' => $entities
        ));
    }

}
