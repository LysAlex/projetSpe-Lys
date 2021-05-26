<?php

namespace App\Controller;

use App\Entity\Notifications;
use App\Entity\User;
use App\Form\RegisterType;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\String\Slugger\SluggerInterface;

class SecurityController extends AbstractController
{
    /**
     * @Route("/", name="login")
     */
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        // if ($this->getUser()) {
        //     return $this->redirectToRoute('target_path');
        // }

        // get the login error if there is one
        $error = $authenticationUtils->getLastAuthenticationError();
        // last username entered by the user
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('login/index.html.twig', ['last_username' => $lastUsername, 'error' => $error]);
    }

    /**
     * @Route("/logout", name="logout")
     */
    public function logout()
    {
        throw new \LogicException('This method can be blank - it will be intercepted by the logout key on your firewall.');
    }

    /**
     * @Route("/register", name="register")
     */
    public function register(UserPasswordEncoderInterface $passwordEncoder, SluggerInterface $slugger, Request $request, AuthenticationUtils $authenticationUtils)
    {
        // Retrieve the entity manager
        $entityManager = $this->getDoctrine()->getManager();
        $this->notificationRepo = $this->getDoctrine()->getRepository(Notifications::class);
        $this->userRepo = $this->getDoctrine()->getRepository(User::class);

        // Create a new user with random data
        $form =$this->createForm(RegisterType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()){
            $user = new User();
            $user->setEmail($form->get('email')->getData());
            $user->setPassword($passwordEncoder->encodePassword(
                $user,
            $form->get('password')->getData()
            ));
            $user->setUsername($form->get('username')->getData());
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
            $user->setRole('user');
            $user->setIsActive(1);
            $entityManager->persist($user);
            $entityManager->flush();
            $this->addFlash('success', 'Inscription réussie.');

            $admin = $this->userRepo->findOneBy(['role' => 'admin']);

            $notification = new Notifications();
            $notification->setUserId($admin->getId());
            $notification->setMessage('Le compte '.$user->getUsername().' a été crée !');
            $notification->setUpdateDate(new \Datetime());
            $notification->setIsRead(0);
            $entityManager->persist($notification);
            $entityManager->flush();

            // get the login error if there is one
            $error = $authenticationUtils->getLastAuthenticationError();

            return $this->redirectToRoute('login', ['error' => $error]);

        }

        return $this->render('login/register.html.twig', [
            'form' => $form->createView()??null,
        ]);

    }
}
