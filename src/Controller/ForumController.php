<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

use Symfony\Component\HttpFoundation\Request;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use App\Entity\SubCategory;
use App\Entity\Category;
use App\Entity\Forum;
use App\Entity\Comment;

use \DateTime;

/**
 * @Route("/category", name="category")
 */
class ForumController extends AbstractController
{
    /**
     * @Route("/", name="category")
     */
    public function category(): Response
    {
        return $this->render('forum/category.html.twig'
        );
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
