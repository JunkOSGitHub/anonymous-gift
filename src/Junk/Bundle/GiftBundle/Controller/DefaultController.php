<?php

namespace Junk\Bundle\GiftBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;

use Junk\Bundle\GiftBundle\Entity\User;
use Junk\Bundle\GiftBundle\Entity\Event;
use Junk\Bundle\GiftBundle\Entity\UserEvent;
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
          AND e.owner = :owner
          ORDER BY e.startdate ASC'
      );
      $query->setParameter('owner',$this->getUser());

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
            $userEvent = new UserEvent();
            $userEvent->setUser($this->getUser());
            $userEvent->setEvent($event);
            $em = $this->getDoctrine()->getManager();
            $em->persist($event);
            $em->persist($userEvent);
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

          return $this->redirectToRoute('junk_gift_bundle_app');
      }
      return $this->render('JunkGiftBundle:Default:success.event.html.twig', array(
          'form' => $form->createView(),
          'event' => $event
      ));
    }

    /**
    * @Route("/app/inviteEvent/{id}",name="junk_gift_bundle_invite_event")
    */
    public function inviteEventAction(Event $event){
      $invite = new Invite();

      $form = $this->createForm('invite_type', $invite);

      $form->handleRequest($this->getRequest());

      if ($form->isSubmitted() && $form->isValid()) {
          $invite = $form->getData();
          $em = $this->getDoctrine()->getManager();
          $em->persist($invite);
          $em->flush();

          return $this->redirectToRoute('junk_gift_bundle_app');
      }
      return $this->render('JunkGiftBundle:Default:invite.event.html.twig', array(
          'form' => $form->createView(),
          'event' => $event
      ));
    }

    /**
    * @Route("/app/share/{shared_token}", name="event_shared_url")
    */
    public function shareEventAction($shared_token){
        $eventRep = $this->getDoctrine()->getRepository('JunkGiftBundle:Event');
        $event = $eventRep->findOneBy(array('sharedToken' => $shared_token));
        if($event != null){
          $currentUser = $this->getUser();
          $userEventRep = $this->getDoctrine()->getRepository('JunkGiftBundle:UserEvent');
          $em = $this->getDoctrine()->getManager();
          $query = $em->createQuery(
              'SELECT e
              FROM JunkGiftBundle:UserEvent e
              WHERE e.user = :user_id
              AND e.event = :event_id
              '
            )
            ->setParameter('user_id', $currentUser->getId())
            ->setParameter('event_id', $event->getId());
          $userEvent = $query->getSingleResult();
          if($userEvent == null){
            // Create new UserEvent
            $userEvent = new UserEvent();
            $userEvent->setUser($currentUser);
            $userEvent->setEvent($event);
            // Persist
            $em = $this->getDoctrine()->getManager();
            $em->persist($userEvent);
            $em->flush();
          }
          // Show event
          return $this->redirectToRoute('junk_gift_bundle_event',array(
            'id' => $event->getId()
          ));
        }
        else{
          // Redirect Home
          return $this->redirectToRoute('junk_gift_bundle_app');
        }
    }

    /**
    * @Route("/app/event/{id}", name="junk_gift_bundle_event")
    */
    public function showEventAction(Event $event){
      $em = $this->getDoctrine()->getManager();
      $query = $em->createQuery(
          'SELECT u, e
          FROM JunkGiftBundle:UserEvent u
          JOIN u.event e
          WHERE e.id = :id
          ORDER BY e.startdate ASC'
      );

      $query->setParameter('id',$event->getId());
      $userEvents = $query->getResult();

      return $this->render('JunkGiftBundle:Default:show.event.html.twig', array(
          'event' => $event,
          'userEvents' => $userEvents
      ));
    }

    /**
    * @Route("/app/repartirEvent/{id}",name="junk_gift_bundle_repartir_event")
    */
    public function RepartirEventAction(Event $event){

    }
}
