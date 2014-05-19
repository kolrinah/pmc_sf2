<?php
/**
 * Description of FiltrosPublicacoesType
 *
 * @author Héctor Martínez / +58-416-9052533
 */
namespace Pmc\IntranetBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Doctrine\ORM\EntityRepository;

class FiltrosPublicacoesType extends AbstractType
{  
    public function buildForm(FormBuilderInterface $builder, array $options)
    {       
      $builder
        ->add('patron', 'text', array('label'=>'Padrão',            
                                 'required'=>false,
                                     'attr'=>array(
                                               'maxlength'=>'20',
                                                   'class'=>'form-control',                                                   
                                             'placeholder'=>'Escreva seu padrão de pesquisa',
                                                   'title'=>'Padrão de pesquisa')))
              
        ->add('secretarias', 'entity', array('label'=>'Filtrar por Secretaria:',
                                   'class' => 'PmcIntranetBundle:Secretaria',
                                'property' => 'nome',
                           'query_builder' => function(EntityRepository $er) {
                                                return $er->createQueryBuilder('s')
                                                ->orderBy('s.nome', 'ASC');},
                                 'required'=>false,
                                 'multiple'=>true,
                                 'expanded'=>true,))
                                                        
        ->add('puntero', 'hidden', array('attr'=>array('value'=>'0')) )
            
         ;
    }
   
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
      $resolver->setDefaults(array(
                /*'data_class' => 'Pmc\IntranetBundle\Entity\Secretaria',*/
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
