<?php

namespace App\Command;

use App\Services\GestionSorties\CloturerInscription;
use App\Services\GestionSorties\EnCoursEtat;
use App\Services\GestionSorties\OuvertureEtat;
use App\Services\GestionSorties\PasseEtat;
use Doctrine\ORM\EntityManagerInterface;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class GestionEtatCommand extends Command
{
    protected static $defaultName = 'app:gestionEtat';

    public function __construct(EntityManagerInterface $entityManager, $name = null)
    {
        parent::__construct($name);
        $this->entityManager = $entityManager;
    }

    protected function configure()
    {
        $this
            ->setDescription('Add a short description for your command')
            ->addArgument('arg1', InputArgument::OPTIONAL, 'Argument description')
            ->addOption('option1', null, InputOption::VALUE_NONE, 'Option description')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $arg1 = $input->getArgument('arg1');

        $ouverture = new OuvertureEtat($this->entityManager);
        $ouverture->suprimerSiPasPublie();
        $ouverture->ouvrir();

        $cloturerInscription = new CloturerInscription($this->entityManager);
        $cloturerInscription->cloturerDateLimite();
        $cloturerInscription->cloturerInscriptionNbMax();

        $encours = new EnCoursEtat($this->entityManager);
        $encours->setEtatEnCours();

        $passe = new PasseEtat($this->entityManager);
        $passe->setEtatPasse();


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
