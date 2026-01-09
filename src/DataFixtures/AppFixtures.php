<?php

namespace App\DataFixtures;

use App\Entity\Category;
use App\Entity\Challenge;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

class AppFixtures extends Fixture
{
    public function __construct(private readonly ParameterBagInterface $params)
    {
    }

    public function load(ObjectManager $manager): void
    {
        $projectDir = $this->params->get('kernel.project_dir');
        $challenges = json_decode(file_get_contents($projectDir . '/data/challenges.json'), true);

        foreach ($challenges as $data) {
            $category = $manager->getRepository(Category::class)->findOneBy(['title' => $data['categoryName']]);

            if (!$category) {
                $category = new Category();
                $category->setTitle($data['categoryName']);
                $category->setDescription("");
                $manager->persist($category);
                $manager->flush();
            }

            $challenge = new Challenge();
            $challenge->setCategory($category);
            $challenge->setTitle($data['title']);
            $challenge->setSlug($this->toKebabCase($data['title']));
            $challenge->setInstruction($data['description']);
            $challenge->setDescription($data['description']);
            $manager->persist($challenge);
        }

        $manager->flush();
    }

    private function toKebabCase(string $value): string
    {
        $normalized = iconv('UTF-8', 'ASCII//TRANSIT//IGNORE', $value);
        if ($normalized === false) $normalized = $value;
        $normalized = strtolower($normalized);
        $normalized = preg_replace('/[^a-z0-9]+/', '-', $normalized);
        return trim($normalized ?? '', '-');
    }

}
