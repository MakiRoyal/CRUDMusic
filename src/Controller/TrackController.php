<?php

namespace App\Controller;

use App\Entity\Track;
use App\Form\TrackType;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping\Entity;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\String\Slugger\SluggerInterface;

class TrackController extends AbstractController
{
    #[Route('/track', name: 'app_track')]
    public function index(EntityManagerInterface $em, Request $r, SluggerInterface $slugger): Response
    {
        $a_track = new Track();
        $form = $this->createForm(TrackType::class, $a_track);

        dump($r);
        $form->handleRequest($r);

        $user = $this->getUser();
        
        if($form->isSubmitted() && $form->isValid() && $user->getRoles()[0] == "ROLE_ADMIN") {
            $slug = $slugger->slug($a_track->getTitle()) . '-' . uniqid(); 
            $a_track->setSlug(($slug));

            $em->persist($a_track);
            $em->flush();

            return $this->redirectToRoute('app_track');
        }

        $tracks = $em->getRepository(Track::class)->findAll();

        return $this->render('track/index.html.twig', [
            'tracks' => $tracks,
            'form' => $form->createView()
        ]);
    }

    #[Route('/track/delete/{id}', name:'app_track_delete')]
    public function delete(Request $r, EntityManagerInterface $em, Track $track) {
        if($this->isCsrfTokenValid('delete' . $track->getId(), $r->request->get('csrf'))) {
            $em->remove($track);
            $em->flush();
        }

        return $this->redirectToRoute(('app_track'));
    }

    #[Route('/track/edit/{id}', name:'app_track_edit')]
public function edit(Request $request, EntityManagerInterface $em, Track $track): Response
{
    $form = $this->createForm(TrackType::class, $track);

    $form->handleRequest($request);

    if ($form->isSubmitted() && $form->isValid()) {
        // Vous pouvez ajouter des validations ou des traitements supplémentaires si nécessaire

        $em->flush();

        return $this->redirectToRoute('app_track_show', ['slug' => $track->getSlug()]);
    }

    return $this->render('track/edit.html.twig', [
        'form' => $form->createView(),
        'track' => $track,
    ]);
}


    #[Route('/track/{slug}', name:'app_track_show')]
    public function show(Track $track) {
        return $this->render('track/show.html.twig', [
            'track' => $track
        ]);
    }
}
