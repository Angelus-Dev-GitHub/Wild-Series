<?php

namespace App\Controller;

use App\Entity\Episode;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;


/**
 * @Route("/seasons", name="season_")
 */
class SeasonController extends AbstractController
{
    /**
     * @Route("/", name="index")
     */
    public function index(): Response
    {
        return $this->render('season/index.html.twig', [
            'controller_name' => 'SeasonController',
        ]);
    }

    /**
     * Getting episodes by id
     *
     * @Route("/show/{seasonId}", name="show")
     *
     */
    public function show(int $seasonId): Response
    {
        $episodes = $this->getDoctrine()
            ->getRepository(Episode::class)
            ->findBy(['season' => $seasonId]);

        return $this->render('season/show.html.twig', [
           'episodes' => $episodes,
            'seasonID' => $seasonId
        ]);
    }


}
