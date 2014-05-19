<?php
/**
 * Description of PostarMensagemType
 *
 * @author Héctor Martínez / +58-416-9052533
 */
namespace Pmc\IntranetBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class PostarMensagemType extends AbstractType
{  
    public function buildForm(FormBuilderInterface $builder, array $options)
    {       
      $builder
        ->add('titulo', null, array('label'=>'Mensagem',            
                                 'required'=>true,
                                     'attr'=>array('rows' =>'3',
                                               'maxlength'=>'2000',
                                                   'class'=>'form-control',
                                                   'style'=>'resize:vertical;',
                                             'placeholder'=>'Escreva sua Mensagem',
                                                   'title'=>'Escreva sua Mensagem')))
              
        ->add('video', null, array('label'=>'Vídeo',            
                                 'required'=>false,
                                     'attr'=>array('rows' =>'3',
                                               'maxlength'=>'250',
                                                   'class'=>'form-control',
                                                   'style'=>'resize:vertical',
                                             'placeholder'=>'Código ou url link de Vídeo (Vimeo ou Youtube)',
                                                   'title'=>'Código ou url link de Vídeo (Vimeo ou Youtube)')))               
              
        ->add('imagem','file', array('label'=>'Imagem',
                                  'required'=>false,
                               'data_class' => null))          
      ;
    }
   
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
      $resolver->setDefaults(array(
                'data_class' => 'Pmc\IntranetBundle\Entity\Publicacao',
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
