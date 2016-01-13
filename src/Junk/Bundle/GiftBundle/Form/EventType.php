<?php
namespace Junk\Bundle\GiftBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormEvent;

class EventType extends AbstractType
{
    private $context;

    public function setSecurityContext($context){
      $this->context = $context;
      var_dump($context);
      die();
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name')
            ->add('startdate', DateTimeType::class)
            ->add('save', SubmitType::class)
        ;


        $context = $this->context;
        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) use ($context){
            $data = $event->getData();
            $form = $event->getForm();
            $context->getToken()->getUser();
        });
    }

    public function getBlockPrefix()
    {
        return 'event_type';
    }

    // For Symfony 2.x
    public function getName()
    {
        return $this->getBlockPrefix();
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Junk\Bundle\GiftBundle\Entity\Event',
        ));
    }
}
