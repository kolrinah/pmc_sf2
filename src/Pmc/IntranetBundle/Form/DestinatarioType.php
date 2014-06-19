<?php
/**
 * Description of DatinatarioType
 *
 * @author Héctor Martínez / +58-416-9052533
 */
namespace Pmc\IntranetBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class DestinatarioType extends AbstractType
{  
    public function buildForm(FormBuilderInterface $builder, array $options)
    {       
      $builder
              
        ->add('usuario', 'entity', array(//'label'=>'Selecione os Destinatários',
                                 'class' => 'PmcIntranetBundle:Usuario',
                               'multiple'=> false,
                               'expanded'=> false,
                                  'attr' => array('class'=>'',    
                                 'style' => 'border:none; background:none; max-width:80%' )))              
            ;
    }
   
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
      $resolver->setDefaults(array(
                     'data_class' => 'Pmc\IntranetBundle\Entity\AvisoDestinatario',
                'csrf_protection' => true,
                'csrf_field_name' => '_token',
                // una clave única para ayudar generar la ficha secreta
                'intention'       => 'task_item',
      ));
    }
   
    public function getName()
    {
      return 'destino';
    }
}
?>
