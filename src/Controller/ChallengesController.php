<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\Challenge;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
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
        $challenges = $this->em->getRepository(Challenge::class)->findAll();

        return $this->render('challenges/index.html.twig', [
            "challenges" => $challenges,
        ]);
    }

    #[Route('/{slug}', name: 'show')]
    public function show(string $slug): Response
    {
        $challenge = $this->em->getRepository(Challenge::class)->findOneBy(['slug' => $slug]);
        $category = $challenge->getCategory();

        return $this->render('challenges/show.html.twig', [
            'challenge' => $challenge,
            'category' => $category,
        ]);
    }
}

