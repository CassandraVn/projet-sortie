<?php

namespace App\Controller;

use App\Entity\Utilisateur;
use App\Form\UtilisateurAdminType;
use App\Form\UtilisateurType;
use App\Repository\UtilisateurRepository;
use App\Service\FileUploader;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/utilisateur')]
class UtilisateurController extends AbstractController
{
    #[Route('/', name: 'app_utilisateur_index', methods: ['GET'])]
    public function index(UtilisateurRepository $utilisateurRepository): Response
    {
        return $this->render('utilisateur/index.html.twig', [
            'utilisateurs' => $utilisateurRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_utilisateur_new', methods: ['GET', 'POST'])]
    public function new(Request $request, UserPasswordHasherInterface $hasher, UtilisateurRepository $utilisateurRepository, FileUploader $fileUploader): Response
    {
        $utilisateur = new Utilisateur();
        $form = $this->createForm(UtilisateurAdminType::class, $utilisateur);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $password = $hasher->hashPassword($this->getUser(), $form->get('plainPassword')->getData());
            $utilisateur->setPassword($password);
            $utilisateur->setActif(true);

            $photo = $form->get('photo')->getData();

            if ($photo) {
                $photoFileName = $fileUploader->upload($photo);
                $utilisateur->setPhoto($photoFileName);
            }

            $utilisateurRepository->save($utilisateur, true);

            return $this->redirectToRoute('app_utilisateur_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('utilisateur/new.html.twig', [
            'utilisateur' => $utilisateur,
            'formUser' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_utilisateur_profil', methods: ['GET'], requirements: ['id'=>'[0-9]+'])]
    public function show(Utilisateur $utilisateur): Response
    {
        return $this->render('utilisateur/show.html.twig', [
            'utilisateur' => $utilisateur,
        ]);
    }

    #[Route('/edit', name: 'app_utilisateur_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, UserPasswordHasherInterface $hasher, UtilisateurRepository $utilisateurRepository, FileUploader $fileUploader): Response

    {
        $form = $this->createForm(UtilisateurType::class, $this->getUser());
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $photo = $form->get('photo')->getData();

            if ($photo) {
                $photoFileName = $fileUploader->upload($photo);

                $this->getUser()->setPhoto($photoFileName);
            }

            if (!empty($form->get('plainPassword')->getData())) {
                $password = $hasher->hashPassword($this->getUser(), $form->get('plainPassword')->getData());
                $this->getUser()->setPassword($password);
            }

            $utilisateurRepository->save($this->getUser(), true);

            return $this->redirectToRoute('app_utilisateur_index', [], Response::HTTP_SEE_OTHER);
        }
        return $this->renderForm('utilisateur/edit.html.twig', ['utilisateur' => $this->getUser(), 'formUser' => $form]);
    }

    #[IsGranted("ROLE_ADMIN")]
    #[Route('/delete/{id}', name: 'app_utilisateur_delete', methods: ['POST'])]
    public function delete(Request $request, Utilisateur $utilisateur, UtilisateurRepository $utilisateurRepository): Response
    {
        if ($this->isCsrfTokenValid('delete'.$utilisateur->getId(), $request->request->get('_token'))) {
            $utilisateurRepository->remove($utilisateur, true);
        }

        return $this->redirectToRoute('app_utilisateur_index', [], Response::HTTP_SEE_OTHER);
    }

    #[IsGranted("ROLE_ADMIN")]
    #[Route('/actifInactif/{id}', name: 'app_utilisateur_actif_inactif', methods: ['GET'])]
    public function setUserActifInactif(UtilisateurRepository $utilisateurRepository, Utilisateur $user)
    {
        if( $user->isActif() )
        {
            $user->setActif(false);
        }
        else
        {
            $user->setActif(true);
        }
        $utilisateurRepository->save($user, true);
        return $this->redirectToRoute('app_utilisateur_index', [], Response::HTTP_SEE_OTHER);
    }
}
