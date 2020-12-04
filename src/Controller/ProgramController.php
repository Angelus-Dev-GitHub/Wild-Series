<?php
//src/Controller/ProgramController.php

namespace App\Controller;


use App\Entity\Episode;
use App\Entity\Program;
use App\Entity\Season;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/programs", name="program_")
 */
class ProgramController extends AbstractController
{
    /**
     * Show all rows from Program's entity
     *
     * @Route("/", name="index")
     * @return Response response instance
     */
    public function index(): Response
    {
        $programs = $this->getDoctrine()
            ->getRepository(Program::class )
            ->findAll();
        return $this->render('program/index.html.twig', [
            'programs' => $programs,
        ]);
    }


    /**
     * Getting a program by id
     *
     * @Route("/show/{program_id}",name="show")
     * @return Response
     */
    public function show(Program $program_id): Response
    {

        return $this->render('program/show.html.twig', [
            'program' => $program_id,
        ]);
    }

    /**
     * Getting season by id
     *
     * @Route("/show/{program_id}/season", name="season_show")
     * @ParamConverter ("program", class="App\Entity\Program", options={"mapping": {"program_id": "id"}})
     *
     *
     */
    public function showSeason(Program $program): Response
    {
        $seasons = $this->getDoctrine()
            ->getRepository(Season::class)
            ->findBy(['program' => $program]);

        return $this->render('season/show.html.twig', [
            'program' => $program,
            'seasons' => $seasons
        ]);
    }

    /**
     * Getting episode by id
     *
     * @Route("/show/{program_id}/season/{season_number}/episode", name="season_episode")
     * @ParamConverter ("program", class="App\Entity\Program", options={"mapping": {"program_id": "id"}})
     * @ParamConverter ("season", class="App\Entity\Season", options={"mapping": {"season_number": "number"}})
     *
     */
    public function showEpisode(Program $program, Season $season) : Response
    {
        $episodes = $this->getDoctrine()
            ->getRepository(Episode::class)
            ->findBy(['season' => $season]);

        return $this->render('episode/show.html.twig', [
            'program' => $program,
            'season' => $season,
            'episodes' => $episodes
        ]);
    }
}