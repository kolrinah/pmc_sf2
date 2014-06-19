<?php
/**
 * Description of LeiDecretoType
 *
 * @author Héctor Martínez / +58-416-9052533
 */
namespace Pmc\IntranetBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class LeiDecretoType extends AbstractType
{  
    public function buildForm(FormBuilderInterface $builder, array $options)
    {      
      $ahora = getdate(time());
      $ano = $ahora['year']; 
      
      $builder
        ->add('nome', null, array('label'=>'Nome',
                                 'required'=>true,
                                     'attr'=>array(
                                               'maxlength'=>'50',
                                                 'onclick'=>'$(this).focus();',
                                                   'class'=>'form-control',                                                   
                                             'placeholder'=>'Escreva nome',
                                                   'title'=>'Nome')))
              
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
              
        ->add('arquivo', 'file', array('label'=>'PDF',
                                   'required'=>false,
                                'data_class' => null))   
              
        ->add('lei', 'choice', array('label' => 'Tipo:',
                                            'required' => true,
                                             'choices' => array('1' => 'Lei',
                                                                '0' => 'Decreto'),
                                            'attr'=>array('class'=>'form-control',) ))
              
        ->add('ano', null, array('required'=>true,
                                     'attr'=>array(  'min'=> ($ano-15),
                                                     'max'=> $ano,
                                                 'onclick'=>'$(this).focus();',                                    
                                               'maxlength'=>'4',
                                                   'class'=>'form-control',                                                   
                                             'placeholder'=>'Ano',
                                                   'title'=>'Escreva o Ano')))
            ;
    }
   
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
      $resolver->setDefaults(array(
                'data_class' => 'Pmc\IntranetBundle\Entity\LeiDecreto',
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
