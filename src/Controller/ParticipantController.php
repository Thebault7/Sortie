<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Routing\Annotation\Route;

class ParticipantController extends Controller
{
    /**
     * @Route("/participant", name="participant")
     */
    public function index()
    {
        return $this->render('registration/register.html.twig');
    }
}
