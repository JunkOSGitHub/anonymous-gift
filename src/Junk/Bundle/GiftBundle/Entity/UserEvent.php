<?php

namespace Junk\Bundle\GiftBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * UserEvent
 *
 * @ORM\Table(name="user_event")
 * @ORM\Entity(repositoryClass="Junk\Bundle\GiftBundle\Repository\UserEventRepository")
 */
class UserEvent
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
    * @var User
    *
    * @ORM\ManyToOne(targetEntity="User")
    */
    private $user;

    /**
    * @var Event
    *
    * @ORM\ManyToOne(targetEntity="Event")
    */
    private $event;

    /**
    * @var User
    *
    * @ORM\ManyToOne(targetEntity="User")
    */
    private $received_user;


    /**
     * Get id
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    

    /**
     * Set user
     *
     * @param \Junk\Bundle\GiftBundle\Entity\User $user
     *
     * @return UserEvent
     */
    public function setUser(\Junk\Bundle\GiftBundle\Entity\User $user = null)
    {
        $this->user = $user;

        return $this;
    }

    /**
     * Get user
     *
     * @return \Junk\Bundle\GiftBundle\Entity\User
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * Set event
     *
     * @param \Junk\Bundle\GiftBundle\Entity\Event $event
     *
     * @return UserEvent
     */
    public function setEvent(\Junk\Bundle\GiftBundle\Entity\Event $event = null)
    {
        $this->event = $event;

        return $this;
    }

    /**
     * Get event
     *
     * @return \Junk\Bundle\GiftBundle\Entity\Event
     */
    public function getEvent()
    {
        return $this->event;
    }

    /**
     * Set receivedUser
     *
     * @param \Junk\Bundle\GiftBundle\Entity\User $receivedUser
     *
     * @return UserEvent
     */
    public function setReceivedUser(\Junk\Bundle\GiftBundle\Entity\User $receivedUser = null)
    {
        $this->received_user = $receivedUser;

        return $this;
    }

    /**
     * Get receivedUser
     *
     * @return \Junk\Bundle\GiftBundle\Entity\User
     */
    public function getReceivedUser()
    {
        return $this->received_user;
    }
}
