<?php



namespace App\Controller;

use App\Entity\Event;
use App\Form\EventType;
use App\Repository\CategoryRepository;
use App\Repository\EventRepository;
use App\Repository\UserRepository;
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
    public function index(CategoryRepository $categories, UserRepository $userRepo): Response
    {
        // Requête du flux RSS des actualités
        $rss = simplexml_load_file('https://www.jeuxactu.com/rss/multi.rss');
        $userRepo->findConnectedAdmins();

        // Récupération des 2 derniers Event
        $eventRepo = $this->getDoctrine()->getRepository(Event::class);
        $events = $eventRepo->findBy([], ['publicationDate' => 'DESC'], 2);


        return $this->render('main/index.html.twig', [
            'categories' => $categories->findAll(),
            'rss' => $rss->channel->item,
            'events' => $events,
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

    /**
     * @Route("/creer-une-annonce", name="create_event")
     * @Security("is_granted('ROLE_MODERATOR')")
     */
    public function createEvent(Request $request): Response
    {
        $event = new Event();
        $form = $this->createForm(EventType::class, $event);
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()){

            $event->setPublicationDate(new \DateTime());

            $em = $this->getDoctrine()->getManager();
            $em->persist($event);
            $em->flush();

            return $this->redirectToRoute('home');
        }

        return $this->render('main/newEvent.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/annonces/", name="view_event")
     * @Security("is_granted('ROLE_MODERATOR')")
     */
    public function viewEvent(EventRepository $eventRepo): Response
    {
        return $this->render('main/viewEvent.html.twig', [
           'events' => $eventRepo->findBy([], ['publicationDate' => 'DESC'])
        ]);
    }

    /**
     * @Route("modifier-annonce/{id}", name="edit_event")
     * @Security("is_granted('ROLE_MODERATOR')")
     */
    public function editEvent(Request $request, Event $event): Response
    {
        $form = $this->createForm(EventType::class, $event);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('view_event');
        }

        return $this->render('main/editEvent.html.twig', [
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("supprimer-l-annonce/{id}", name="delete_event", methods={"POST"})
     */
    public function deleteEvent(Request $request, Event $event): Response
    {
        if ($this->isCsrfTokenValid('delete'.$event->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($event);
            $entityManager->flush();
        }

        return $this->redirectToRoute('view_event');
    }


}
