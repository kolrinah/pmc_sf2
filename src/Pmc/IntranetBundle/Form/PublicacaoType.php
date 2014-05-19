<?php
/**
 * Description of PublicacaoType
 *
 * @author Héctor Martínez / +58-416-9052533
 */
namespace Pmc\IntranetBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class PublicacaoType extends AbstractType
{  
    protected $datos;
    
    public function __construct($datos) 
    {
        $this->datos = $datos;
    }
    
    public function buildForm(FormBuilderInterface $builder, array $options)
    { 
      $builder
        ->add('titulo', null, array('label'=>'Título',            
                                 'required'=>true,
                                     'attr'=>array('rows' =>'1',
                                          'tabindex'=>0,
                                         'autofocus'=>'autofocus',
                             'onclick'=>'$(this).focus();resizeTextarea($(this).attr("id"));',
                                    'onkeyup'=>'resizeTextarea($(this).attr("id"))',
                                               'maxlength'=>'150',
                                                   'class'=>'form-control',
                                                   'style'=>'resize:vertical;',
                                             'placeholder'=>'Escreva o Título',
                                                   'title'=>'Escreva o Título')))

        ->add('resumo', null, array('label'=>'Resumo',
                                 'required'=>false,
                                     'attr'=>array('rows' =>'2',
                               'onclick'=>'$(this).focus();resizeTextarea($(this).attr("id"));',
                                         'onkeyup'=>'resizeTextarea($(this).attr("id"))',
                                               'maxlength'=>'1000',
                                                   'class'=>'form-control',
                                                   'style'=>'resize:vertical;',
                                             'placeholder'=>'Escreva o Resumo',
                                                   'title'=>'Escreva o Resumo')))
              
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
              
        ->add('video', null, array('label'=>'Vídeo',            
                                 'required'=>false,
                      'attr'=>array('rows' =>'2',
                                'maxlength'=>'250',
                                    'class'=>'form-control',
                                  'onclick'=>'$(this).focus(); resizeTextarea($(this).attr("id"));',
                                  'onkeyup'=>'resizeTextarea($(this).attr("id"))',
                                    'style'=>'resize:vertical;',
                              'placeholder'=>'Código ou url link de Vídeo (Vimeo ou Youtube)',
                                    'title'=>'Código ou url link de Vídeo (Vimeo ou Youtube)')))               
              
        ->add('imagem', 'file', array('label'=>'Imagem',                                    
                                   'required'=>false,
                                'data_class' => null))  
        
        ->add('dataEvento', 'date' , array('label' => 'Data do Evento',                                          
                                          'widget' => 'single_text',
                                          'format' => 'dd/MM/yyyy',
                            'attr' => array('class'=>'form-control',
                                         'readonly'=>'readonly',
                           'style'=>'background-color:white;'.
                                    'display: inline; margin-right:3px;'.
                                    'cursor:pointer; width:auto;'.
                                    'text-align:center', )))
              
        ->add('tipo', 'entity', array('required'=>true,
                                         'class'=>'PmcIntranetBundle:TipoPublicacao',
                                       'choices'=>array($this->datos['tipo']) ))        
        
              
        ->add('usuario', 'entity', array('required'=>true,
                                            'class'=>'PmcIntranetBundle:Usuario',
                                          'choices'=>array($this->datos['usuario']) ))
              
        ->add('publico', null, array('label'=>'Público',
                                     'required'=>true,
                                     'attr'=>array('value'=>false))) 
              
        ->add('ativo', null, array('required'=>true,
                                     'attr'=>array('value'=>true))) 
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
