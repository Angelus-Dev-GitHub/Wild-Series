<?php

namespace App\DataFixtures;

use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class UserFixtures extends Fixture
{
    private $passwordEncoder;

    public function __construct(UserPasswordEncoderInterface $passwordEncoder)
    {
        $this->passwordEncoder = $passwordEncoder;
    }


    public function load(ObjectManager $manager)
    {
        $contributor1 = new User();
        $contributor1->setEmail('t.dufosse666@gmail.com');
        $contributor1->setRoles(['ROLE_CONTRIBUTOR']);
        $contributor1->setPassword($this->passwordEncoder->encodePassword(
            $contributor1,
            'thierryrpassword'
        ));
        $manager->persist($contributor1);
        $this->addReference('contributor', $contributor1);


        // Création d’un utilisateur de type “contributor”
        $contributor = new User();
        $contributor->setEmail('subscriber@monsite.com');
        $contributor->setRoles(['ROLE_CONTRIBUTOR']);
        $contributor->setPassword($this->passwordEncoder->encodePassword(
            $contributor,
            'subscriberpassword'
        ));

        $manager->persist($contributor);

        // Création d’un utilisateur de type “administrateur”

        $admin = new User();
        $admin->setEmail('admin@monsite.com');
        $admin->setRoles(['ROLE_ADMIN']);
        $admin->setPassword($this->passwordEncoder->encodePassword(
            $admin,
            'adminpassword'
        ));

        $manager->persist($admin);

        // Sauvegarde des 2 nouveaux utilisateurs :

        $manager->flush();
    }
}
