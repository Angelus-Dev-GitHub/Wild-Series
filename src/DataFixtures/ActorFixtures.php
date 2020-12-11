<?php


namespace App\DataFixtures;


use App\Entity\Actor;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class ActorFixtures extends Fixture implements DependentFixtureInterface
{
    const ACTORS = [
        'Andrew Lincoln',
        'Norman Reedus',
        'Melissa McBride',
        'Michiel Huisman',
        'Carla Gugino',
        'Josh Hartnett',
        'Timothy Dalton'
    ];

    public function load(ObjectManager $manager)
    {
        $i=1;
        foreach (self::ACTORS as $key => $actorName) {
            $actor = new Actor();
            $actor->setName($actorName);
            $actor->addProgram($this->getReference('program_' . rand(1,6)));
            $manager->persist($actor);
            $this->addReference('actor_' . $i, $actor);
            $i++;
        }
        $manager->flush();
    }

    public function getDependencies()
    {
        return [ProgramFixtures::class];
    }
}