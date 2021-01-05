<?php

namespace App\Controller;

use App\Entity\Actor;
use App\Entity\Program;
use App\Form\ActorType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/actor", name="actor_")
 */
class ActorController extends AbstractController
{
    /**
     * @Route("/", name="index")
     */
    public function index(): Response
    {
        $actors = $this->getDoctrine()
            ->getRepository(Actor::class)
            ->findAll();
        return $this->render('actor/index.html.twig', [
            'actors' => $actors
        ]);
    }

    /**
     * @Route("/{actor_id}", methods={"GET"}, name="show")
     * @ParamConverter("actor", class="App\Entity\Actor", options={"mapping": {"actor_id": "id"}})
     */
    public function show(Actor $actor):Response
    {
        $program = $this->getDoctrine()
            ->getRepository(Program::class)
            ->findBy(['id' => $actor]);
        return $this->render('actor/show.html.twig', [
            'program' => $program,
            'actor' => $actor
        ]);
    }

    /**
     * @Route("/{actor_id}/edit", methods={"GET","POST"}, name="edit")
     * @ParamConverter("actor", class="App\Entity\Actor", options={"mapping": {"actor_id": "id"}})
     */
    public function edit(Request $request, Actor $actor):Response
    {
        $formActor = $this->createForm(ActorType::class, $actor);
        $formActor->handleRequest($request);

        if ($formActor->isSubmitted() && $formActor->isValid()) {
            $this->getDoctrine()->getManager()->flush();
            $this->addFlash('success', 'Vous avez bien modifié l\'acteur ou l\'actrice');

            return $this->redirectToRoute('actor_index');
        }

        return $this->render('actor/edit.html.twig', [
            'formActor' => $formActor->createView(),
            'actor' => $actor,
        ]);
    }

    /**
     * @Route("/{id}", name="delete", methods={"DELETE"})
     *
     */
    public function delete(Request $request, Actor $actor): Response
    {
        if ($this->isCsrfTokenValid('delete'.$actor->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($actor);
            $entityManager->flush();
        }

        $this->addFlash('danger', 'Vous avez supprimé un acteur ou actrice');
        return $this->redirectToRoute('actor_index');
    }

}
