<?php

namespace Pmc\IntranetBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Pmc\IntranetBundle\Entity\Secretaria;
use Pmc\IntranetBundle\Form\FiltrosPublicacoesType;
use Pmc\IntranetBundle\Form\SecretariaType;

class SecretariaController extends Controller
{
    /*
     * Lista las Secretarias existentes en el sistema
     */
    public function listarSecretariasAction(Request $request)
    {           
        $data = $this->get('serviciosComunes')->rutinaInicio();
        
        $data['titulo'] = 'Secretarias';
        $data['uri'] = $request->getUri(); 

        $formulario = $this->createForm(new FiltrosPublicacoesType());
        $data['uri'] = $request->getUri(); 
        // SOLO SI EL USUARIO ES ADMIN O PRENSA PUEDE FILTRAR SECRETARIAS POR STATUS        
        $data['filtroStatus'] =  !($this->get('security.context')->isGranted('ROLE_PRENSA'))?
                                  'oculto':'';
        
        $filtros = array();
        if ($request->isMethod('POST'))
        {        
           $filtros = $request->request->get('form');           
           $formulario->bind($request); 
        }  
        $filtros['filtroStatus'] = $data['filtroStatus'];
        
        $query = $this->getDoctrine()->getRepository('PmcIntranetBundle:Secretaria')
                                     ->getSecretariasFiltradas($filtros);      
        
        $firstResult =(isset($filtros['puntero']))? $filtros['puntero']: 0;
        $data['secretarias'] = $this->get('serviciosComunes')
                                    ->paginador($query, $firstResult);
        
        // EXAMINAMOS SI LA PETICION VIENE DE AJAX
        if (!($this->getRequest()->isXmlHttpRequest()))  
            return $this->render('PmcIntranetBundle:Secretaria:listaSecretarias.html.twig',
                    array('formulario' => $formulario->createView(), 
                                'data' => $data ));
        else
            return $this->render('PmcIntranetBundle:Secretaria:secretarias.html.twig',
                                    array('data' => $data ));
    }
    
    /*
     * Método para Insertar Nueva Secretaria
     */
    public function novaSecretariaAction(Request $request)
    {
       // CHEQUEO DE PERMISOS
       if ( !($this->get('security.context')->isGranted('ROLE_PRENSA')) )
            return $this->render('PmcIntranetBundle:Templates:prohibido.html.twig');
              
       $data = $this->get('serviciosComunes')->rutinaInicio();
       $data['titulo'] = 'Secretaria';
       $data['accion'] = 'nova';
       
       $secretaria = new Secretaria();
       
       $secretarios = $this->_buscarSecretarios();       
       
       $formulario = $this->createForm(new SecretariaType($secretarios), $secretaria);
             
       if ($request->isMethod('POST'))
       {   
           $formulario->bind($request); 
           
           if ($formulario->isValid()) 
           { 
              // ACTUALIZAMOS EN BD
              $error = false;
              $em = $this->getDoctrine()->getManager();
              $em->persist($secretaria);
              try {
                    $em->flush();
              } catch (\Exception $e) { // Atrapa Error del servidor
                      if(stristr($e->getMessage(), 'Integrity constraint violation: 1048'))                          
                         $error ='Todos os campos tem que ser preenchidos corretamente.';
                      else if(stristr($e->getMessage(), 
                              'Integrity constraint violation: 1062 Duplicate entry'))                  
                         $error='Email ou Usuario duplicado no Servidor';                 
                      else $error = $e->getMessage();           
                      
                $this->get('session')->getFlashBag()->add('error', $error);
              }  
              
              if (!$error)
              {
                // Registramos en bitácora
                $data['action'] = 'INSERT';
                $data['module'] = 'Secretaria';
                $data['description'] = 'Nova Secretaria id: '.$secretaria->getId().
                                       ', "'.substr($secretaria->getNome(), 0, 20).'...".';
                $this->get('serviciosComunes')->cadastrarLog($data); 
                
                // ACTUALIZACIÓN SATISFACTORIA REDIRECCIONAMOS A LISTADO 
                $this->get('session')->getFlashBag()
                                     ->add('info', 'a Secretaria foi criada com sucesso'); 
                
                return $this->redirect($this->generateUrl('secretarias'));  
              }  
           }   
       }     
       return $this->render('PmcIntranetBundle:Secretaria:crudSecretaria.html.twig',array(
                         'formulario' => $formulario->createView(),   
                               'data' => $data )); 
    }     
    
