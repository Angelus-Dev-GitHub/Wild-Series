<?php
//src/Controller/ProgramController.php

namespace App\Controller;


use App\Entity\Actor;
use App\Entity\Episode;
use App\Entity\Program;
use App\Entity\Season;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Form\ProgramType;

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
     * The controller for the category add form
     *
     * @Route("/new", name="new")
     */
    public function new(Request $request) : Response
    {
        // Create a new Category Object
        $category = new Program();
        // Create the associated Form
        $form = $this->createForm(ProgramType::class, $category);
        // Get data from HTTP request
        $form->handleRequest($request);
        // Was the form submitted ?
        if ($form->isSubmitted() && $form->isValid()) {
            // Deal with the submitted data
            // Get the Entity Manager
            $entityManager = $this->getDoctrine()->getManager();
            // Persist Category Object
            $entityManager->persist($category);
            // Flush the persisted object
            $entityManager->flush();
            // Finally redirect to categories list
            return $this->redirectToRoute('program_index');
        }
        // Render the form
        return $this->render('program/new.html.twig', [
            "form" => $form->createView(),
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
        $seasons = $this->getDoctrine()
            ->getRepository(Season::class)
            ->findBy(['program' => $program_id]);

        return $this->render('program/show.html.twig', [
            'program' => $program_id,
            'seasons'=> $seasons,
        ]);
    }

    /**
     * Getting season by id
     *
     * @Route("/show/{program_id}/season/{season_number}", name="season_show")
     * @ParamConverter ("program", class="App\Entity\Program", options={"mapping": {"program_id": "id"}})
     * @ParamConverter ("season", class="App\Entity\Season", options={"mapping": {"season_number": "number"}})
     *
    */
    public function showSeason(Program $program, Season $season): Response
    {
        $episodes = $this->getDoctrine()
            ->getRepository(Episode::class)
            ->findBy(['season' => $season]);

        return $this->render('season/show.html.twig', [
            'program' => $program,
            'season' => $season,
            'episodes' => $episodes
        ]);
    }

    /**
     * Getting episode by id
     *
     * @Route("/show/{program_id}/season/{season_number}/episode/{episode_number}", name="season_episode")
     * @ParamConverter ("program", class="App\Entity\Program", options={"mapping": {"program_id": "id"}})
     * @ParamConverter ("season", class="App\Entity\Season", options={"mapping": {"season_number": "number"}})
     * @ParamConverter ("episode", class="App\Entity\Episode", options={"mapping": {"episode_number": "number"}})
     *
     */
    public function showEpisode(Program $program, Season $season, Episode $episode) : Response
    {
        return $this->render('episode/show.html.twig', [
            'program' => $program,
            'season' => $season,
            'episode' => $episode
        ]);
    }
}