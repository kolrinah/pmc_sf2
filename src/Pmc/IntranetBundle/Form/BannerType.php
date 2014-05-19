<?php
/**
 * Description of BannerType
 *
 * @author Héctor Martínez / +58-416-9052533
 */
namespace Pmc\IntranetBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class BannerType extends AbstractType
{  
    public function buildForm(FormBuilderInterface $builder, array $options)
    {       
      $builder
        ->add('nome', null, array('label'=>'Nome',
                                 'required'=>true,
                                     'attr'=>array(
                                               'maxlength'=>'30',
                                                 'onclick'=>'$(this).focus();',
                                                   'class'=>'form-control',                                                   
                                             'placeholder'=>'Escreva nome do Banner',
                                                   'title'=>'Nome do Banner')))
              
        ->add('url', 'url', array('label'=>'URL',
                               'required'=>false,
                                   'attr'=>array(
                                               'maxlength'=>'100',
                                                 'onclick'=>'$(this).focus();',
                                                   'class'=>'form-control',                                                   
                                             'placeholder'=>'URL',
                                                   'title'=>'URL Link')))
              
        ->add('imagem','file', array('label'=>'Imagem',
                                  'required'=>false,
                               'data_class' => null))
            ;
    }
   
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
      $resolver->setDefaults(array(
                'data_class' => 'Pmc\IntranetBundle\Entity\Banner',
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
