<?php

namespace Pmc\IntranetBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Pmc\IntranetBundle\Entity\LinkUteis;
use Pmc\IntranetBundle\Form\LinkUteisType;

class LinkUteisController extends Controller
{
    /*
     * Lista los Links Útiles existentes en el sistema
     */
    public function listarLinksUteisAction(Request $request)
    {   
        $data = $this->get('serviciosComunes')->rutinaInicio();
        
        $data['titulo'] = 'Links Úteis';
        $data['uri'] = $request->getUri(); 

        $formulario = $this->createFormBuilder()
                
        ->add('patron', 'text', array('required' => false,
                         'attr' => array('placeholder'=>'Escreva seu padrão de pesquisa',
                                               'class'=>'form-control', 
                                           'maxlength'=>'20', 
                                               'title'=>'Padrão de pesquisa')))
                
        ->add('puntero', 'hidden', array('attr'=>array('value'=>'0')) )
                
        ->getForm();        
        
        $filtros = array();
        if ($request->isMethod('POST'))
        {        
           $filtros = $request->request->get('form');
           //die(json_encode($filtros));
           $formulario->bind($request); 
        }  
        
        $patron = (isset($filtros['patron']) and trim($filtros['patron']) != '')? 
                                 trim($filtros['patron']):'';
                
        $em = $this->getDoctrine()->getManager();        
        $query = $em->createQuery('SELECT l
                                   FROM PmcIntranetBundle:LinkUteis l 
                                   WHERE (l.nome LIKE :patron or
                                          l.link LIKE :patron)                                                
                                   ORDER BY l.nome ASC, l.id DESC')
                     ->setParameters(array('patron' => "%$patron%"))                 
                     ->getResult();        
        
        $firstResult =(isset($filtros['puntero']))? $filtros['puntero']: 0;
        $data['linksUteis'] =  $this->get('serviciosComunes')
                                    ->paginador($query, $firstResult);
        
        // EXAMINAMOS SI LA PETICION VIENE DE AJAX
        if (!($this->getRequest()->isXmlHttpRequest()))  
            return $this->render('PmcIntranetBundle:LinksUteis:listaLinksUteis.html.twig',
                    array('formulario' => $formulario->createView(), 
                                'data' => $data ));
        else
            return $this->render('PmcIntranetBundle:LinksUteis:linksUteis.html.twig',
                                    array('data' => $data ));
    }

   /*
     * Método AJAX para excluir un Link
     */
    public function excluirLinkUteisAction(Request $request)
    {
       // Verifica si la petición No es de AJAX  
       if (!($this->getRequest()->isXmlHttpRequest())) 
          return $this->render('PmcIntranetBundle:Templates:prohibido.html.twig');
       
       if ($request->isMethod('POST')) 
       {  
           $id = $request->request->get('idLink');           

           // Buscamos el Link
           $em = $this->getDoctrine()->getManager();
       
           $LinkUteis = $em->getRepository('PmcIntranetBundle:LinkUteis')
                        ->find($id);
           
           if (!$LinkUteis) die(json_encode(array('error'=>'Objeto não encontrado')));
                  
           $clon = $LinkUteis->getNome(); //Clonamos titulo antes de eliminarlo
           
           $error='';
           $em->remove($LinkUteis);            
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
           $data['module'] = 'LinkUteis';
           $data['description'] = 'Exclusão de link id: '.$id.
                                 ', "'.substr($clon, 0, 20).'...".';
           $this->get('serviciosComunes')->cadastrarLog($data);
           
           die(json_encode(array('html' => '' )));      
       }       
    }        
    
    /*
     * Método para Insertar Nuevo Link Uteis
     */
    public function novoLinkUteisAction(Request $request)
    {
       // CHEQUEO DE PERMISOS
       if ( !($this->get('security.context')->isGranted('ROLE_PRENSA')) and
            !($this->get('security.context')->isGranted('ROLE_ADMIN')))
            return $this->render('PmcIntranetBundle:Templates:prohibido.html.twig');
       
       $data = $this->get('serviciosComunes')->rutinaInicio();
       $data['titulo'] = 'Link';
       $data['accion'] = 'novo';
       $data['uri'] = $request->getUri();
              
       $linkUteis = new LinkUteis();
       
       $formulario = $this->createForm(new LinkUteisType(), $linkUteis);
             
       if ($request->isMethod('POST'))
       {   
           $formulario->bind($request); 
           
           if ($formulario->isValid()) 
           {  
              // ACTUALIZAMOS EN BD
              $error = false;
              $em = $this->getDoctrine()->getManager();
              $em->persist($linkUteis);
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
                $data['module'] = 'LinkUteis';
                $data['description'] = 'Novo Link id: '.$linkUteis->getId().
                                       ', "'.substr($linkUteis->getNome(), 0, 20).'...".';
                $this->get('serviciosComunes')->cadastrarLog($data); 
                
                // ACTUALIZACIÓN SATISFACTORIA REDIRECCIONAMOS A LINKS
                $this->get('session')->getFlashBag()->add('info', 'o Link foi criado com sucesso');
                return $this->redirect($this->generateUrl('linksUteis'));  
              }  
           }   
       }     
       return $this->render('PmcIntranetBundle:LinksUteis:crudLinksUteis.html.twig',array(
                         'formulario' => $formulario->createView(),   
                               'data' => $data )); 
    }     
    
    /*
     * Método para Editar Link id
     */
    public function editarLinkUteisAction($id)
    {
       // CHEQUEO DE PERMISOS
       if ( !($this->get('security.context')->isGranted('ROLE_PRENSA')) and
            !($this->get('security.context')->isGranted('ROLE_ADMIN')) and
            !($id === null)   )
            return $this->render('PmcIntranetBundle:Templates:prohibido.html.twig');
              
       $data = $this->get('serviciosComunes')->rutinaInicio();
       $data['titulo'] = 'Link';
       $data['accion'] = 'editar';
              
       $link = $this->getDoctrine()
                      ->getRepository('PmcIntranetBundle:LinkUteis')
                      ->find($id);
       if (!$link)  return $this->render('PmcIntranetBundle:Templates:prohibido.html.twig');
       
       $formulario = $this->createForm(new LinkUteisType(), $link);
       
       $request = $this->getRequest();
       $data['uri'] = $request->getUri();
       
       if ($request->isMethod('POST'))
       {             
           $formulario->bind($request); 
           
           if ($formulario->isValid()) 
           {  
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
                $data['module'] = 'LinkUteis';
                $data['description'] = 'Edição de Link id: '.$id.
                                       ', "'.substr($link->getNome(), 0, 20).'...".';
                $this->get('serviciosComunes')->cadastrarLog($data); 
                
                // ACTUALIZACIÓN SATISFACTORIA REDIRECCIONAMOS A BANNERS
                $this->get('session')->getFlashBag()->add('info', 'o Link foi atualizado com sucesso');
                return $this->redirect($this->generateUrl('linksUteis'));  
              }  
           }   
       }     
       return $this->render('PmcIntranetBundle:LinksUteis:crudLinksUteis.html.twig',array(
                         'formulario' => $formulario->createView(),   
                               'data' => $data )); 
    }        

}