<?php

namespace App\Command;

use App\Entity\Participant;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class NewUserCsvCommand extends Command
{
    protected static $defaultName = 'app:newUserCsv';

    public function __construct(EntityManagerInterface $entityManager, $name = null)
    {
        parent::__construct($name);
        $this->entityManager = $entityManager;
    }


    protected function configure()
    {
        $this
            ->setDescription("Permet d'integrer de nouveaux utilisateurs dans la bdd à partir du fichier .csv")
            ->addArgument('arg1', InputArgument::OPTIONAL, 'Argument description')
            ->addOption('option1', null, InputOption::VALUE_NONE, 'Option description')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $arg1 = $input->getArgument('arg1');


        $reader = Reader::createFromPath($this->getParameter('users_csv_directory'));

        $results = $reader->fetchAssoc();

        $io->progressStart(count($results));

        foreach ($results as $row) {

            // do a look up for existing Athlete matching first + last + dob
            // or create new athlete
            $participant = new Participant();



            $participant
                ->setNom($row['NOM'])
                ->setPrenom($row['PRENOM'])
                ->setTelephone($row['TELEPHONE'])
                ->setMail($row['MAIL'])
                ->setSite($row['SITE']);

            $administrateur = $row['ADMINISTRATEUR'];
            if($administrateur === "vrai"){
                $participant->setAdministrateur(1);
            }
            else{
                $participant->setAdministrateur(0);
            }

            $actif = $row['ACTIF'];
            if($actif === "vrai"){
                $participant->setActif(1);
            }
            else{
                $participant->setActif(0);
            }

            $this->em->persist($participant);
            $this->em->flush();
        }

              /*  ->setFirstName($row['first_name'])
                ->setLastName($row['last_name'])
                ->setDateOfBirth(new \DateTime($row['date_of_birth']))*/
            ;







        if ($arg1) {
            $io->note(sprintf('You passed an argument: %s', $arg1));
        }

        if ($input->getOption('option1')) {
            // ...
        }

        $io->success('You have a new command! Now make it your own! Pass --help to see your options.');

        return 0;
    }
}
