<?php
//src/Controller/ProgramController.php

namespace App\Controller;



use App\Entity\Comment;
use App\Entity\Episode;
use App\Entity\Program;
use App\Entity\Season;
use App\Form\CommentType;
use App\Form\SearchProgramType;
use App\Repository\ProgramRepository;
use App\Service\Slugify;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Routing\Annotation\Route;
use App\Form\ProgramType;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

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
    public function index(Request $request,
                          ProgramRepository $programRepository): Response
    {
        $form = $this->createForm(SearchProgramType::class);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $search = $form->getData()['search'];
            $programs = $programRepository->findLikeName($search);
        } else {
            $programs = $programRepository->findAll();
        }
        return $this->render('program/index.html.twig', [
            'programs' => $programs,
            'form' => $form->createView(),
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
            $program->setOwner($this->getUser());
            $entityManager->persist($program);
            $entityManager->flush();
            $email = (new Email())
                ->from($this->getParameter('mailer_from'))
                ->to('b7d5639be8-7bc9fc@inbox.mailtrap.io')
                ->subject('Une nouvelle série vient d\'être publiée !')
                ->html($this->renderView('program/newProgramEmail.html.twig',
                    ['program' => $program]));
            $mailer->send($email);

            $this->addFlash('success', 'Vous avez bien ajouté une nouvelle série');
            return $this->redirectToRoute('program_index');
        }
        return $this->render('program/new.html.twig', [
            "form" => $form->createView(),
        ]);
    }


    /**
     *
     * @Route("/{programSlug}", methods={"GET"}, name="show")
     * @ParamConverter ("program", class="App\Entity\Program", options={"mapping": {"programSlug": "slug"}})
     *
     * @return Response
     */
    public function show(Program $program): Response
    {
        $seasons = $this->getDoctrine()
            ->getRepository(Season::class)
            ->findBy(['program' => $program]);

        return $this->render('program/show.html.twig', [
            'program' => $program,
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
            $comment->setAuthor($this->getUser());
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

    /**
     * @Route("/{programSlug}/edit", name="edit", methods={"GET","POST"})
     * @ParamConverter ("program", class="App\Entity\Program", options={"mapping": {"programSlug": "slug"}})
     */
    public function edit(Request $request, Program $program): Response
    {
        if (!($this->getUser() == $program->getOwner())) {
            // If not the owner, throws a 403 Access Denied exception
            throw new AccessDeniedException('Only the owner can edit the program!');
        }

        $form = $this->createForm(ProgramType::class, $program);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->flush();
            $this->addFlash('success', 'Vous avez bien modifié la série');

            return $this->redirectToRoute('program_index');
        }

        return $this->render('program/edit.html.twig', [
            'program' => $program,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="delete", methods={"DELETE"})
     *
     */
    public function delete(Request $request, Program $program): Response
    {
        if ($this->isCsrfTokenValid('delete'.$program->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($program);
            $entityManager->flush();
        }

        $this->addFlash('danger', 'Vous avez supprimé une série');
        return $this->redirectToRoute('program_index');
    }




}