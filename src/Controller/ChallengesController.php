<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\Challenge;
use App\Entity\Result;
use App\Entity\User;
use App\Form\ResultType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/challenge', name: 'challenges_')]
class ChallengesController extends AbstractController
{

    public function __construct(
        private readonly EntityManagerInterface $em
    )
    {
    }

    #[Route('/', name: 'index')]
    public function index(): Response
    {
        $groupedChallenges = $this->em->getRepository(Challenge::class)->findGroupedByCategory();

        return $this->render('challenges/index.html.twig', [
            'groupedChallenges' => $groupedChallenges,
        ]);
    }

    #[Route('/{slug}', name: 'show')]
    public function show(Request $request, string $slug): Response
    {
        $challenge = $this->em->getRepository(Challenge::class)->findOneBy(['slug' => $slug]);
        $category = $challenge->getCategory();
        $resultForm = null;

        $user = $this->getUser();
        if ($user instanceof User) {
            $result = new Result();
            $result->setUserResult($user);
            $result->setChallenge($challenge);

            $resultForm = $this->createForm(ResultType::class, $result, [
                'include_challenge_field' => false,
            ]);

            $resultForm->handleRequest($request);

            if ($resultForm->isSubmitted() && $resultForm->isValid()) {
                $this->em->persist($result);
                $this->em->flush();

                return $this->redirectToRoute('profile_index');
            }
        }

        return $this->render('challenges/show.html.twig', [
            'challenge' => $challenge,
            'category' => $category,
            'resultForm' => $resultForm,
        ]);
    }

}
