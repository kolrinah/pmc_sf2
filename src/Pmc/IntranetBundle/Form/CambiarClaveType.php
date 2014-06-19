<?php
/**
 * Description of CambiarClaveType
 *
 * @author Héctor Martínez / +58-416-9052533
 */
namespace Pmc\IntranetBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotNull;

class CambiarClaveType extends AbstractType
{    
   public function buildForm(FormBuilderInterface $builder, array $options)
   {       
      $builder
              ->add('email','email', array('attr'=>array('readonly'=>'readonly',                  
                                                            'class'=>'form-control', 
                                                        'maxlength'=>150,
                                                            'title'=>'Email')))  
      
              ->add('senha', 'repeated', array('type' => 'password',                                        
                                    'invalid_message' => ' As senhas não conferem ',                    
                                           'required' => true,
               'first_options' => array('label' => 'Defina sua senha:',
                                        'attr'=>array('placeholder'=>'Introduza sua senha',
                                                            'title'=>'Introduza sua senha',
                                                           'class' => 'form-control')),
              'second_options' => array('label' => 'Repita sua senha',
                                          'attr'=>array('placeholder'=>'Repita sua senha',
                                                            'title'=>'Repita sua senha',
                                                           'class' => 'form-control')),                  
                 'constraints' => array(
                                   new NotNull(array('message'=>'Campo Requerido')),
                                   new Length(array('min' => 6,
                                                    'max' => 50,
                                   'minMessage' => ' Deve superar 6 caracteres ',
                                   'maxMessage' => ' não pode exceder 50 caracteres '))),
                   
                  ));
   }
   
   public function setDefaultOptions(OptionsResolverInterface $resolver)
   {
      $resolver->setDefaults(array(               
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
