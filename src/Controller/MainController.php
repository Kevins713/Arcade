<?php

namespace App\Controller;

use App\Repository\CategoryRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;

class MainController extends AbstractController
{
    /**
     * Page d'accueil
     * @Route("/", name="home")
     */
    public function index(CategoryRepository $categories,Request $request): Response
    {
        // Requête du flux RSS des actualités
        $rss = simplexml_load_file('https://www.jeuxactu.com/rss/multi.rss');

        return $this->render('main/index.html.twig', [
            'categories' => $categories->findAll(),
            'rss' => $rss->channel->item
        ]);
    }


    /**
     * @Route("/mon-profil", name="main_profil")
     * @Security("is_granted('ROLE_USER')")
     */
    public function profil(): Response
    {
        return $this->render('main/profil.html.twig');
    }



}
