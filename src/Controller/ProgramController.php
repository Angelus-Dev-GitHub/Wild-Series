<?php
//src/Controller/ProgramController.php

namespace App\Controller;



use App\Entity\Comment;
use App\Entity\Episode;
use App\Entity\Program;
use App\Entity\Season;
use App\Form\CommentType;
use App\Service\Slugify;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
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
    public function new(Request $request, Slugify $slugify, MailerInterface $mailer) : Response
    {
        $program = new Program();
        $form = $this->createForm(ProgramType::class, $program);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();
            $slug = $slugify->generate($program->getTitle());
            $program->setSlug($slug);
            $entityManager->persist($program);
            $entityManager->flush();
            $email = (new Email())
                ->from($this->getParameter('mailer_from'))
                ->to('b7d5639be8-7bc9fc@inbox.mailtrap.io')
                ->subject('Une nouvelle série vient d\'être publiée !')
                ->html($this->renderView('program/newProgramEmail.html.twig',
                    ['program' => $program]));
            $mailer->send($email);

            return $this->redirectToRoute('program_index');
        }
        return $this->render('program/new.html.twig', [
            "form" => $form->createView(),
        ]);
    }


    /**
     *
     * @Route("/{slug}", methods={"GET"}, name="show")
     *
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
     *
     * @Route("/{programSlug}/season/{season_number}", name="season_show")
     * @ParamConverter ("program", class="App\Entity\Program", options={"mapping": {"programSlug": "slug"}})
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
     *
     * @Route("/{programSlug}/seasons/{season_number}/episodes/{episodeSlug}", name="season_episode")
     * @ParamConverter ("program", class="App\Entity\Program", options={"mapping": {"programSlug": "slug"}})
     * @ParamConverter ("season", class="App\Entity\Season", options={"mapping": {"season_number": "number"}})
     * @ParamConverter ("episode", class="App\Entity\Episode", options={"mapping": {"episodeSlug": "slug"}})
     *
     */
    public function showEpisode(Program $program,
                                Season $season,
                                Episode $episode,
                                Request $request,
                                EntityManagerInterface $entityManager) : Response
    {
        $comment = new Comment();
        $form = $this->createForm(CommentType::class, $comment);
        $form->handleRequest($request);
        $user = $this->getUser();
        if($form->isSubmitted() && $form->isValid()){
            $entityManager = $this->getDoctrine()->getManager();
            $comment->setAuthor($user);
            $comment->setEpisode($episode);
            $entityManager->persist($comment);
            $entityManager->flush();
        }
        $comments = $this->getDoctrine()
            ->getRepository(Comment::class)
            ->findBy(['episode' => $episode]);

        return $this->render('episode/show.html.twig', [
            'program' => $program,
            'season' => $season,
            'episode' => $episode,
            'comments' => $comments,
            'form' => $form->createView(),
        ]);
    }
}