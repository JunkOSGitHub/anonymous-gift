<?php
namespace Junk\Bundle\GiftBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormEvent;

class InviteType extends AbstractType
{

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('email', 'text', array(
                 'label' => 'Adresse email',
                 'data' => 'junkbutawesome.os@gmail.com'
            ))
            ->add('message', 'text', array(
                 'label' => 'Invitation',
                 'data' => 'Votre ami {user.firstname} {user.lastname}, vous invite à le rejoindre sur anonymous-gift.local en cliquant sur le lien suivant : {path(‘event_shared_url’, {“shared_token” : shared_token}, true)}'
            ))
            ->add('save', SubmitType::class, array(
                 'label' => 'Inviter'
            ))
        ;
    }

    public function getBlockPrefix()
    {
        return 'invite_type';
    }

    // For Symfony 2.x
    public function getName()
    {
        return $this->getBlockPrefix();
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Junk\Bundle\GiftBundle\Entity\Invite',
        ));
    }
}
