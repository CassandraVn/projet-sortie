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
            /*
            if( $_POST["campus"] != '')
            {
                $params['campus'] = $_POST["campus"];
            }
            if(  $_POST["nomSortie"] != '' )
            {
                $params['nomSortie'] = "%".$_POST["nomSortie"]."%";
            }
            if( $_POST["dateDepuis"] != '' )
            {
                $params['dateDepuis'] = $_POST["dateDepuis"];
            }
            if( $_POST["dateUntil"] != '' )
            {
                $params['dateUntil'] = $_POST["dateUntil"];
            }

            if( isset($_POST["orga"]) and $_POST["orga"] == 'on' )
            {
                $params['orga'] = $_POST["orga"];
            }
            if( isset($_POST["inscrit"]) and $_POST["inscrit"] == 'on' )
            {
                $params['inscrit'] = $_POST["inscrit"];
            }
            if( isset($_POST["pasInscrit"]) and $_POST["pasInscrit"] == 'on' )
            {
                $params['pasInscrit'] = $_POST["pasInscrit"];
            }
            if( isset($_POST["passees"]) and $_POST["passees"] == 'on' )
            {
                $params['passees'] = $_POST["passees"];
            }
            */

            $params = array_merge($params, $this->generateArray($_POST));
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

    #[Route('/{id}', name: 'app_sortie_show', methods: ['GET'])]
    public function show(Sortie $sortie, UtilisateurRepository $utilisateurRepo): Response
    {
        return $this->render('sortie/show.html.twig', [
            'sortie' => $sortie,
            'utilisateurs' => $utilisateurRepo->findAll()
        ]);
    }

    #[Route('/{id}/edit', name: 'app_sortie_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Sortie $sortie, SortieRepository $sortieRepository): Response
    {
        $form = $this->createForm(SortieType::class, $sortie);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $sortieRepository->save($sortie, true);

            return $this->redirectToRoute('app_sortie_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('sortie/edit.html.twig', [
            'sortie' => $sortie,
            'form' => $form,
        ]);

//        $sortForm = $this->createForm(SortieType::class,$sortie);
//        $sortForm->handleRequest($request);
//
//        if($sortForm->isSubmitted() && $sortForm->isValid()){
//            $sortieRepository->update();
//            return $this->redirectToRoute("app_sortie_index");
//        }
//
//        return $this->render('sortie/edit.html.twig',["sortForm"=>$sortForm->createView()]);
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

    private function generateArray($params)
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
