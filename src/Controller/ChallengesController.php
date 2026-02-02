<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\Challenge;
use App\Entity\Result;
use App\Entity\User;
use App\Form\ResultType;
use App\Repository\ChallengeRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
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

        $formSearch = $this->createFormBuilder()
            ->add("searchInput", TextType::class,  ['label' => false, 'required' => false, 'attr' => ['placeholder' => 'Nom du challenge']])
            ->add("search", SubmitType::class)
            ->getForm();

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
