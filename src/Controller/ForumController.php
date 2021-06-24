<?php

namespace App\Controller;


use App\Controller\MainController;
use App\Form\CategoryEditType;
use App\Form\ForumFormType;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\HttpFoundation\File\File;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use App\Repository\CategoryRepository;
use App\Repository\SubCategoryRepository;
use App\Repository\ForumRepository;

use App\Form\CreateCommentFormType;
use App\Form\SubCategoryFormType;
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
 *
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


            do {

                $newFileName = md5($connectedUser->getId() . random_bytes(100)) . '.' . $image->guessExtension();


            } while (file_exists($imageDirectory . $newFileName));

            dump($newFileName);

            // Mise à jour de l'image de la catégorie dans la BDD
            $newCategory->setImage($newFileName);
            $em->persist($newCategory);
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
     * @Route("categorie/{slug}/", name="category")
     */
    public function category(SubCategoryRepository $subCategory ,Category $category, Request $request): Response
    {

        return $this->render('forum/category/category.html.twig',[
            'categorie' => $category,
            'subcategories' => $subCategory->findAll(),
        ]);

    }


    /**
     * Contrôleur de la page permettant de créer une nouvelle sous categorie
     *
     * @Route("/nouvelle-souscategorie/{slug}", name="new_subcategory")
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
            return $this->redirectToRoute('category',[
                'slug'=> $category->getSlug()
            ]);
        }

        return $this->render('forum/category/newSubCategory.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/forumlist/{slug}", name="forumlist")
     */
    public function forumList(Request $request, SubCategory $subCategory): Response
    {

        return $this->render('forum/forumList.html.twig',[
            'subcategory' => $subCategory,
        ]);

    }


    /**
     * Contrôleur de la page permettant de créer un nouveau forum
     *
     * @Route("/nouveau-forum/{slug}", name="new_forum")
     * @Security("is_granted('ROLE_ADMIN','ROLE_MODERATOR')")
     */
    public function newForum(Request $request, SubCategory $subCategory): Response
    {
        $newForum = new Forum();
        $form = $this->createForm(ForumFormType::class, $newForum);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $connectedUser = $this->getUser();
            $newForum
            ->setSubCategory($subCategory)
            ->setAuthor($connectedUser)
            ->setPublicationDate(new DateTime());

            $em = $this->getDoctrine()->getManager();
            $em->persist($newForum);
            $em->flush();

            $this->addFlash('success', 'Forum créée avec succès !');
            return $this->redirectToRoute('forumlist',[
                'slug'=> $subCategory->getSlug()
            ]);
        }

        return $this->render('forum/newForum.html.twig', [
            'form' => $form->createView(),
            'forumlist' => $newForum,
        ]);
    }

    /**
     * @Route("/forum/{slug}", name="forum")
     */
    public function forum(Forum $forum, Request $request, PaginatorInterface $paginator): Response
    {
        //todo Utilisation du CommentRepository pour la requete DQL

        //Paginator pour les commentaires de la page forum
        $requestedPage = $request->query->getInt('page', 1);

        if ($requestedPage < 1) {
            throw new NotFoundHttpException();
        }

        $em = $this->getDoctrine()->getManager();

        $query = $em->createQuery('SELECT c FROM App\Entity\Comment c ORDER BY c.publicationDate DESC');

        $comments = $paginator->paginate(
            $forum->getComments(),             // Requête de selection
            $requestedPage,     // Numéro de la page actuelle
            5              // Nombre d'articles par page
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
            $this->addFlash('success', 'Le commentaire a été publié avec succès !');

            // supression des deux variables
            unset($newComment);
            unset($form);

            $newComment = new Comment();
            $form = $this->createForm(CreateCommentFormType::class, $newComment);
            // Redirection vers la page de l'article modifié
            return $this->redirectToRoute('forum', [
                'slug' => $forum->getSlug(),
            ]);
        }

        return $this->render('forum/forum.html.twig',[
            'forum'=>$forum,
            'comments'=>$comments,
            'form' =>$form->createView(),
        ]);
    }

    /**
     * Page moderation permettant de supprimer un commentaire
     *
     * @Route("/forum/suppression-commentaire/{id}/", name="comment_delete")
     * @Security("is_granted('ROLE_MODERATOR')")
     */
    public function commentDelete(Comment $comment, Request $request): Response
    {

        // Récupération du token csrf dans l'url
        $tokenCSRF = $request->query->get('csrf_token');

        // Vérification que le token est valide
        if(!$this->isCsrfTokenValid('comment_delete' . $comment->getId(), $tokenCSRF ))
        {
            $this->addFlash('error', 'Token sécurité invalide, veuillez ré-essayer.');
        } else {

            // Suppression du commentaire
            $em = $this->getDoctrine()->getManager();
            $em->remove($comment);
            $em->flush();

            $this->addFlash('success', 'Le commentaire a été supprimé avec succès !');

        }
        return $this->redirectToRoute('forum', [
            'slug' => $comment->getForum()->getSlug(),
        ]);
    }

    /**
     * Page moderation permettant de modifier un commentaire existant
     *
     * @Route("/forum/modifier-commentaire/{id}/", name="comment_edit")
     * @Security("is_granted('ROLE_MODERATOR')")
     */
    public function commentEdit( Comment $comment, Request $request): Response
    {

        // Création du formulaire de modification
        $form = $this->createForm(CreateCommentFormType::class, $comment);

        // Liaison des données POST avec le formulaire
        $form->handleRequest($request);

        // Si le formulaire est envoyé et n'a pas d'erreur
        if($form->isSubmitted() && $form->isValid()){

            // Sauvegarde des changements dans la BDD
            $em = $this->getDoctrine()->getManager();
            $em->flush();

            // Message flash de succès
            $this->addFlash('success', 'Sujet modifié avec succès !');

            // Redirection vers la page de l'article modifié
            return $this->redirectToRoute('forum', [
                'slug' => $comment->getForum()->getSlug(),
            ]);

        }

        // Appel de la vue en envoyant le formulaire à afficher
        return $this->render('forum/commentEdit.html.twig', [
            'form' => $form->createView(),
        ]);

    }

    /**
     * @Route("/modifier-categorie/{id}", name="edit_category")
     * @Security("is_granted('ROLE_MODERATOR')")
     */
    public function categoryEdit(Request $request, Category $category): Response
    {
        $form = $this->createForm(CategoryEditType::class, $category);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $em = $this->getDoctrine()->getManager();
            $em->flush();


            // Message flash de succès et redirection de l'utilisateur
            $this->addFlash('success', 'Image de catégorie modifiée avec succès !');
            return $this->redirectToRoute('home');

        }

        return $this->render('forum/category/editCategory.html.twig', [
            'form' => $form->createView(),
            'category' => $category
        ]);
    }


    /**
     * @Route("supprimer-categorie/{id}", name="delete_category", methods={"POST"})
     */
    public function deleteCategory(Request $request, Category $category): Response
    {
        if ($this->isCsrfTokenValid('delete'.$category->getId(), $request->request->get('_token'))) {

            $entityManager = $this->getDoctrine()->getManager();

            // On parcourt toute ses sous-catégories et tout leurs contenus pour les supprimer sinon erreur
            $subCategories = $category->getSubCategories();
            foreach($subCategories as $subCategory){
                $forums = $subCategory->getForums();
                foreach ($forums as $forum){
                    $comments = $forum->getComments();
                    foreach ($comments as $comment){
                        $entityManager->remove($comment);
                    }
                    $entityManager->remove($forum);
                }
                $entityManager->remove($subCategory);
            }

            $entityManager->remove($category);
            $entityManager->flush();

            $this->addFlash('success', 'Catégorie supprimée avec succès !');
        } else {
            $this->addFlash('error', 'Token sécurité invalide, veuillez ré-essayer.');
        }

        return $this->redirectToRoute('home');
    }
}
