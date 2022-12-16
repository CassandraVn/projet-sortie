<?php

namespace App\Controller;

use App\Entity\Etat;
use App\Entity\Lieu;
use App\Entity\Sortie;
use App\Entity\Utilisateur;
use App\Form\FiltreType;
use App\Form\ImportCsvType;
use App\Form\LieuType;
use App\Form\model\FiltreFormModel;
use App\Form\model\ImportCsvFormModel;
use App\Form\SortieType;
use App\Repository\CampusRepository;
use App\Repository\EtatRepository;
use App\Repository\LieuRepository;
use App\Repository\SortieRepository;
use App\Repository\UtilisateurRepository;
use App\Service\FileUploader;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ObjectManager;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\Encoder\CsvEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;

#[Route('/sortie')]
class SortieController extends AbstractController
{
    #[Route('/', name: 'app_sortie_index', methods: ['GET', 'POST'])]
    public function index(Request $request, SortieRepository $sortieRepository, CampusRepository $campusRepository): Response
    {
        $form = $this->createForm(FiltreType::class);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            /** @var FiltreFormModel $filtre */
            $filtre = $form->getData();
            $sorties = $sortieRepository->findByFiltre($filtre, $this->getUser());
        }
        else
        {
            $sorties = $sortieRepository->findAllSorties($this->getUser());
        }

        return $this->render('sortie/index.html.twig', [
            'sorties' =>  $sorties,
            'lesCampus' => $campusRepository->findAll(),
            'filtreForm' => $form->createView()
        ]);
    }


    #[Route('/new', name: 'app_sortie_new', methods: ['GET', 'POST'])]
    public function new(Request $request, SortieRepository $sortieRepository, EtatRepository $etatRepo): Response
    {
        $etat = $etatRepo-> findOneBy(['libelle'=> 'Créée']);
        $sortie = new Sortie();
        $sortie->setEtat($etat);
        $sortie->setOrganisateur($this->getUser());
        $sortie->getParticipant()->add($this->getUser());
        $sortForm = $this->createForm(SortieType::class, $sortie);
        $sortForm->handleRequest($request);

        if ($sortForm->isSubmitted() && $sortForm->isValid()) {
            $sortieRepository->save($sortie, true);

            return $this->redirectToRoute('app_sortie_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('sortie/new.html.twig',[
            "sortForm"=>$sortForm->createView(),
            "lieuForm"=>$this->renderLieuForm()
        ]);

    }

    private function renderLieuForm(){
        $lieu = new Lieu();
        $lieuForm = $this->createForm(LieuType::class, $lieu);

        return $lieuForm->createView();
    }

//    #[IsGranted("ROLE_ADMIN")]
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


    #[Route('/{id}/ouvrire', name: 'app_sortie_ouvrir', methods: ['GET'])]
    public function ouvrirSortie(SortieRepository $sortieRepository, EtatRepository $etatRepository, Sortie $sortie)
    {
        if( $this->getUser() != $sortie->getOrganisateur() )
        {
            return $this->redirectToRoute('app_sortie_index');
        }
        if( !($sortie->getDateLimiteInscription() > new \DateTime()) )
        {
            $this->addFlash("message","La date limite d'inscription doit être supérieure à la date du jour");
        }
        elseif( !($sortie->getDateLimiteInscription() < $sortie->getDateHeureDebut()) )
        {
            $this->addFlash("message","La date limite d'inscription doit être inférieure à la date de l'évenement");
        }
        elseif( !($sortie->getNbInscriptionMax() > 1) )
        {
            $this->addFlash("message","Le nombre d'inscription max doit être supérieure à 1");
        }
        elseif( !($sortie->getDuree() > 15) )
        {
            $this->addFlash("message","La durée doit être supérieure à 15min");
        }
        else
        {
            $sortie->setEtat(
                $etatRepository->findOneBy(['libelle'=>'Ouverte'])
            );
            $sortieRepository->save($sortie, true);
            return $this->redirectToRoute('app_sortie_index');
        }
        return $this->redirectToRoute('app_sortie_edit',['id'=>$sortie->getId()]);
    }


    #[Route('/inscritionDesistementSortie/{id}', name: 'app_inscription_desistement_sortie', methods: ['GET'])]
    public function inscritionDesistementSortie(Request $request, SortieRepository $sortieRepository, EtatRepository $etatRepository , Sortie $sortie)
    {
        if( $this->isGranted('INSCRIPTION', $sortie) )
        {
            $sortie->addParticipant($this->getUser());
            if( $sortie->getNbInscriptionMax() == $sortie->getParticipant()->count() )
            {
                $sortie->setEtat(
                    $etatRepository->findOneBy(['libelle'=>'Clôturée'])
                );
            }
            $sortieRepository->save($sortie, true);
        }
        elseif( $this->isGranted('DESISTEMENT', $sortie) )
        {
            $sortie->removeParticipant($this->getUser());
            if( $sortie->getEtat()->getLibelle() == 'Clôturée' and $sortie->getDateLimiteInscription() > new \DateTime())
            {
                $sortie->setEtat(
                    $etatRepository->findOneBy(['libelle'=>'Ouverte'])
                );
            }
            $sortieRepository->save($sortie, true);
        }

        return $this->redirectToRoute('app_sortie_index', [], Response::HTTP_SEE_OTHER);
    }






}
