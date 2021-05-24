<?php

namespace App\Controller;

use App\Entity\User;
use App\Entity\Writing;
use App\Form\RegisterType;
use App\Form\WritingType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\String\Slugger\SluggerInterface;

class WritingController extends AbstractController
{

    /**
     * @Route("/writing", name="writing")
     */
    public function index(EntityManagerInterface $em, Request $request): Response
    {

        // Initialisation du formulaire
        $form =$this->createForm(WritingType::class);
        $form->handleRequest($request);

        // Soumission et validation du formulaire
        if ($form->isSubmitted() && $form->isValid()){

            // Création d'une histoire
            $writing = new Writing();
            $writing->setTitle($form->get('title')->getData());
            $writing->setContent($form->get('content')->getData());
            $writing->setUpdateDate(new \Datetime());
            $writing->setUser($this->getUser()->getId());
            $em->persist($writing);
            $em->flush();

            // Message de validation + redirection
            $this->addFlash('success','Enregistrement réussi.');
            return $this->redirectToRoute('writing');
        }

        return $this->render('writing/index.html.twig', [
            'form' => $form->createView()??null,
        ]);
    }

     /**
     * @Route("/writing/list", name="writing.list")
     * @Route("/writing/list/{userId}", name="writing.list_user", requirements={"userId"="[0-9]+"})
     * @Route("/writing/list/{userId}/{storyId}", name="writing.delete", requirements={"userId"="[0-9]+","storyId"="[0-9]+"})
     */
    public function list($userId = null, $storyId = null, EntityManagerInterface $em, Request $request): Response
    {

        // Récupération du repo Writing
        $this->writingRepo = $this->getDoctrine()->getRepository(Writing::class);

        // Condition qui permet de récupérer toutes les histoires ou seulement les histoires de l'utilisateur
        if ($userId == $this->getUser()->getId()){
            $writing = $this->writingRepo->findBy(['user' => $this->getUser()->getId()]);

            // Suppression d'une histoire
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

        // Initialisation du formulaire
        $form = $this->createForm(WritingType::class);
        $form->handleRequest($request);


        // Soumission et validation du formulaire
        if ($form->isSubmitted() && $form->isValid()){

            // Modification de l'histoire
            $story = $this->writingRepo->find($form->get('id')->getData());
            $story->setTitle($form->get('title')->getData());
            $story->setContent($form->get('content')->getData());
            $story->setUpdateDate(new \DateTime());
            $em->persist($story);
            $em->flush($story);

            // Message de validation + redirection
            $this->addFlash('success', 'Mise à jour réussie');
            return $this->redirectToRoute('writing.list');
        }

        return $this->render('writing/list.html.twig', [
            'writing' => $writing??null,
            'form' => $form??null,
            'userId' => $userId??null,
        ]);
    }

    /**
     * @Route("/writing/profile/{userId}", name="writing.profile", requirements={"userId"="[0-9]+"})
     */
    public function profile($userId = null, EntityManagerInterface $entityManager, UserPasswordEncoderInterface $passwordEncoder, Request $request, SluggerInterface $slugger): Response
    {
        // Recupération des repository
        $this->userRepo = $this->getDoctrine()->getRepository(User::class);

        // Condition qui permet de récupérer toutes les histoires ou seulement les histoires de l'utilisateur
        if ($userId == $this->getUser()->getId()) {
            $user = $this->userRepo->find($userId);

            // Initialisation du formulaire
            $form = $this->createForm(RegisterType::class, $user);
            $form->handleRequest($request);

            if ($form->isSubmitted() && $form->isValid()){
                /** @var UploadedFile $imageFile */
                $imageFile = $form->get('image')->getData();

                // Vérifie si une image est présente
                if ($imageFile) {
                    $originalFilename = pathinfo($imageFile->getClientOriginalName(), PATHINFO_FILENAME);

                    // this is needed to safely include the file name as part of the URL
                    $safeFilename = $slugger->slug($originalFilename);
                    $newFilename = $safeFilename.'-'.uniqid().'.'.$imageFile->guessExtension();


                    // Move the file to the directory where images are stored
                    try {
                        $imageFile->move(
                            $this->getParameter('profile_image'),
                            $newFilename
                        );
                    } catch (FileException $e) {
                        // ... handle exception if something happens during file upload
                        $this->addFlash('error', 'Problème durant le chargement du fichier.');
                        return $this->redirectToRoute('writing.profile', ['userId' => $userId]);
                    }

                    // updates the 'imageFilename' property to store the Image file name
                    // instead of its contents
                    $user->setImage($newFilename);
                }
                $user->setEmail($form->get('email')->getData());
                $user->setPassword($passwordEncoder->encodePassword(
                    $user,
                    $form->get('password')->getData()
                ));
                $user->setUsername($form->get('username')->getData());
                $entityManager->persist($user);
                $entityManager->flush();
                // ... persist the $user variable or any other work
                $this->addFlash('success', 'Votre profil a bien été modifié.');
                return $this->redirectToRoute('writing.profile', ['userId' => $userId]);
            }
        }

        return $this->render('writing/profile.html.twig', [
            'user' => $user??null,
            'form' => $form->createView()??null,
        ]);
    }
}
