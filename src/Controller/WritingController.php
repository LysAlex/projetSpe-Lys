<?php

namespace App\Controller;

use App\Entity\Writing;
use App\Form\WritingType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class WritingController extends AbstractController
{

    /**
     * @Route("/writing", name="writing")
     */
    public function index(EntityManagerInterface $em, Request $request): Response
    {

        $form =$this->createForm(WritingType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()){
            $writing = new Writing();
            $writing->setTitle($form->get('title')->getData());
            $writing->setContent($form->get('content')->getData());
            $writing->setUpdateDate(new \Datetime());
            $writing->setUser($this->getUser()->getId());
            $em->persist($writing);
            $em->flush();
            $this->addFlash('success','Enregistrement réussi.');
            return $this->redirectToRoute('writing');
        }

        return $this->render('writing/index.html.twig', [
            'form' => $form->createView()??null,
        ]);
    }

     /**
     * @Route("/writing/list", name="writing.list")
     * @Route("/writing/list/{userId}", name="writing.list_user", requirements={"userId"="[0-9+]"})
     * @Route("/writing/list/{userId}/{storyId}", name="writing.delete", requirements={"userId"="[0-9+]","storyId"="[0-9+]"})
     *
     */
    public function list($userId = null, $storyId = null, EntityManagerInterface $em, Request $request): Response
    {
        $this->writingRepo = $this->getDoctrine()->getRepository(Writing::class);
        if ($userId == $this->getUser()->getId()){
            $writing = $this->writingRepo->findBy(['user' => $this->getUser()->getId()]);
            if ($storyId){
                $writing = $this->writingRepo->find($storyId);
                $em->remove($writing);
                $em->flush();
                $this->addFlash('success', 'Votre histoire a bien été supprimée.');
                return $this->redirectToRoute('writing.list');
            }
        }
        else {
            $writing = $this->writingRepo->findAll();
        }

        $form = $this->createForm(WritingType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()){
            $story = $this->writingRepo->find($form->get('id')->getData());
            $story->setTitle($form->get('title')->getData());
            $story->setContent($form->get('content')->getData());
            $story->setUpdateDate(new \DateTime());
            $em->persist($story);
            $em->flush($story);
            $this->addFlash('success', 'Mise à jour réussie');
            return $this->redirectToRoute('writing.list');
        }

        return $this->render('writing/list.html.twig', [
            'writing' => $writing??null,
            'form' => $form??null,
            'userId' => $userId??null,
        ]);
    }
}
