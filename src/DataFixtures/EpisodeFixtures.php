<?php


namespace App\DataFixtures;


use App\Entity\Episode;
use App\Service\Slugify;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Faker;

class EpisodeFixtures extends Fixture implements DependentFixtureInterface
{
    private $slugify;

    public function __construct(Slugify $slugify)
    {
        $this->slugify = $slugify;
    }

    public function load(ObjectManager $manager)
    {
        $faker = Faker\Factory::create('en_US');
        for($i=1; $i<15;$i++){
            $episode = new Episode();
            $episode->setNumber($i);
            $episode->setTitle($faker->word);
            $slugTitle = $this->slugify->generate($episode->getTitle());
            $episode->setSlug($slugTitle);
            $episode->setSynopsis($faker->text);
            $episode->setSeason($this->getReference('season_' . rand(1,6)));
            $manager->persist($episode);
            $this->addReference('episode_' . $i, $episode);
        }
        $manager->flush();
    }

    public function getDependencies()
    {
        return [SeasonFixtures::class];
    }


}