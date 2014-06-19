<?php
/**
 * Description of MeuPerfilType
 *
 * @author Héctor Martínez / +58-416-9052533
 */
namespace Pmc\IntranetBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class MeuPerfilType extends AbstractType
{      
    public function buildForm(FormBuilderInterface $builder, array $options)
    { 
      $builder
        ->add('secretaria', null, array('disabled' => true,
                                           'attr' => array('class'=>'form-control')))
              
        ->add('nome', null, array('disabled' => true,
                                      'attr' => array('class'=>'form-control')))
              
        ->add('email', 'email', array('disabled' => true,
                                          'attr' => array('class'=> 'form-control')))
              
        ->add('cargo', null, array('disabled' => true,
                                      'attr' => array('class'=>'form-control')))

        ->add('telefone', null, array('disabled' => true,
                                      'attr' => array('class'=>'form-control')))
              
        ->add('foto', 'file', array('required'=> false,
                                 'data_class' => null))  
      ;
    }
   
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
      $resolver->setDefaults(array(
                'data_class' => 'Pmc\IntranetBundle\Entity\Usuario',
                'csrf_protection' => true,
                'csrf_field_name' => '_token',
                // una clave única para ayudar generar la ficha secreta
                'intention'       => 'task_item',
      ));
    }
   
    public function getName()
    {
      return 'form';
    }
}
?>
