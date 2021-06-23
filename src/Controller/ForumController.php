<?php

namespace App\Controller;

use App\Repository\CommentRepository;
use App\Repository\SubCategoryRepository;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Knp\Component\Pager\PaginatorInterface;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use App\Repository\CategoryRepository;
use App\Repository\ForumRepository;

use App\Form\CreateCommentFormType;
use App\Form\CategoryFormType;

use App\Entity\Category;
use App\Entity\SubCategory;
use App\Entity\Forum;
use App\Entity\Comment;
use App\Entity\User;

use \DateTime;

/**
 * Contrôleurs de la page qui liste les categories du site
 *
 * @Route("/categorie", name="category_")
 */
class ForumController extends AbstractController
{

    /**
     * Contrôleur de la page permettant de créer une nouvelle sous categorie
     *
     * @Route("/nouvelle-categorie/", name="new_category")
     * @Security("is_granted('ROLE_ADMIN','ROLE_MODERATOR')")
     */
    public function newCategory(Request $request): Response
    {


        $newCategory = new Category();
        $form = $this->createForm(CategoryFormType::class, $newCategory);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $image = $form->get('image')->getData();

            $imageDirectory = $this->getParameter('app_category_image_directory');

            $connectedUser = $this->getUser();
            $em = $this->getDoctrine()->getManager();
            $em->persist($newCategory);

            do {

                $newFileName = md5($connectedUser->getId() . random_bytes(100)) . '.' . $image->guessExtension();


            } while (file_exists($imageDirectory . $newFileName));

            // Mise à jour du nom de la photo de profil de l'utilisateur connecté dans la BDD
            $newCategory->setImage($newFileName);
            $em = $this->getDoctrine()->getManager();
            $em->flush();

            $image->move(
                $imageDirectory,
                $newFileName
            );

            $this->addFlash('success', 'Catégorie créée avec succès !');
            return $this->redirectToRoute('home');
        }

        return $this->render('forum/newCategory.html.twig', [
            'form' => $form->createView(),
        ]);
    }




    /**
     * Contrôleur de la page permettant de créer une nouvelle sous categorie
     *
     * @Route("/{slug}/nouvelle-souscategorie/", name="new_subcategory")
     * @Security("is_granted('ROLE_ADMIN','ROLE_MODERATOR')")
     */
    public function newSubCategory(Request $request, Category $category): Response
    {


        $newSubCategory = new SubCategory();
        $form = $this->createForm(SubCategoryFormType::class, $newSubCategory);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $image = $form->get('image')->getData();

            $imageDirectory = $this->getParameter('app_category_image_directory');
            $connectedUser = $this->getUser();

            do {

                $newFileName = md5($connectedUser->getId() . random_bytes(100)) . '.' . $image->guessExtension();

                dump($newFileName);

            } while (file_exists($imageDirectory . $newFileName));

            // Mise à jour du nom de la photo de la sous catégorie
            $newSubCategory->setImage($newFileName);
            $newSubCategory->setCategory($category);

            $em = $this->getDoctrine()->getManager();
            $em->persist($newSubCategory);
            $em->flush();

            $image->move(
                $imageDirectory,
                $newFileName
            );

            $this->addFlash('success', 'Sous-Catégorie créée avec succès !');
            return $this->redirectToRoute('category_sub_category',[
                'slug'=> $category->getSlug()
            ]);
        }

        return $this->render('forum/category/newSubCategory.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * Contrôleur de la page permettant de créer un nouveau forum
     *
     * @Route("/{slug}/nouveau-forum/", name="new_forum")
     * @Security("is_granted('ROLE_ADMIN','ROLE_MODERATOR')")
     */
    public function newForum(Request $request, Forum $forum): Response
    {


        $newForum = new Forum();
        $form = $this->createForm(ForumFormType::class, $newForum);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $image = $form->get('image')->getData();

            $imageDirectory = $this->getParameter('app_category_image_directory');
            $connectedUser = $this->getUser();

            do {

                $newFileName = md5($connectedUser->getId() . random_bytes(100)) . '.' . $image->guessExtension();

                dump($newFileName);

            } while (file_exists($imageDirectory . $newFileName));

            // Mise à jour du nom de la photo de la sous catégorie
            $newForum->setImage($newFileName);
            $newForum->setCategory($forum);

            $em = $this->getDoctrine()->getManager();
            $em->persist($newForum);
            $em->flush();

            $image->move(
                $imageDirectory,
                $newFileName
            );

            $this->addFlash('success', 'Forum créée avec succès !');
            return $this->redirectToRoute('category_forumlist',[
                'slug'=> $category->getSlug()
            ]);
        }

        return $this->render('forum/category/newForum.html.twig', [
            'form' => $form->createView(),
        ]);
    }



    /**
     * @Route("/sous-categorie/{slug}", name="sub_category")
     */
    public function subCategory(SubCategory $subCategory, Request $request): Response
    {

        return $this->render('forum/category/subCategory.html.twig',[
            'subcategories' => $subCategory,
        ]);

    }



    /**
     * @Route("/sous-categorie/forum/{slug}", name="forum")
     */
    public function forum(Forum $forum, Request $request, PaginatorInterface $paginator): Response
    {
        //todo Utilisation du CommentRepository pour la requete DQL

        //Paginator pour les commentaires de la page forum
        $requestedPage = $request->query->getInt('page', 1);

        if($requestedPage < 1){
            throw new NotFoundHttpException();
        }

        $em = $this->getDoctrine()->getManager();

        $query = $em->createQuery('SELECT c FROM App\Entity\Comment c ORDER BY c.publicationDate DESC');

        $comments = $paginator->paginate(
            $query,             // Requête de selection
            $requestedPage,     // Numéro de la page actuelle
            3              // Nombre d'articles par page
        );


        // Si l'utilisateur n'est pas connecté, on appel directement la vue sans traiter le formulaire en dessous
        if(!$this->getUser()){
            return $this->render('forum\forum.html.twig', [
                'forum' => $forum,
                'comments'=>$comments,
            ]);
        }

        // Création d'un nouveau Comment
        $newComment = new Comment();

        // Création d'un nouveau formulaire
        $form = $this->createForm(CreateCommentFormType::class, $newComment);

        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()){

            // Récupération de la personne connectée
            $connectedUser = $this->getUser();

            // Hydratation du comment avec la date et l'auteur
            $newComment
                ->setPublicationDate( new DateTime() )
                ->setAuthor($connectedUser)
                ->setForum($forum)
            ;

            // Récupération du manager général pour sauvegarder l'article en BDD
            $em = $this->getDoctrine()->getManager();

            $em->persist($newComment);

            $em->flush();

            // Message flash de succès
            //todo
            // supression des deux variables
            unset($newComment);
            unset($form);

            $newComment = new Comment();
            $form = $this->createForm(CreateCommentFormType::class, $newComment);
        }



        return $this->render('forum/forum.html.twig',[
            'forum'=>$forum,
            'comments'=>$comments,
            'form' =>$form->createView(),
        ]);
    }
}