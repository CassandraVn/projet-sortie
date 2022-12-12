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
            $sorties = $sortieRepository->findByFiltre($filtre, $this->getUser()->getId());
        }
        else
        {
            $sorties = $sortieRepository->findAllSorties();
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
        if( $this->isGranted('INSCRIPTION', $sortie) )
        {
            $sortie->addParticipant($this->getUser());
            $sortieRepository->save($sortie, true);
        }
        elseif( $this->isGranted('DESISTEMENT', $sortie) )
        {
            $sortie->removeParticipant($this->getUser());
            $sortieRepository->save($sortie, true);
        }

        return $this->redirectToRoute('app_sortie_index', [], Response::HTTP_SEE_OTHER);
    }

    #[Route('/importCsv/importCsv', name: 'app_import_csv', methods: ['GET', 'POST'])]
    public function importUserCsv(Request $request, CampusRepository $campusRepository, UtilisateurRepository $utilisateurRepository, UserPasswordHasherInterface $hasher, FileUploader $fileUploader, EntityManagerInterface $em)
    {
        $form = $this->createForm(ImportCsvType::class);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            /** @var ImportCsvFormModel $csv */
            $csv = $form->get('csv')->getData();
            if ($csv) {
                $photoFileName = $fileUploader->upload($csv);
                $csv2 = new ImportCsvFormModel();
                $csv2->setCsv($photoFileName);
            }
            $normalizers = [new ObjectNormalizer()];
            $encoders = [new CsvEncoder()];

            $serializer = new Serializer($normalizers, $encoders);

            $fileSTR = file_get_contents( './uploads/photos/'.$csv2->getCsv() );
            $users = $serializer->decode($fileSTR, "csv", [CsvEncoder::DELIMITER_KEY => ';']);

            foreach($users as $row)
            {
                $user = new Utilisateur();
                $password = $hasher->hashPassword($user, "1234");

                $user->setPseudo( $this->generateRandomString() )
                    ->setPrenom($row['prenom'])
                    ->setNom($row['nom'])
                    ->setMail($row['mail'])
                    ->setTelephone($row['telephone'])
                    ->setActif(true)
                    ->setCampus($campusRepository->findOneBy(['nom'=>'Nicolas']))
                    ->setPassword($password);
                $em->persist($user);
            }
            $em->flush();
        }

        return $this->render('sortie/importCsv.html.twig', [
            'csvForm' => $form->createView()
        ]);
    }

    function generateRandomString()
    {
        $length = random_int(8, 16);
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }
        return $randomString;
    }




}
