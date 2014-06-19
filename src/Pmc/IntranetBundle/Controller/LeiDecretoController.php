<?php

namespace Pmc\IntranetBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Pmc\IntranetBundle\Entity\LeiDecreto;
use Pmc\IntranetBundle\Form\LeiDecretoType;

class LeiDecretoController extends Controller
{
    /*
     * Lista las Leyes o Decretos existentes en el sistema
     */
    public function listarLeiDecretoAction(Request $request)
    {   
        $data = $this->get('serviciosComunes')->rutinaInicio();
        
        $data['titulo'] = 'Leis e Decretos';
        $data['uri'] = $request->getUri(); 

        $formulario = $this->createFormBuilder()
                
        ->add('patron', 'text', array('required' => false,
                         'attr' => array('placeholder'=>'Escreva seu padrão de pesquisa',
                                               'class'=>'form-control', 
                                           'maxlength'=>'20', 
                                               'title'=>'Padrão de pesquisa')))
                
        ->add('lei', 'choice', array('label' => 'Filtrar por:',
                                     'required' => true,
                                      'choices' => array('0' => 'Mostrar solo Leis',
                                                         '1' => 'Mostrar solo Decretos',
                                                         '3' => 'Todos'),
                                     'multiple' => false,
                                     'expanded' => true ))
                
        ->add('puntero', 'hidden', array('attr'=>array('value'=>'0')) )
                
        ->getForm();        
        
        $filtros = array();
        if ($request->isMethod('POST'))
        {        
           $filtros = $request->request->get('form');
           $formulario->bind($request); 
        }  
        
        $patron = (isset($filtros['patron']) and trim($filtros['patron']) != '')? 
                                 trim($filtros['patron']):'';
        
        $lei = (isset($filtros['lei']) and ($filtros['lei']) != '')? 
                                 $filtros['lei']:3;
        
        $em = $this->getDoctrine()->getManager();        
        $query = $em->createQuery('SELECT l
                                   FROM PmcIntranetBundle:LeiDecreto l 
                                   WHERE (l.nome LIKE :patron or
                                          l.ano LIKE :patron or
                                          l.conteudo LIKE :patron)
                                   AND l.lei != :lei       
                                   ORDER BY l.nome ASC, l.id DESC')
                                  ->setParameters(array('patron' => "%$patron%",
                                                        'lei' => $lei))                 
                                  ->getResult();        
        
        $firstResult =(isset($filtros['puntero']))? $filtros['puntero']: 0;
        $data['leiDecreto'] = $this->get('serviciosComunes')
                                   ->paginador($query, $firstResult);
                
        // EXAMINAMOS SI LA PETICION VIENE DE AJAX
        if (!($this->getRequest()->isXmlHttpRequest()))  
            return $this->render('PmcIntranetBundle:LeiDecreto:listaLeiDecreto.html.twig',
                    array('formulario' => $formulario->createView(), 
                                'data' => $data ));
        else
            return $this->render('PmcIntranetBundle:LeiDecreto:leiDecreto.html.twig',
                                    array('data' => $data ));
    }

   /*
     * Método AJAX para excluir un Archivo
     */
    public function excluirLeiDecretoAction(Request $request)
    {
       // Verifica si la petición No es de AJAX  
       if (!($this->getRequest()->isXmlHttpRequest())) 
          return $this->render('PmcIntranetBundle:Templates:prohibido.html.twig');
       
       if ($request->isMethod('POST')) 
       {  
           $id = $request->request->get('id');

           // Buscamos el Archivo
           $em = $this->getDoctrine()->getManager();
       
           $leiDecreto = $em->getRepository('PmcIntranetBundle:LeiDecreto')
                        ->find($id);           
           
           if (!$leiDecreto) die(json_encode(array('error'=>'Objeto não encontrado')));
           
           $clon = $leiDecreto->getNome(); //Clonamos titulo antes de eliminarlo
           
           // Si posee archivo
           if (strlen(trim($leiDecreto->getArquivo())) > 0)
           {   // Eliminamos el archivo del Servidor
               $leiDecreto->removeFile($this->container->getParameter('path_pdf')); 
           }                      
           $error='';
           $em->remove($leiDecreto);            
           try { $em->flush();
           } catch (\Exception $e) { // Atrapa Error del servidor
                    if(stristr($e->getMessage(), 'Foreign key violation'))
                      $error = 'Verifique que no posea Dependencias.';                   
                    else if(stristr($e->getMessage(), 'Unique violation'))                  
                      $error='Objeto duplicado no Servidor'; 
                    else $error = $e->getMessage();               
            die(json_encode(array('error'=>$error)));   
           } 
           // Logró ser eliminado, Registramos en bitácora
           $data['action'] = 'DELETE';
           $data['module'] = 'LeiDecreto';
           $data['description'] = 'Exclusão de lei ou decreto id: '.$id.
                                 ', "'.substr($clon, 0, 20).'...".';
           $this->get('serviciosComunes')->cadastrarLog($data);
           
           die(json_encode(array('html' => '' )));      
       }       
    }        
    
    /*
     * Método para Insertar Nueva Ley o Decreto
     */
    public function novoLeiDecretoAction(Request $request)
    {
       // CHEQUEO DE PERMISOS
       if ( !($this->get('security.context')->isGranted('ROLE_PRENSA')) and
            !($this->get('security.context')->isGranted('ROLE_ADMIN')))
            return $this->render('PmcIntranetBundle:Templates:prohibido.html.twig');
       
       $data = $this->get('serviciosComunes')->rutinaInicio();
       $data['titulo'] = 'Leis e Decretos';
       $data['accion'] = 'nova';
       $data['uri'] = $request->getUri();
              
       $leiDecreto = new LeiDecreto();
       
       $formulario = $this->createForm(new LeiDecretoType(), $leiDecreto);
             
       if ($request->isMethod('POST'))
       {   
           $formulario->bind($request); 
           
           if ($formulario->isValid()) 
           {  // Si hay archivo
              if ($leiDecreto->getArquivo() != null) 
              {
                 $path_pdf = $this->container->getParameter('path_pdf');
                 $leiDecreto->uploadFile($path_pdf);
              }               
              // ACTUALIZAMOS EN BD
              $error = false;
              $em = $this->getDoctrine()->getManager();
              $em->persist($leiDecreto);
              try {
                    $em->flush();
              } catch (\Exception $e) { // Atrapa Error del servidor
                      if(stristr($e->getMessage(), 'Not null violation') or
                         stristr($e->getMessage(), 'Integrity constraint violation')) 
                         $error ='Todos os campos tem que ser preenchidos corretamente.';
                      else if(stristr($e->getMessage(), 'Unique violation'))                  
                         $error='Objeto duplicado no Servidor';                 
                      else $error = $e->getMessage();           
                      
                $this->get('session')->getFlashBag()->add('error', $error);
              }  
              
              if (!$error)
              {
                // Registramos en bitácora
                $data['action'] = 'INSERT';
                $data['module'] = 'LeiDecreto';
                $data['description'] = 'Nova Lei ou Decreto id: '.$leiDecreto->getId().
                                       ', "'.substr($leiDecreto->getNome(), 0, 20).'...".';
                $this->get('serviciosComunes')->cadastrarLog($data); 
                
                // ACTUALIZACIÓN SATISFACTORIA REDIRECCIONAMOS A LINKS
                $this->get('session')->getFlashBag()->add('info', 'o Arquivo foi subido com sucesso');
                return $this->redirect($this->generateUrl('leiDecreto'));  
              }  
           }   
       }     
       return $this->render('PmcIntranetBundle:LeiDecreto:crudLeiDecreto.html.twig',array(
                         'formulario' => $formulario->createView(),   
                               'data' => $data )); 
    }     
    
    /*
     * Método para Editar Lei id
     */
    public function editarLeiDecretoAction($id)
    {
       // CHEQUEO DE PERMISOS
       if ( !($this->get('security.context')->isGranted('ROLE_PRENSA')) and
            !($this->get('security.context')->isGranted('ROLE_ADMIN')) and
            !($id === null)   )
            return $this->render('PmcIntranetBundle:Templates:prohibido.html.twig');
              
       $data = $this->get('serviciosComunes')->rutinaInicio();
       $data['titulo'] = 'Leis e Decretos';
       $data['accion'] = 'editar';
              
       $leiDecreto = $this->getDoctrine()
                      ->getRepository('PmcIntranetBundle:LeiDecreto')
                      ->find($id);
       if (!$leiDecreto)  return $this->render('PmcIntranetBundle:Templates:prohibido.html.twig');
       
       $formulario = $this->createForm(new LeiDecretoType(), $leiDecreto);
       
       $request = $this->getRequest();
       $data['uri'] = $request->getUri();
       
       if ($request->isMethod('POST'))
       {       
           $archivoOriginal = $formulario->getData()->getArquivo();
           
           $formulario->bind($request); 
           
           if ($formulario->isValid()) 
           {  
              if (null == $leiDecreto->getArquivo()) 
              {  // El archivo original no se modifica, recuperar su ruta
                 $leiDecreto->setArquivo($archivoOriginal);
              } 
              else 
              {  // El archivo se ha modificado              
                 $path_pdf = $this->container->getParameter('path_pdf');
                 $leiDecreto->uploadFile($path_pdf);                 
                 // Borrar el archivo anterior
                 @unlink($path_pdf.$archivoOriginal);
              }               
              // ACTUALIZAMOS EN BD
              $error = false;
              $em = $this->getDoctrine()->getManager();
              try {
                    $em->flush();
              } catch (\Exception $e) { // Atrapa Error del servidor
                      if(stristr($e->getMessage(), 'Not null violation') or
                         stristr($e->getMessage(), 'Integrity constraint violation')) 
                         $error ='Todos os campos tem que ser preenchidos corretamente.';
                      else if(stristr($e->getMessage(), 'Unique violation'))                  
                         $error='Objeto duplicado no Servidor';                 
                      else $error = $e->getMessage();           
                      
                $this->get('session')->getFlashBag()->add('error', $error);
              }  
              
              if (!$error)
              {
                // Registramos en bitácora
                $data['action'] = 'UPDATE';
                $data['module'] = 'LeiDecreto';
                $data['description'] = 'Edição de Lei ou Decreto id: '.$leiDecreto->getId().
                                       ', "'.substr($leiDecreto->getNome(), 0, 20).'...".';
                $this->get('serviciosComunes')->cadastrarLog($data); 
                
                // ACTUALIZACIÓN SATISFACTORIA REDIRECCIONAMOS A LeiDecreto
                $this->get('session')->getFlashBag()->add('info', 'o Arquivo foi atualizado com sucesso');
                return $this->redirect($this->generateUrl('leiDecreto'));  
              }  
           }   
       }     
       return $this->render('PmcIntranetBundle:LeiDecreto:crudLeiDecreto.html.twig',array(
                         'formulario' => $formulario->createView(),   
                               'data' => $data )); 
    }        

}