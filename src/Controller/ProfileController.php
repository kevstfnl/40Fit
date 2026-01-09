<?php

declare(strict_types=1);

namespace App\Controller;

use App\Repository\ChallengeRepository;
use App\Repository\ResultRepository;
use App\Form\ProfileType;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/profile', name: 'profile_')]
#[isGranted('IS_AUTHENTICATED_FULLY')]
class ProfileController extends AbstractController
{

    #[Route('/', name: 'index')]
    public function index(ResultRepository $resultRepository, ChallengeRepository $challengeRepository): Response
    {
        $user = $this->getUser();

        if (!$user instanceof User) {
            throw $this->createAccessDeniedException('User not authenticated.');
        }

        $results = $resultRepository->findByUser($user);
        $pendingChallenges = $challengeRepository->findWithoutResultForUser($user);

        return $this->render('profile/index.html.twig', [
            'results' => $results,
            'pendingChallenges' => $pendingChallenges,
        ]);
    }

    #[Route('/edit', name: 'edit')]
    public function edit(Request $request, EntityManagerInterface $entityManager, UserPasswordHasherInterface $passwordHasher): Response
    {
        $user = $this->getUser();

        if (!$user instanceof User) {
            throw $this->createAccessDeniedException('User not authenticated.');
        }

        $form = $this->createForm(ProfileType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $newPassword = $form->get('newPassword')->getData();
            if (!empty($newPassword)) {
                $user->setPassword($passwordHasher->hashPassword($user, $newPassword));
            }

            $entityManager->flush();

            return $this->redirectToRoute('profile_index');
        }

        return $this->render('profile/edit.html.twig', [
            'profileForm' => $form,
        ]);
    }
}