    /*
     * Método para Editar Secretaria id
     */
    public function editarSecretariaAction($id)
    {
       // CHEQUEO DE PERMISOS
       if ( !($this->get('security.context')->isGranted('ROLE_PRENSA')) and
            !($id === null)   )
            return $this->render('PmcIntranetBundle:Templates:prohibido.html.twig');
              
       $data = $this->get('serviciosComunes')->rutinaInicio();
       $data['titulo'] = 'Secretaria';
       $data['accion'] = 'editar';
              
       $secretaria = $this->getDoctrine()
                          ->getRepository('PmcIntranetBundle:Secretaria')
                          ->find($id);
       if (!$secretaria) return $this->render('PmcIntranetBundle:Templates:prohibido.html.twig');
       
       $secretarios = $this->_buscarSecretarios();       
       
       $formulario = $this->createForm(new SecretariaType($secretarios), $secretaria);
       
       $request = $this->getRequest();
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
                      if(stristr($e->getMessage(), 'Integrity constraint violation: 1048'))                          
                         $error ='Todos os campos tem que ser preenchidos corretamente.';
                      else if(stristr($e->getMessage(), 
                              'Integrity constraint violation: 1062 Duplicate entry'))                  
                         $error='Secretaria duplicada no Servidor';                 
                      else $error = $e->getMessage();           
                      
                $this->get('session')->getFlashBag()->add('error', $error);
              } 
              
              if (!$error)
              {
                // Registramos en bitácora
                $data['action'] = 'UPDATE';
                $data['module'] = 'Secretaria';
                $data['description'] = 'Edição da Secretaria id: '.$id.
                                       ', "'.substr($secretaria->getNome(), 0, 20).'...".';
                $this->get('serviciosComunes')->cadastrarLog($data); 
                
                // ACTUALIZACIÓN SATISFACTORIA REDIRECCIONAMOS A BANNERS
                $this->get('session')->getFlashBag()->add('info', 'A Secretaria foi atualizada com sucesso');
                return $this->redirect($this->generateUrl('secretarias'));  
              }  
           }   
       }     
       return $this->render('PmcIntranetBundle:Secretaria:crudSecretaria.html.twig',array(
                         'formulario' => $formulario->createView(),
                               'data' => $data )); 
    }    
    
   /*
     * Método AJAX para excluir una Secretaria
     */
    public function excluirSecretariaAction(Request $request)
    {
       // Verifica si la petición No es de AJAX  
       if (!($this->getRequest()->isXmlHttpRequest())) 
          return $this->render('PmcIntranetBundle:Templates:prohibido.html.twig');
       
       if ($request->isMethod('POST')) 
       {  
           $id = $request->request->get('id');

           // Buscamos el Servicio
           $em = $this->getDoctrine()->getManager();
       
           $secretaria = $em->getRepository('PmcIntranetBundle:Secretaria')
                            ->find($id);           
           
           if (!$secretaria) die(json_encode(array('error'=>'Objeto não encontrado')));
            
           $clon = $secretaria->getNome(); //Clonamos nombre antes de eliminarla
           
           $error='';
           $em->remove($secretaria);            
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
           $data['module'] = 'Secretaria';
           $data['description'] = 'Exclusão da Secretaria id: '.$id.
                                 ', "'.substr($clon, 0, 20).'...".';
           $this->get('serviciosComunes')->cadastrarLog($data);
           
           die(json_encode(array('html' => '' )));      
       }       
    } 
    
    private function _buscarSecretarios()
    {
       // Identificamos el rol Secretario y Prefeito
       $secretario = $this->getDoctrine()
                          ->getRepository('PmcIntranetBundle:Role')
                          ->find(3);
       $prefeito = $this->getDoctrine()
                        ->getRepository('PmcIntranetBundle:Role')
                        ->find(4);
       
       // BUSCAMOS LOS SECRETARIOS POSIBLES
       $secretarios = $this->getDoctrine()->getManager()
                                          ->createQuery(
                        'SELECT u FROM PmcIntranetBundle:Usuario u
                         WHERE u.ativo = :ativo
                         AND ((:secretario MEMBER OF u.role) or
                              (:prefeito MEMBER OF u.role))
                         ORDER BY u.nome ASC')
                    ->setParameters(array('ativo' => true,
                                     'secretario' => $secretario,
                                       'prefeito' => $prefeito ))
                    ->getResult();
       
       return $secretarios;
    }

}