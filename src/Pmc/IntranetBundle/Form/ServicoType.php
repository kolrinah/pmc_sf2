<?php
/**
 * Description of ServicoType
 *
 * @author Héctor Martínez / +58-416-9052533
 */
namespace Pmc\IntranetBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Doctrine\ORM\EntityRepository;

class ServicoType extends AbstractType
{  
    //protected $usuarios;
    protected $secretarias;
    
    public function __construct($datos) 
    {
        //$this->usuarios = $datos['usuarios'];
        $this->secretarias = $datos['secretarias'];
    }
    
    public function buildForm(FormBuilderInterface $builder, array $options)
    {       
      $builder
        ->add('nome', null, array('label'=>'Nome',
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
              
        ->add('ativo', null, array( 'label'=>'Serviço Ativo',
                                 'required'=>true,
                                     'attr'=>array('value'=>true)))               
              
        ->add('secretaria', 'entity', array('required'=>true,
                                       // 'empty_value' => '[ Selecione a Secretaria ]',
                                               'class'=> 'PmcIntranetBundle:Secretaria',
                                             'choices'=> $this->secretarias,
                                                'attr'=> array('class'=>'form-control'))) 
                                                               
        ->add('responsavel', 'entity', array('label'=>'Selecione os Responsáveis',
                                 'class' => 'PmcIntranetBundle:Usuario',
                         'query_builder' => function(EntityRepository $er) {
                                  return $er->createQueryBuilder('u')
                                            ->where('u.ativo = :ativo')                                            
                                            ->setParameter('ativo', true)
                                            ->orderBy('u.nome', 'ASC');},
                               'required'=> true,
                               'multiple'=> true,
                               'expanded'=> true,
                                  'attr'=> array('class'=>'oculto')))
            ;
    }
   
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
      $resolver->setDefaults(array(
                'data_class' => 'Pmc\IntranetBundle\Entity\Servico',
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
