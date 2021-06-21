<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use App\Entity\SubCategory;
use App\Entity\Category;
use App\Form\CategoryFormType;
use App\Repository\CategoryRepository;
use Symfony\Component\HttpFoundation\Request;
use \DateTime;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Contrôleurs de la page qui liste les categories du site
 *
 * @Route("/category", name="category_")
 */
class ForumController extends AbstractController
{

            /**
     * Contrôleur de la page permettant de créer une nouvelle sous categorie
     *
     * @Route("/nouvelle-category/", name="new_category")
     * @Security("is_granted('ROLE_ADMIN','ROLE_MODERATOR')")
     */
    public function newCategory(Request $request): Response
    {


        $newCategory = new Category();
        $form = $this->createForm(CategoryFormType::class, $newCategory);
        $form->handleRequest($request);

        if($form->isSubmitted()&& $form->isValid()){

            $connectedUser = $this->getUser();

            // récupération du manager des entités et sauvegarde en BDD de $newCategory
            $em = $this->getDoctrine()->getManager();

            $em->persist($newCategory);

            $em->flush();

            $this->addFlash('success', 'Categorie créé avec succès !');
            return $this->redirectToRoute('category_category_',[
                'slug' => $newCategory->getSlug()
            ]);

            // Si le formulaire a été envoyé, on dump notre Category, qui est pré-rempli automatiquement avec les données provenant du formulaire !
            dump($newCategory);
        }

        return $this->render('forum/category/newcategory.html.twig',[
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/{slug}", name="category_")
     */
    public function category(CategoryRepository $categorie, Request $request): Response
    {

        return $this->render('forum/category/category.html.twig',[
            'categories' => $categorie->findAll(),
        ]);
    }
}
