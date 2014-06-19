<?php
/**
 * Description of AvisoType
 *
 * @author Héctor Martínez / +58-416-9052533
 */
namespace Pmc\IntranetBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Pmc\IntranetBundle\Form\DestinatarioType;

class AvisoType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {       
      $builder
        ->add('titulo', null, array('label'=>'Título/Assunto',
                                 'required'=>true,
                                     'attr'=>array(
                                               'maxlength'=>'100',
                                                 'onclick'=>'$(this).focus();',
                                                   'class'=>'form-control',                                                   
                                             'placeholder'=>'Escreva nome do Serviço',
                                                   'title'=>'Nome do Serviço')))
              
        ->add('conteudo', null, array('label'=>'Conteúdo',
                                 'required'=>false,
                                     'attr'=>array('rows' =>'2',
                                               'maxlength'=>'2000',
                               'onclick'=>'$(this).focus();resizeTextarea($(this).attr("id"));',
                                         'onkeyup'=>'resizeTextarea($(this).attr("id"))',
                                                   'class'=>'form-control',
                                                   'style'=>'resize:vertical;',
                                             'placeholder'=>'Escreva o Conteúdo',
                                                   'title'=>'Escreva o Conteúdo')))
              
        ->add('importante', null, array( 'label'=>'Importante',
                                 'required'=>true,
                                     'attr'=>array('value'=>true)))               
                               
        ->add('destinatarios', 'collection', array(                      
                'error_bubbling' => false,
                'type'           => new DestinatarioType(),
                'label'          => 'Destinatários',
                'by_reference'   => false,
                'allow_add'      => true,
                'attr'           => array(
                    'class' => '' ) ))
            ;
    }
   
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
      $resolver->setDefaults(array(
                     'data_class' => 'Pmc\IntranetBundle\Entity\Aviso',                     
             'cascade_validation' => true,
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
