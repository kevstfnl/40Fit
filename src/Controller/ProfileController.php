<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\User;
use App\Form\ProfileType;
use App\Repository\ChallengeRepository;
use App\Repository\ResultRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
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
    public function index(Request $request, ResultRepository $resultRepository, ChallengeRepository $challengeRepository): Response
    {
        $user = $this->getUser();

        if (!$user instanceof User) {
            throw $this->createAccessDeniedException('User not authenticated.');
        }
        $results = $resultRepository->findByUser($user);

        $formSearch = $this->createFormBuilder()
            ->add("searchInput", TextType::class,  ['label' => false, 'required' => false, 'attr' => ['placeholder' => 'Nom du challenge']])
            ->add("search", SubmitType::class)
            ->getForm();

        $searchInput = null;
        $formSearch->handleRequest($request);
        if ($formSearch->isSubmitted() && $formSearch->isValid()) {
            $searchInput = $formSearch->get('searchInput')->getData();
        }

        $groupedPendingChallenges = $challengeRepository->findWithoutResultForUserGrouped($user, $searchInput);
        $pendingChallengesCount = 0;

        foreach ($groupedPendingChallenges as $group) {
            $pendingChallengesCount += count($group['items']);
        }


        return $this->render('profile/index.html.twig', [
            'results' => $results,
            'formSearch' => $formSearch,
            'groupedPendingChallenges' => $groupedPendingChallenges,
            'pendingChallengesCount' => $pendingChallengesCount,
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
