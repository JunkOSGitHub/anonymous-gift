<?php

namespace Junk\Bundle\GiftBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;

use Junk\Bundle\GiftBundle\Entity\User;
use Junk\Bundle\GiftBundle\Entity\Event;
use Junk\Bundle\GiftBundle\Entity\Invite;
use Junk\Bundle\GiftBundle\Form\EventType;
use Junk\Bundle\GiftBundle\Entity\InviteType;

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

        $form->handleRequest($this->getRequest());

        if ($form->isSubmitted() && $form->isValid()) {
            $event = $form->getData();
            $em = $this->getDoctrine()->getManager();
            $em->persist($event);
            $em->flush();
            return $this->redirectToRoute('junk_gift_bundle_success_event',array(
              'id' => $event->getId()
            ));
        }

        return $this->render('JunkGiftBundle:Default:create.event.html.twig', array(
            'form' => $form->createView(),
        ));
    }

    /**
    * @Route("/app/successEvent/{id}", name="junk_gift_bundle_success_event")
    */
    public function successEventAction(Event $event)
    {
      $invite = new Invite();

      $form = $this->createForm('invite_type', $invite);

      $form->handleRequest($this->getRequest());

      if ($form->isSubmitted() && $form->isValid()) {
          $invite = $form->getData();
          $em = $this->getDoctrine()->getManager();
          $em->persist($invite);
          $em->flush();

          return $this->redirectToRoute('junk_gift_bundle_app',array(
            'event' => $event
          ));
      }
      return $this->render('JunkGiftBundle:Default:success.event.html.twig', array(
          'form' => $form->createView(),
          'event' => $event
      ));
    }

    /**
    * @Route("/app/inviteEvent",name="junk_gift_bundle_invite_event")
    */
    public function inviteEventAction(){

    }

    /**
    * @Route("/app/share/{shared_token}", name="event_shared_url")
    */
    public function shareEventAction(){

    }

    /**
    * @Route("/app/inviteEvent",name="junk_gift_bundle_repartir_event")
    */
    public function RepartirEventAction(){

    }
}
