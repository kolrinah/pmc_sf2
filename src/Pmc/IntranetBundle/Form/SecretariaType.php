<?php
/**
 * Description of SecretariaType
 *
 * @author Héctor Martínez / +58-416-9052533
 */
namespace Pmc\IntranetBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class SecretariaType extends AbstractType
{   
    protected $secretarios;
    
    public function __construct($secretarios) 
    {        
        $this->secretarios = $secretarios;
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
                                             'placeholder'=>'Escreva nome da Secretaria',
                                                   'title'=>'Nome da Secretaria')))
              
        ->add('endereco', null, array('label'=>'Endereço',
                                     'attr'=>array(
                                               'maxlength'=>'100',
                                                 'onclick'=>'$(this).focus();',
                                                   'class'=>'form-control',                                                   
                                             'placeholder'=>'Escreva endereço da Secretaria',
                                                   'title'=>'Endereço da Secretaria')))
              
        ->add('telefone', null, array('attr'=>array(
                                               'maxlength'=>'13',
                                                 'onclick'=>'$(this).focus();',
                                                   'class'=>'form-control',                                                   
                                             'placeholder'=>'Telefone da Secretaria',
                                                   'title'=>'Telefone da Secretaria')))
              
        ->add('email', 'email', array('label'=>'E-Mail da Secretaria',
                                     'attr'=>array(
                                               'maxlength'=>'100',
                                                 'onclick'=>'$(this).focus();',
                                                   'class'=>'form-control',                                                   
                                             'placeholder'=>'E-Mail da Secretaria',
                                                   'title'=>'E-Mail da Secretaria')))              
              
        ->add('urlSite', 'url', array('label'=>'URL da Secretaria',
                                          'attr'=>array(
                                               'maxlength'=>'100',
                                                 'onclick'=>'$(this).focus();',
                                                   'class'=>'form-control',
                                             'placeholder'=>'Telefone da Secretaria',
                                                   'title'=>'Telefone da Secretaria')))  
              
        ->add('mapa', null, array('label'=>'Mapa de Localização',
                                 'required'=>false,
                      'attr'=>array('rows' =>'2',
                                'maxlength'=>'1500',
                                    'class'=>'form-control',
                                  'onclick'=>'$(this).focus(); resizeTextarea($(this).attr("id"));',
                                  'onkeyup'=>'resizeTextarea($(this).attr("id"))',
                                    'style'=>'resize:vertical;',
                              'placeholder'=>'Código de googleMaps',
                                    'title'=>'Código de googleMaps')))             
             
        ->add('secretario', 'entity', array('label'=>'Secretário',
                                 'class' => 'PmcIntranetBundle:Usuario',
                           'empty_value' => '[ Selecione Secretário ]',
                               'choices' => $this->secretarios,
                               'required'=> true,
                                   'attr'=> array('class'=>'form-control') ))

        ->add('ativo', null, array( 'label'=>'Secretaria Ativa',
                                 'required'=>true,
                                     'attr'=>array('value'=>true)))                                                     

            ;
    }
   
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
      $resolver->setDefaults(array(
                'data_class' => 'Pmc\IntranetBundle\Entity\Secretaria',
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
