<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Image;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
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
     * @Route("/photos/{id}", name="show_image")
     */
    public function showImageAction(Image $image){
        return $this->render('default/show_image.html.twig', array(
            'image' => $image
        ));
    }
}
