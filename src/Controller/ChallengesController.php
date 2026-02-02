<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\Challenge;
use App\Entity\Result;
use App\Entity\User;
use App\Form\ResultType;
use App\Form\SearchType;
use App\Repository\ChallengeRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormError;
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
    public function index(Request $request, ChallengeRepository $challengeRepository): Response
    {

        $formSearch = $this->createForm(SearchType::class);

        $searchInput = null;
        $formSearch->handleRequest($request);
        if ($formSearch->isSubmitted() && $formSearch->isValid()) {
            $searchInput = $formSearch->get('searchInput')->getData();
        }
        $groupedChallenges = $challengeRepository->findWithSearch($searchInput);

        return $this->render('challenges/index.html.twig', [
            'formSearch' => $formSearch,
            'groupedChallenges' => $groupedChallenges,
        ]);
    }

    #[Route('/{slug}', name: 'show')]
    public function show(Request $request, string $slug): Response
    {
        $challenge = $this->em->getRepository(Challenge::class)->findOneBy(['slug' => $slug]);
        $category = $challenge->getCategory();
        $user = $this->getUser();

        $result = $this->em->getRepository(Result::class)->findOneBy(['challenge' => $challenge, 'userResult' => $user]);
        if (!$result) {
            $result = new Result()->setUserResult($user)->setChallenge($challenge);
        }

        $resultForm = $this->createForm(ResultType::class, $result);
        if ($result->getScore()) {
            $resultForm->get('score')->setData($result->getScore());
        }
        if ($result->getDate()) {
            $resultForm->get('date')->setData($result->getDate());
        } else {
            $resultForm->get('date')->setData(new \DateTime());
        }
        $resultForm->handleRequest($request);

        if ($resultForm->isSubmitted() && $resultForm->isValid()) {

            $this->em->persist($result);
            $this->em->flush();
            return $this->redirectToRoute('profile_index');

        }

        return $this->render('challenges/show.html.twig', [
            'challenge' => $challenge,
            'category' => $category,
            'resultForm' => $resultForm,
        ]);
    }

}
