<?php

namespace Pmc\IntranetBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class LoginUsuarioType extends AbstractType
{     
   protected $data;
    
   public function __construct($data) 
   {
        $this->data = $data;
   }
    
   public function buildForm(FormBuilderInterface $builder, array $options)
   {               
      $builder
        ->add('_username', 'email', array('attr'=>array('placeholder'=>'Correo Electrónico',
                                                              'class'=>'span4', 
                                                          'maxlength'=>'40', 
                                                           'required'=>'required',
                                                              'value'=>$this->data,
                                                              'title'=>'Correo Electrónico')))
                
        ->add('_password', 'password', array('attr'=>array('placeholder'=>'Senha',
                                                              'class'=>'span4', 
                                                          'maxlength'=>'40', 
                                                           'required'=>'required',
                                                              'title'=>'Senha')))
        ->add('captcha', 'captcha', array('label'=>' ',
                                     'expiration'=> 15,
                               'background_color'=> [255, 255, 255],
                                         'width' => 190,
                                        'height' => 40,
                                        'length' => 6,
                                          'attr' => array('placeholder'=>'Inserte el código',                                                                
                                                            'maxlength'=>'10', 
                                                             'required'=>'required',
                                                                'title'=>'Código Captcha')))            
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
      return 'login';
   }
}

?>
