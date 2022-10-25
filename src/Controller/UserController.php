<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;

use WhiteOctober\BreadcrumbsBundle\Model\Breadcrumbs;

use App\Entity\LogEvent;
use App\Entity\User;
use App\Form\Model\ChangePassword;
use App\Form\ChangePasswordFormType;
use App\Form\SettingsFormType;
use App\Repository\RssFeedRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Security\Http\Attribute\CurrentUser;

class UserController extends AbstractController
{

    public function __construct(Breadcrumbs $breadcrumbs, private ManagerRegistry $doctrine)
    {
        $breadcrumbs->addItem('Utilisateur', '/user');
    }

    private function getDoctrine(): ManagerRegistry
    {
        return $this->doctrine;
    }

    #[Route("/user", name: "user")]
    public function index(RssFeedRepository $rssFeedRepository)
    {
        $feedsCount = $rssFeedRepository->getFeedCountForUser($this->getUser());

        return $this->render('user/index.html.twig', [
            'feeds_count' => $feedsCount,
        ]);
    }

    #[Route('user/settings', name: 'user_settings')]
    public function settings(
        Breadcrumbs $breadcrumbs,
        Request $request,
        #[CurrentUser] ?User $user,
        UserPasswordHasherInterface $passwordEncoder
    ) {
        $breadcrumbs->addItem('Mes paramètres');

        $passwordForm = $this->createForm(ChangePasswordFormType::class, new ChangePassword());

        $passwordForm->handleRequest($request);
        if ($passwordForm->isSubmitted() && $passwordForm->isValid()) {
            $changePassword = $passwordForm->getData();

            $encodedPassword = $passwordEncoder->hashPassword(
                $user,
                $changePassword->newPassword
            );

            $user->setPassword($encodedPassword);

            $em = $this->getDoctrine()->getManager();
            $em->persist($user);
            $em->flush();

            $this->addFlash('success', 'Votre mot de passe est modifié avec succès.');

            return $this->redirectToRoute('user_settings');
        }

        $settingsForm = $this->createForm(SettingsFormType::class, $this->getUser());
        $settingsForm->handleRequest($request);
        if ($settingsForm->isSubmitted() && $settingsForm->isValid()) {
            $user = $settingsForm->getData();

            $em = $this->getDoctrine()->getManager();
            $em->persist($user);
            $em->flush();

            $this->addFlash('success', 'Vos paramètres ont été modifiés avec succès.');

            return $this->redirectToRoute('user_settings');
        }

        return $this->render('user/settings.html.twig', [
            'password_form' => $passwordForm->createView(),
            'settings_form' => $settingsForm->createView(),
        ]);
    }

    #[Route("/user/status", name: "user_status")]
    public function status(Breadcrumbs $breadcrumbs)
    {
        $breadcrumbs->addItem('Status de mes feeds');

        $repository = $this->getDoctrine()->getRepository(LogEvent::class);
        $logs = $repository->findAll();

        return $this->render('user/status.html.twig', [
            'logs' => $logs
        ]);
    }
}
