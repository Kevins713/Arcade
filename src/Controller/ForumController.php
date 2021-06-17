<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use App\Entity\SubCategory;
use App\Form\SubCategoryType;
use Symfony\Component\HttpFoundation\Request;
use \DateTime;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class ForumController extends AbstractController
{
    /**
     * @Route("/subcategory", name="subcategory")
     */
    public function subcategory(Request $request, PaginatorInterface $paginator)
    {
        $requestedPage = $request->query->getInt('page', 1);
        if($requestedPage < 1){
            throw new NotFoundHttpException();
        }

        $em = $this->getDoctrine()->getManager();
        $query = $em->createQuery('SELECT a FROM App\Entity\SubCategory a');
        $pageForum = $paginator->paginate(
            $query,     // Requête de selection des sous categories en BDD
            $requestedPage,     // Numéro de la page dont on veux les sous categories
            10      // Nombre de sous categorie par page
        );

        return $this->render('category/subCategory.html.twig',[
        ]);
    }

        /**
     * Contrôleur de la page permettant de créer une nouvelle sous categorie
     *
     * @Route("/nouvelle-subcategory/", name="new_subcategory")
     * @Security("is_granted('ROLE_ADMIN','ROLE_MODERATOR)")
     */
    public function newSubCategory(Request $request): Response
    {

        $newSubCategory = new SubCategory();
        $form = $this->createForm(SubCategoryType::class, $newSubCategory);
        $form->handleRequest($request);

        if($form->isSubmitted()&& $form->isValid()){

            $connectedUser = $this->getUser();

            $newSubCategory
                ->setPublicationDate(new DateTime())
                ->setAuthor($connectedUser)
            ;

            // récupération du manager des entités et sauvegarde en BDD de $newSubCategory
            $em = $this->getDoctrine()->getManager();

            $em->persist($newSubCategory);

            $em->flush();

            $this->addFlash('success', 'Sous categorie créé avec succès !');
            return $this->redirectToRoute('subcategory',[
                'slug' => $newSubCategory->getSlug()
            ]);

            // Si le formulaire a été envoyé, on dump notre SubCategory, qui est pré-rempli automatiquement avec les données provenant du formulaire !
            dump($newSubCategory);
        }

        return $this->render('category/newSubCategory.html.twig',[
            'form' => $form->createView()
        ]);
    }
}
