<?php
/**
 * Description of UsuarioType
 *
 * @author Héctor Martínez / +58-416-9052533
 */
namespace Pmc\IntranetBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Doctrine\ORM\EntityRepository;

class UsuarioType extends AbstractType
{      
    protected $roles;
    
    public function __construct($roles) 
    {
        $this->roles=$roles;
    }
    
    public function buildForm(FormBuilderInterface $builder, array $options)
    { 
      $builder
        ->add('email', 'email', array('required'=>true,
                          'attr'=>array('readonly' => 'readonly', // HABILITAR POR Javascript
                                         'tabindex'=>0,
                                        'autofocus'=>'autofocus',                              
                                          'onclick'=>'$(this).focus()',
                                        'maxlength'=>'100',
                                            'class'=>'form-control',                                                   
                                      'placeholder'=>'Introduza Email',
                                            'title'=>'Introduza Email')))
              
        ->add('nome', null, array('required'=>true,
                           'attr'=>array('onclick'=>'$(this).focus()',
                                        'maxlength'=>'100',
                                            'class'=>'form-control',                                                   
                                      'placeholder'=>'Escreva o Nome',
                                            'title'=>'Escreva o Nome')))              
              
        ->add('cargo', null, array('required'=>false,
                          'attr'=>array(  'onclick'=>'$(this).focus()',
                                        'maxlength'=>'50',
                                            'class'=>'form-control',                                                   
                                      'placeholder'=>'Introduza posição',
                                            'title'=>'Introduza posição')))

        ->add('telefone', null, array('required'=>false,
                          'attr'=>array(  'onclick'=>'$(this).focus()',
                                        'maxlength'=>'20',
                                            'class'=>'form-control',                                                   
                                      'placeholder'=>'Introduza Telefone',
                                            'title'=>'Introduza Telefone')))
              
        ->add('foto', 'file', array('required'=> false,
                                 'data_class' => null))  
              
        ->add('secretaria', 'entity', array('required'=>true,
                                        'empty_value' => '[ Selecione a Secretaria ]',
                                            'class'=>'PmcIntranetBundle:Secretaria',
                                   'query_builder' => function(EntityRepository $er) {
                                             return $er->createQueryBuilder('s')
                                                       ->orderBy('s.nome', 'ASC');},
                                      'attr'=>array('class'=>'form-control'))) 
              
        ->add('role', 'entity', array('label'=>'Selecione o nível de usuário',
                                 'class' => 'PmcIntranetBundle:Role',
                                'choices'=> $this->roles,
                               'required'=>true,
                               'multiple'=>true,
                               'expanded'=>true))  
                                                               
        ->add('matricula', null, array(    'label'=>'Matrícula',
                                        'required'=>false,
                          'attr'=>array(  'onclick'=>'$(this).focus()',
                                        'maxlength'=>'20',
                                            'class'=>'form-control',                                                   
                                      'placeholder'=>'Introduza Matrícula',
                                            'title'=>'Introduza Matrícula')))
              
        ->add('rg', null, array(    'label'=>'RG',
                                        'required'=>false,
                          'attr'=>array(  'onclick'=>'$(this).focus()',
                                        'maxlength'=>'20',
                                            'class'=>'form-control',                                                   
                                      'placeholder'=>'Introduza Registro Geral',
                                            'title'=>'Introduza Registro Geral')))

        ->add('cpf', null, array(    'label'=>'CPF',
                                        'required'=>false,
                          'attr'=>array(  'onclick'=>'$(this).focus()',
                                        'maxlength'=>'20',
                                            'class'=>'form-control',                                                   
                                      'placeholder'=>'Introduza Cadastro de pessoa física',
                                            'title'=>'Introduza Cadastro de pessoa física')))        
        
        ->add('ativo', null, array('label'=>'Usuário Ativo')) 
                                                               
        ->add('banido', null, array('label'=>'Usuário Banido'))                                                                
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
