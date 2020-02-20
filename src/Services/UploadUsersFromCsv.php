<?php


namespace App\Services;

use App\Entity\Participant;
use App\Entity\Site;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use League\Csv\Reader;
use Doctrine\ORM\EntityManagerInterface;

class UploadUsersFromCsv
{

    public function __construct(EntityManagerInterface $entityManager, UserPasswordEncoderInterface $passwordEncoder, $path)
    {
        $this->entityManager = $entityManager;
        $this->passwordEncoder = $passwordEncoder;
        $this->path = $path;
    }

    public function uploadImages(){


//'%kernel.root_dir%/public/usersCsv/users.csv'
        $reader = Reader::createFromPath($this->path);//$this->getParameter('users_csv_directory'), 'r'
//set r mode on the file to ensure it can be opened
        $reader->setHeaderOffset(0);
       // $header_offset = $reader->getHeaderOffset(); //returns 0
      //  $header = $reader->getHeader();

        $siteRepository = $this->entityManager->getRepository(Site::class);

        $results = $reader->getRecords();


        foreach ($results as $offset => $result) {

            $participant = new Participant();

            $participant
                ->setNom($result['NOM'])
                ->setPrenom($result['PRENOM'])
                ->setTelephone($result['TELEPHONE'])
                ->setMail($result['MAIL']);

            $site = $result['SITE'];
            if($site === "SAINT HERBLAIN"){
                $participant->setSite($siteRepository->find(1));
            }
            else if($site === "CHARTRES DE BRETAGNE"){
                $participant->setSite($siteRepository->find(2));
            }
            else if($site === "LA ROCHE SUR YON"){
                $participant->setSite($siteRepository->find(3));
            }

            $administrateur = $result['ADMINISTRATEUR'];
            if($administrateur === "vrai"){
                $participant->setAdministrateur(1);
            }
            else{
                $participant->setAdministrateur(0);
            }

            $actif = $result['ACTIF'];
            if($actif === "vrai"){
                $participant->setActif(1);
            }
            else{
                $participant->setActif(0);
            }

            $password = "azerty";

//            // génération d'un mot de passe aléatoire de 10 chiffres
//            for ($i = 0; $i < 10; $i++) {
//                $motDePasse = $motDePasse . rand() % (10);
//            }

            // cryptage du mot de passe
            $participant->setPassword(
                $this->passwordEncoder->encodePassword(
                    $participant,
                    $password
                )
            );

            $participant
                ->setPhoto(null)
                ->setPseudo(null);

            $this->entityManager->persist($participant);
            $this->entityManager->flush();
    }

}}