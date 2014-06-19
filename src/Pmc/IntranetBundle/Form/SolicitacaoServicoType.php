<?php
/**
 * Description of SolicitacaoServicoType
 *
 * @author Héctor Martínez / +58-416-9052533
 */
namespace Pmc\IntranetBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class SolicitacaoServicoType extends AbstractType
{  
    protected $servico;
    protected $secretaria;
    
    public function __construct($datos) 
    {
        $this->servico = $datos['servico'];
        $this->secretaria = $datos['secretaria'];
    }
    
    public function buildForm(FormBuilderInterface $builder, array $options)
    {       
      $builder
        ->add('titulo', null, array('label'=>'Título/Assunto:',
                                 'required'=>true,
                                     'attr'=>array(
                                               'maxlength'=>'100',
                                                 'onclick'=>'$(this).focus();',
                                                   'class'=>'form-control',                                                   
                                             'placeholder'=>'Título/Assunto',
                                                   'title'=>'Título/Assunto')))
              
        ->add('conteudo', null, array('label'=>'Descrição da Solicitação',
                                 'required'=>false,
                                     'attr'=>array('rows' =>'2',
                                               'maxlength'=>'2000',
                               'onclick'=>'$(this).focus();resizeTextarea($(this).attr("id"));',
                                         'onkeyup'=>'resizeTextarea($(this).attr("id"))',
                                                   'class'=>'form-control',
                                                   'style'=>'resize:vertical;',
                                             'placeholder'=>'Descrição da Solicitação',
                                                   'title'=>'Descrição da Solicitação')))
              
        ->add('urgente', null, array( 'label'=>'Urgente',
                                   'required'=>true))               
                                                               
        ->add('servico', 'entity', array('label'=>'Serviço',
                                    'class' => 'PmcIntranetBundle:Servico',
                                   'choices'=> $this->servico,
                                // 'disabled' => true,                               
                                      'attr'=> array('class'=>'form-control',
                                                  'readonly'=>'readonly')))
              
        ->add('secretaria', 'text', array('label' => 'Secretaria',
                                         'mapped' => false,                              
                                      'attr'=> array('class'=>'form-control',
                                                    'value' => $this->secretaria,
                                                  'readonly'=>'readonly')))              
            ;
    }
   
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
      $resolver->setDefaults(array(
                'data_class' => 'Pmc\IntranetBundle\Entity\SolicitacaoServico',
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
