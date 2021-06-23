<?php

namespace App\Controller;

use App\Repository\CategoryRepository;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use App\Form\EditPhotoType;
use App\Form\EditEmailType;
use App\Form\EditPasswordType;
use App\Form\EditDescriptionType;
use App\Entity\Comment;
use App\Entity\User;


class MainController extends AbstractController
{
    /**
     * Page d'accueil
     * @Route("/", name="home")
     */
    public function index(CategoryRepository $categories, UserRepository $userRepo): Response
    {
        // Requête du flux RSS des actualités
        $rss = simplexml_load_file('https://www.jeuxactu.com/rss/multi.rss');
        $userRepo->findConnectedAdmins();

        return $this->render('main/index.html.twig', [
            'categories' => $categories->findAll(),
            'rss' => $rss->channel->item
        ]);
    }


    /**
     * @Route("/mon-profil/", name="main_profil")
     * @Security("is_granted('ROLE_USER')")
     */
    public function profil(Request $request): Response
    {

        $commentRepo = $this->getDoctrine()->getRepository(Comment::class);

        $comments = $commentRepo->findBy([], ['publicationDate' => 'DESC']);

        return $this->render('main/profil.html.twig', [
            'comments' => $comments,
        ]);
    }


    /**
     * @Route("/edit-profil/", name="edit_profil")
     * @Security("is_granted('ROLE_USER')")
     */
    public function editProfil(Request $request, UserPasswordEncoderInterface $encoder): Response
    {
        $form = $this->createForm(editPhotoType::class);

        $form->handleRequest($request);

        // Si le formulaire a été envoyé et il n'y a aucune erreur
        if($form->isSubmitted() && $form->isValid()){

            $avatar = $form->get('avatar')->getData();

            // Récupération de l'emplacement du dossier des photos de profils
            $profilAvatarDirectory = $this->getParameter('users_uploaded_avatar_directory');

            // Récupération de l'utilisateur connecté
            $connectedUser = $this->getUser();

            // Si l'utilistateur à déjà un avatar, l'ancienne est supprimé
            if($connectedUser->getAvatar() != null){
                unlink( $profilAvatarDirectory . $connectedUser->getAvatar() );
            }

            //dump($avatar);

            do{

                $newFileName = md5( $connectedUser->getId() . random_bytes(100) ) . '.' . $avatar->guessExtension();

            //dump($newFileName);

            } while( file_exists( $profilAvatarDirectory . $newFileName ) );

            // Mise à jour de l'avatar de l'utilisateur dans la bdd
            $connectedUser->setAvatar($newFileName);

            $em = $this->getDoctrine()->getManager();

            $em->flush();

            $avatar->move(
                $profilAvatarDirectory,
                $newFileName
            );

            // TODO :Message de succès

            return $this->redirectToRoute('main_profil');
        }


        //Modification du mot de passe
        $formPass = $this->createForm(editPasswordType::class);

        $formPass->handleRequest($request);

        // Si le formulaire a été envoyé et il n'y a aucune erreur
        if($formPass->isSubmitted() && $formPass->isValid()){

            $connectedUser = $this->getUser();

            dump($formPass->get('pass')->getData());

            // Si les deux champs sont correct+
            if($formPass->get('pass')->getData() == $formPass->get('confirm-pass')->getData()){

                $hashOfNewPassword = $encoder->encodePassword($connectedUser, $formPass->get('pass')->getData());

                $connectedUser->setPassword($hashOfNewPassword);

                $em = $this->getDoctrine()->getManager();

                $em->flush();


                return $this->redirectToRoute('logout');

                // TODO :Message de success mdp modifié avec succès !


            } else {

                // TODO :Message d'error les mdp ne sont pas identiques !

            }
        }


        //Modification de l'adresse mail
        $formEmail = $this->createForm(editEmailType::class);

        $formEmail->handleRequest($request);

        // Si le formulaire a été envoyé et il n'y a aucune erreur
        if($formEmail->isSubmitted() && $formEmail->isValid()){

            dump($formEmail->get('mail')->getData());

            if($formEmail->get('mail')->getData() == $formEmail->get('confirm-mail')->getData()){

                $em = $this->getDoctrine()->getManager();

                $connectedUser = $this->getUser();

                $connectedUser->setEmail($formEmail->get('mail')->getData());

                //$connectedUser->setIsVerified()

                $em->flush();


                return $this->redirectToRoute('logout');

                // TODO :Message de success email modifié avec succès !


            } else {

                // TODO :Message d'error emails ne sont pas identiques !

            }
        }

        //Modification de la description
        $formDesc = $this->createForm(editDescriptionType::class);

        $formDesc->handleRequest($request);

        // Si le formulaire a été envoyé et il n'y a aucune erreur
        if($formDesc->isSubmitted() && $formDesc->isValid()){

            dump($formDesc->get('description')->getData());

            if($formDesc->get('description')->getData()){

                $em = $this->getDoctrine()->getManager();

                $connectedUser = $this->getUser();

                $connectedUser->setDescription($formDesc->get('description')->getData());

                $em->flush();

                return $this->redirectToRoute('main_profil');

                // TODO :Message de success email modifié avec succès !

            }
        }


        return $this->render('main/editProfil.html.twig', [
            'form' => $form->CreateView(),
            'formPass' => $formPass->CreateView(),
            'formEmail' => $formEmail->CreateView(),
            'formDesc' => $formDesc->CreateView(),
        ]);
    }


        /**
     * Page admin permettant de supprimer un la description
     *
     * @Route("/description/suppression/", name="description_delete")
     * @Security("is_granted('ROLE_USER')")
     */
    public function descriptionDelete(Request $request): Response
    {

            // Suppression de la description
            $em = $this->getDoctrine()->getManager();

            $connectedUser = $this->getUser();

            $connectedUser->setDescription(null);

            $em->flush();

            // $this->addFlash('success', 'La description a été supprimé avec succès !');

        return $this->redirectToRoute('main_profil');
    }
}
