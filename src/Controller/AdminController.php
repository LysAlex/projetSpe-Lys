<?php

namespace App\Controller;

use App\Entity\Notifications;
use App\Entity\User;
use App\Entity\Writing;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class AdminController extends AbstractController
{

    /**
     * @Route("/admin", name="admin")
     */
    public function index(): Response
    {
        // Recupération des repository
        $this->writingRepo = $this->getDoctrine()->getRepository(Writing::class);
        $this->userRepo = $this->getDoctrine()->getRepository(User::class);
        $this->notificationsRepo = $this->getDoctrine()->getRepository(Notifications::class);

        // Afficher les notifications
        $notifications = $this->notificationsRepo->findBy(['userId' => $this->getUser()->getId(), 'isRead' => 0]);

        $writing = $this->writingRepo->findAll();
        $user = $this->userRepo->findAll();
        foreach ($writing as $keyWriting => $valueWriting){
            foreach ($user as $keyUser => $valueUser){
                if ($valueWriting->getUser() == $valueUser->getId()){
                    $writing[$keyWriting]->username = $valueUser->getUsername();
                }
            }
        }

        return $this->render('admin/index.html.twig', [
            'notifications' => $notifications??null,
            'writings' => $writing??null,
            'users' => $user??null,
        ]);
    }

    /**
     * @Route("/admin/user/{userId}", name="admin.delete_user", requirements={"userId"="[0-9]+"})
     * @Route("/admin/writing/{storyId}", name="admin.delete_writing", requirements={"storyId"="[0-9]+"})
     */
    public function delete(Request $request, EntityManagerInterface $em, $userId = null, $storyId = null): Response
    {
        // Recupération des repository
        $this->userRepo = $this->getDoctrine()->getRepository(User::class);
        $this->writingRepo = $this->getDoctrine()->getRepository(Writing::class);

        // Récupérer la route actuelle
        $route = $request->attributes->get('_route');

        if ($route == 'admin.delete_user'){
            $user = $this->userRepo->find($userId);
            $writing = $this->writingRepo->findBy(['user' => $user->getId()]);
            foreach ($writing as $value){
                $em->remove($value);
                $em->flush();
            }
            $em->remove($user);
            $em->flush();
            $this->addFlash('success', 'L\'utilisateur a été supprimé.');
        }

        if ($route == 'admin.delete_writing'){
            $writing = $this->writingRepo->find($storyId);
            $em->remove($writing);
            $em->flush();
            $this->addFlash('success', 'L\'histoire a été supprimée.');
        }

        return $this->redirectToRoute('admin');
    }
}
