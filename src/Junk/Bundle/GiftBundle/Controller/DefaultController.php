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
            $event->setStartDate(\DateTime::createFromFormat("d-m-Y H:i",$event->getStartDate()));
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

      $currentUser = $this->getUser();
      $sharedUrl = $this->generateUrl('event_shared_url', array('shared_token' => $event->getSharedToken()));
      $message = 'Votre ami '.$currentUser->getFirstName().' '.$currentUser->getLastName().' vous invite à le rejoindre sur anonymous-gift.local en cliquant sur le lien suivant : <a href="'.$sharedUrl.'">Lien</a>';

      return $this->render('JunkGiftBundle:Default:success.event.html.twig', array(
          'form' => $form->createView(),
          'event' => $event,
          'sharedUrl' => $sharedUrl,
          'sharedMessage' => $message
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
      $currentUser = $this->getUser();
      $sharedUrl = $this->generateUrl('event_shared_url', array('shared_token' => $event->getSharedToken()));
      $message = 'Votre ami '.$currentUser->getFirstName().' '.$currentUser->getLastName().' vous invite à le rejoindre sur anonymous-gift.local en cliquant sur le lien suivant : <a href="'.$sharedUrl.'">Lien</a>';

      return $this->render('JunkGiftBundle:Default:invite.event.html.twig', array(
          'form' => $form->createView(),
          'event' => $event,
          'sharedUrl' => $sharedUrl,
          'sharedMessage' => $message
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
          $userEvent = $query->getOneOrNullResult();
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
      $userEvents = $this->getUserEvents($event);
      return $this->render('JunkGiftBundle:Default:show.event.html.twig', array(
          'event' => $event,
          'userEvents' => $userEvents
      ));
    }

    /**
    * @Route("/app/repartirEvent/{id}",name="junk_gift_bundle_repartir_event")
    */
    public function repartirEventAction(Event $event){
      if(!$event->getIsDistributed()){
        $this->repartirGift($event);
      }
      return $this->redirectToRoute('junk_gift_bundle_event',array(
        'id' => $event->getId()
      ));
    }

    /**
    * Retrieve user events
    */
    function getUserEvents($event){
      $em = $this->getDoctrine()->getManager();
      $query = $em->createQuery(
          'SELECT u, e
          FROM JunkGiftBundle:UserEvent u
          JOIN u.event e
          WHERE e.id = :id
          ORDER BY e.startdate ASC'
      );
      $query->setParameter('id',$event->getId());
      return $query->getResult();
    }

    function repartirGift($event){
      $em = $this->getDoctrine()->getManager();
      $userEvents = $this->getUserEvents($event);
      if(count($userEvents) <= 1){
        return;
      }
      shuffle($userEvents);
      $users = array();
      foreach ($userEvents as $userEvent){
        $users[] = $userEvent->getUser();
      }
      $nbUsers = count($users);
      for($i = 0; $i < $nbUsers; $i++){
        if( $i === $nbUsers - 1 ){
          $userEvents[$i]->setReceivedUser($users[0]);
        }
        else{
          $userEvents[$i]->setReceivedUser($users[$i+1]);
        }
        $em->persist($userEvents[$i]);
      }
      $event->setIsDistributed(true);
      // Persist
      $em->persist($event);
      $em->flush();
    }
}
