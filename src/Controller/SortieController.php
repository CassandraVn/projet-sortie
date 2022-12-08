<?php

namespace App\Controller;

use App\Entity\Etat;
use App\Entity\Sortie;
use App\Entity\Utilisateur;
use App\Form\SortieType;
use App\Repository\CampusRepository;
use App\Repository\EtatRepository;
use App\Repository\SortieRepository;
use App\Repository\UtilisateurRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/sortie')]
class SortieController extends AbstractController
{
    #[Route('/', name: 'app_sortie_index', methods: ['GET', 'POST'])]
    public function index(SortieRepository $sortieRepository, CampusRepository $campusRepository): Response
    {
        $params = array("user"=>$this->getUser()->getId());
        if( !empty($_POST) )
        {
            $params = array_merge($params, $this->generateParamsArray($_POST));
            $sorties = $sortieRepository->findByFiltre($params);
        }
        else
        {
            $sorties = $sortieRepository->findByFiltre();
        }

        return $this->render('sortie/index.html.twig', [
            'sorties' =>  $sorties,
            'lesCampus' => $campusRepository->findAll(),
            'params' => $params
        ]);
    }

    #[Route('/new', name: 'app_sortie_new', methods: ['GET', 'POST'])]
    public function new(Request $request, SortieRepository $sortieRepository, EtatRepository $etatRepo): Response
    {
        $etat = $etatRepo-> findOneBy(['libelle'=> 'Créée']);
        $sortie = new Sortie();
        $sortie->setEtat($etat);
        $sortie->setOrganisateur($this->getUser());
        $sortForm = $this->createForm(SortieType::class, $sortie);
        $sortForm->handleRequest($request);

        if ($sortForm->isSubmitted() && $sortForm->isValid()) {
            $sortieRepository->save($sortie, true);

            return $this->redirectToRoute('app_sortie_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('sortie/new.html.twig',["sortForm"=>$sortForm->createView()]);

    }


    #[Route('/{id}/cancel', name: 'app_sortie_cancel', methods: ['GET', 'POST'])]
    public function cancel(Request $request, SortieRepository $sortieRepository, Sortie $sortie,  EtatRepository $etatRepo): Response
    {

        $etat = $etatRepo-> findOneBy(['libelle'=> 'Annulée']);
        $sortie->setEtat($etat);
        $sortForm = $this->createForm(SortieType::class, $sortie);
        $sortForm->handleRequest($request);

        if ($sortForm->isSubmitted() && $sortForm->isValid()) {
            $sortieRepository->save($sortie, true);

            return $this->redirectToRoute('app_sortie_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('sortie/cancel.html.twig', [
            'sortie' => $sortie,
            'sortForm' => $sortForm,
        ]);

    }



    #[Route('/{id}', name: 'app_sortie_show', methods: ['GET'])]
    public function show(SortieRepository $sortieRepository, int $id): Response
    {
        return $this->render('sortie/show.html.twig', [
            'sortie' => $sortieRepository->findSortieById($id)
        ]);
    }

    #[Route('/{id}/edit', name: 'app_sortie_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Sortie $sortie, SortieRepository $sortieRepository): Response
    {
        $sortForm = $this->createForm(SortieType::class, $sortie);
        $sortForm->handleRequest($request);

        if ($sortForm->isSubmitted() && $sortForm->isValid()) {
            $sortieRepository->save($sortie, true);

            return $this->redirectToRoute('app_sortie_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('sortie/edit.html.twig', [
            'sortie' => $sortie,
            'sortForm' => $sortForm,0
        ]);
    }

    #[Route('/{id}', name: 'app_sortie_delete', methods: ['POST'])]
    public function delete(Request $request, Sortie $sortie, SortieRepository $sortieRepository): Response
    {
        if ($this->isCsrfTokenValid('delete'.$sortie->getId(), $request->request->get('_token'))) {
            $sortieRepository->remove($sortie, true);
        }

        return $this->redirectToRoute('app_sortie_index', [], Response::HTTP_SEE_OTHER);
    }

    #[Route('/inscritionDesistementSortie/{id}', name: 'app_inscription_desistement_sortie', methods: ['GET'])]
    public function inscritionDesistementSortie(Request $request, SortieRepository $sortieRepository, Sortie $sortie)
    {
        if ($sortie->getParticipant()->contains($this->getUser())) {
            $sortie->removeParticipant($this->getUser());
            $sortieRepository->save($sortie, true);
        }
        elseif( $sortie->getNbInscriptionMax() != count($sortie->getParticipant()) )
        {
            $sortie->addParticipant($this->getUser());
            $sortieRepository->save($sortie, true);
        }

        return $this->redirectToRoute('app_sortie_index', [], Response::HTTP_SEE_OTHER);
    }

    private function generateParamsArray($params)
    {
        $tempArr = array();
        $keys = array_keys($params);
        for($i=0; $i< count($keys); $i++)
        {
            if( !empty($params[$keys[$i]] ) )
            {
                $tempArr[$keys[$i]] = $params[$keys[$i]];
            }
        }
        return $tempArr;
    }
}
