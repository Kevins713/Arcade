<?php

namespace App\Controller;

use App\Entity\Category;
use App\Repository\CategoryRepository;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Form\CategoryFormType;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use App\Entity\SubCategory;
use App\Entity\Forum;
use App\Entity\Comment;
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

                dump($newFileName);

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
     * @Route("/{slug}/", name="category_")
     */
    public function category(CategoryRepository $category, Request $request): Response
    {

        return $this->render('main/index.html.twig', [
            'categories' => $category->findAll(),
        ]);
    }

    /**
     * @Route("/sub-category/", name="sub_category")
     */
    public function sub_category(): Response
    {
        return $this->render('forum/subCategory.html.twig'
        );
    }

    /**
     * @Route("/sub-category/forum/", name="forum")
     */
    public function forum(): Response
    {
        return $this->render('forum/forum.html.twig');

    }

}
