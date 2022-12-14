<?php

namespace App\DataFixtures;

use App\Entity\Campus;
use App\Entity\Etat;
use App\Entity\Lieu;
use App\Entity\Sortie;
use App\Entity\Utilisateur;
use App\Entity\Ville;
use App\Repository\CampusRepository;
use App\Repository\EtatRepository;
use App\Repository\LieuRepository;
use App\Repository\UtilisateurRepository;
use App\Repository\VilleRepository;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;
use Faker\Generator;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;


class AppFixtures extends Fixture
{
    private ObjectManager $manager;
    private Generator $generator;
    private VilleRepository $repoVille;
    private CampusRepository $repoCampus;
    private UtilisateurRepository $repoUtilisateur;
    private EtatRepository $repoEtat;
    private LieuRepository $repoLieu;
    private UserPasswordHasherInterface $hasher;

    public function __construct(UserPasswordHasherInterface $hasher, VilleRepository $repoVille, CampusRepository $repoCampus, UtilisateurRepository $repoUtilisateur, EtatRepository $repoEtat, LieuRepository $repoLieu) {
        $this->generator = Factory::create('fr_FR');
        $this->repoVille = $repoVille;
        $this->repoCampus = $repoCampus;
        $this->repoUtilisateur = $repoUtilisateur;
        $this->repoEtat =  $repoEtat;
        $this->repoLieu =  $repoLieu;
        $this->hasher = $hasher;
    }

    public function load(ObjectManager $manager): void
    {
        $this->manager = $manager;
        $this->addEtat();
        $this->addCampus();
        $this->addUser($this->repoCampus);
        $this->addVille();
        $this->addLieu($this->repoVille);
        $this->addSortie($this->repoCampus,  $this->repoLieu, $this->repoEtat, $this->repoUtilisateur);
    }

    public function addEtat() {
        $etat = new Etat();
        $etat->setLibelle("Créée");
        $ouverte = new Etat();
        $ouverte->setLibelle("Ouverte");
        $cloture = new Etat();
        $cloture->setLibelle("Clôturée");
        $encours = new Etat();
        $encours->setLibelle("Activité en cours");
        $passee = new Etat();
        $passee->setLibelle("Passée");
        $annule = new Etat();
        $annule->setLibelle("Annulée");
        $historise = new Etat();
        $historise->setLibelle("Historisée");

        $this->manager->persist($etat);
        $this->manager->persist($ouverte);
        $this->manager->persist($cloture);
        $this->manager->persist($encours);
        $this->manager->persist($passee);
        $this->manager->persist($annule);
        $this->manager->persist($historise);

        $this->manager->flush();
    }

    public function addCampus() {
        for ($i = 0; $i < 10; $i++) {
            $campus = new Campus();
            $campus->setNom($this->generator->company);
            $this->manager->persist($campus);
        }
        $this->manager->flush();
    }

    public function addVille() {
        for ($i = 0; $i < 10; $i++) {
            $ville = new Ville();
            $ville->setNom($this->generator->city);
            $ville->setCodePostal($this->generator->postcode);
            $this->manager->persist($ville);
        }
        $this->manager->flush();
    }

    public function addLieu(VilleRepository $repoVille) {
        $lieux = $this->repoVille->findAll();
        for ($i = 0; $i < 3; $i++) {
            $lieu = new Lieu();
            $lieu->setNom($this->generator->colorName);
            $lieu->setRue($this->generator->address);
            $lieu->setVille($this->generator->randomElement($lieux));
            $lieu->setLatitude($this->generator->randomFloat());
            $this->manager->persist($lieu);
        }
        $this->manager->flush();
    }

    public function addUser(CampusRepository $campusRepo) {

        $campus = $this->repoCampus->findAll();

        for ($i = 0; $i <3; $i++) {
            $user = new Utilisateur();
            $user->setMail($this->generator->companyEmail);
            $password = $this->hasher->hashPassword($user, '1234');
            $user->setPassword($password);
            $user->setPrenom($this->generator->firstName);
            $user->setNom($this->generator->lastName);
            $user->setPseudo($this->generator->userName);
            $user->setRoles([]);
            $user->setCampus($this->generator->randomElement($campus));
            $user->setTelephone($this->generator->phoneNumber);
            $user->setActif(true);
            $this->manager->persist($user);
        }
        $this->manager->flush();
    }

    public function addSortie(CampusRepository $campusRepo, LieuRepository $lieuRepo, EtatRepository $etatRepo, UtilisateurRepository $userRepo) {
        $campus =  $this->repoCampus->findAll();
        $lieu = $this->repoLieu->findAll();
        $etat = $this->repoEtat->findAll();
        $user = $this->repoUtilisateur->findAll();
        for ($i = 0; $i < 10; $i++) {
            $sortie = new Sortie();
            $sortie->setNom($this->generator->domainName);
            $sortie->setCampus($this->generator->randomElement($campus));
            $sortie->setLieu($this->generator->randomElement($lieu));
            $sortie->setDateHeureDebut($this->generator->dateTimeThisMonth);
            $sortie->setDateLimiteInscription($this->generator->dateTimeThisYear);
            $sortie->setNbInscriptionMax(10);
            $sortie->setEtat($this->generator->randomElement($etat));
            $sortie->setDuree($this->generator->randomDigit);
            $sortie->setOrganisateur($this->generator->randomElement($user));
            $this->manager->persist($sortie);
        }
        $this->manager->flush();
    }

}
