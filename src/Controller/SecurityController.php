<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\RegisterType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

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
    public function register(UserPasswordEncoderInterface $passwordEncoder, Request $request, AuthenticationUtils $authenticationUtils)
    {
        // Retrieve the entity manager
        $entityManager = $this->getDoctrine()->getManager();
        
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
            $user->setRole('user');
            $user->setIsActive(1);
            $entityManager->persist($user);
            $entityManager->flush();
            $this->addFlash('success', 'Inscription réussie.');

            // get the login error if there is one
            $error = $authenticationUtils->getLastAuthenticationError();

            return $this->redirectToRoute('login', ['error' => $error]);

        }

        return $this->render('login/register.html.twig', [
            'form' => $form->createView()??null,
        ]);

    }
}
