<?php

namespace Junk\Bundle\GiftBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;

use Junk\Bundle\GiftBundle\Entity\User;
use Junk\Bundle\GiftBundle\Entity\Event;
use Junk\Bundle\GiftBundle\Form\EventType;

class DefaultController extends Controller
{
    /**
     * @Route("/", name="junk_gift_bundle_home")
     */
    public function indexAction()
    {
        return $this->render('JunkGiftBundle:Default:index.html.twig');
    }

    /**
    * @Route("/app", name="junk_gift_bundle_app")
    */
    public function appAction(){
      $repository = $this->getDoctrine()->getRepository('JunkGiftBundle:Event');
      $em = $this->getDoctrine()->getManager();
      $query = $em->createQuery(
          'SELECT e
          FROM JunkGiftBundle:Event e
          WHERE e.startdate > CURRENT_DATE()
          ORDER BY e.startdate ASC'
      );

      $events = $query->getResult();

      return $this->render('JunkGiftBundle:Default:list.html.twig',array(
        "events" => $events
      ));
    }

    /**
    * @Route("/app/createEvent", name="junk_gift_bundle_create_event")
    */
    public function createEventAction()
    {
        $event = new Event();

        $form = $this->createForm('event_type', $event);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $event = $form->getData();
            $em = $this->getDoctrine()->getManager();
            $em->persist($event);
            $em->flush();

            return $this->redirectToRoute('junk_gift_bundle_app');
        }

        return $this->render('default/create.event.html.twig', array(
            'form' => $form->createView(),
        ));
    }
}
