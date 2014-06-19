<?php

namespace Pmc\IntranetBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Pmc\IntranetBundle\Form\FiltrosPublicacoesType;
use Pmc\IntranetBundle\Entity\Servico;
use Pmc\IntranetBundle\Form\ServicoType;

class ServicoController extends Controller
{
    /*
     * Lista los Servicios existentes en el sistema
     */
    public function listarServicosAction(Request $request)
    {           
        $data = $this->get('serviciosComunes')->rutinaInicio();
        
        $data['titulo'] = 'Serviços';
        $data['uri'] = $request->getUri(); 

        $formulario = $this->createForm(new FiltrosPublicacoesType());
         
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
        
        $query = $this->getDoctrine()->getRepository('PmcIntranetBundle:Servico')
                                     ->getServicosFiltrados($filtros); 
        
        $firstResult =(isset($filtros['puntero']))? $filtros['puntero']: 0;
        $data['servicos'] = $this->get('serviciosComunes')
                                 ->paginador($query, $firstResult);
        
        // EXAMINAMOS SI LA PETICION VIENE DE AJAX
        if (!($this->getRequest()->isXmlHttpRequest()))  
            return $this->render('PmcIntranetBundle:Servico:listaServicos.html.twig',
                    array('formulario' => $formulario->createView(), 
                                'data' => $data ));
        else
            return $this->render('PmcIntranetBundle:Servico:servicos.html.twig',
                                    array('data' => $data ));
    }
    
    /*
     * Método para Crear Servico
     */
    public function novoServicoAction(Request $request)
    {
       // CHEQUEO DE PERMISOS
       if ( !($this->get('security.context')->isGranted('ROLE_ADJUNTO')) )
            return $this->render('PmcIntranetBundle:Templates:prohibido.html.twig');
              
       $data = $this->get('serviciosComunes')->rutinaInicio();
       $data['titulo'] = 'Serviço';
       $data['accion'] = 'novo';
            
       $servico = new Servico();
       $secretarias = ($this->get('security.context')->isGranted('ROLE_PRENSA'))? 
                                null : array($this->getUser()->getSecretaria());
       
       $datos = array('secretarias' => $secretarias);
       
       $formulario = $this->createForm(new ServicoType($datos), $servico);
       
       $request = $this->getRequest();
       if ($request->isMethod('POST'))
       {   
           $formulario->bind($request); 
           
           if ($formulario->isValid()) 
           {   
              // ACTUALIZAMOS EN BD
              $error = false;
              $em = $this->getDoctrine()->getManager();
              $em->persist($servico);
              try {
                    $em->flush();
              } catch (\Exception $e) { // Atrapa Error del servidor
                      if(stristr($e->getMessage(), 'Integrity constraint violation: 1048'))                          
                         $error ='Todos os campos tem que ser preenchidos corretamente.';
                      else if(stristr($e->getMessage(), 
                              'Integrity constraint violation: 1062 Duplicate entry'))                  
                         $error='Objeto duplicado no Servidor';                 
                      else $error = $e->getMessage();           
                      
                $this->get('session')->getFlashBag()->add('error', $error);
              } 
              
              if (!$error)
              {
                // Registramos en bitácora
                $data['action'] = 'INSERT';
                $data['module'] = 'Servico';
                $data['description'] = 'Novo Serviço id: '.$servico->getId().
                                       ', "'.substr($servico->getNome(), 0, 20).'...".';
                $this->get('serviciosComunes')->cadastrarLog($data); 
                
                // ACTUALIZACIÓN SATISFACTORIA REDIRECCIONAMOS A SERVICIOS
                $this->get('session')->getFlashBag()->add('info', 'o Serviço foi criado com sucesso');
                return $this->redirect($this->generateUrl('servicos'));  
              }  
           }   
       }     
       return $this->render('PmcIntranetBundle:Servico:crudServico.html.twig',array(
                         'formulario' => $formulario->createView(),   
                               'data' => $data )); 
    }
    
    /*
     * Método para Editar Servico id
     */
    public function editarServicoAction($id)
    {
       // CHEQUEO DE PERMISOS
       if ( !($this->get('security.context')->isGranted('ROLE_ADJUNTO')) and
            !($id === null)   )
            return $this->render('PmcIntranetBundle:Templates:prohibido.html.twig');
              
       $data = $this->get('serviciosComunes')->rutinaInicio();
       $data['titulo'] = 'Serviço';
       $data['accion'] = 'editar';
              
       $servico = $this->getDoctrine()
                       ->getRepository('PmcIntranetBundle:Servico')
                       ->find($id);
       if (!$servico or ( !($this->get('security.context')->isGranted('ROLE_PRENSA')) and
                         ($this->getUser()->getSecretaria() != $servico->getSecretaria()) )) 
          return $this->render('PmcIntranetBundle:Templates:prohibido.html.twig');
       
       $secretarias = ($this->get('security.context')->isGranted('ROLE_PRENSA'))? 
                                null : array($servico->getSecretaria());
       $datos = array('secretarias' => $secretarias);
       
       $formulario = $this->createForm(new ServicoType($datos), $servico);
       
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
                         $error='Objeto duplicado no Servidor';                 
                      else $error = $e->getMessage();           
                      
                $this->get('session')->getFlashBag()->add('error', $error);
              } 
              
              if (!$error)
              {
                // Registramos en bitácora
                $data['action'] = 'UPDATE';
                $data['module'] = 'Servico';
                $data['description'] = 'Edição de Serviço id: '.$id.
                                       ', "'.substr($servico->getNome(), 0, 20).'...".';
                $this->get('serviciosComunes')->cadastrarLog($data); 
                
                // ACTUALIZACIÓN SATISFACTORIA REDIRECCIONAMOS A Servicios
                $this->get('session')->getFlashBag()->add('info', 'o Serviço foi atualizado com sucesso');
                return $this->redirect($this->generateUrl('servicos'));  
              }  
           }   
       }     
       return $this->render('PmcIntranetBundle:Servico:crudServico.html.twig',array(
                         'formulario' => $formulario->createView(),   
                               'data' => $data )); 
    }
    
   /*
     * Método AJAX para excluir un Servicio
     */
    public function excluirServicoAction(Request $request)
    {
       // Verifica si la petición No es de AJAX  
       if (!($this->getRequest()->isXmlHttpRequest())) 
          return $this->render('PmcIntranetBundle:Templates:prohibido.html.twig');
       
       if ($request->isMethod('POST')) 
       {  
           $id = $request->request->get('id');

           // Buscamos el Servicio
           $em = $this->getDoctrine()->getManager();
       
           $servico = $em->getRepository('PmcIntranetBundle:Servico')
                            ->find($id);           
           
           if (!$servico) die(json_encode(array('error'=>'Objeto não encontrado')));

           $clon = $servico->getNome(); //Clonamos nombre antes de eliminarlo
           
           $error='';
           $em->remove($servico);            
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
           $data['module'] = 'Servico';
           $data['description'] = 'Exclusão de Serviço id: '.$id.
                                 ', "'.substr($clon, 0, 20).'...".';
           $this->get('serviciosComunes')->cadastrarLog($data);
           
           die(json_encode(array('html' => '' )));      
       }       
    }            
    
   /*
     * Método AJAX para mostrar los funcionarios de una secretaria id
     */
    public function mostrarFuncionariosAction(Request $request)
    {
       // Verifica si la petición No es de AJAX  
       if (!($this->getRequest()->isXmlHttpRequest())) 
          return $this->render('PmcIntranetBundle:Templates:prohibido.html.twig');
       
       if ($request->isMethod('POST')) 
       {  
           $id = $request->request->get('id');
           $checkados = $request->request->get('checkados');
           // Consultamos en BD           
           $usuarios = $this->getDoctrine()->getManager()
                            ->createQuery(
                                         'SELECT u FROM PmcIntranetBundle:Usuario u
                                          JOIN u.secretaria s
                                          LEFT JOIN u.role r
                                          WHERE u.ativo = :ativo                                          
                                          AND (r.id != 6)
                                          AND s.id = :id
                                          ORDER BY u.nome ASC')
                            ->setParameters(array('ativo' => true,
                                                     'id' => $id,))
                            ->getResult();
           
           $data['usuarios'] = $usuarios;
           $data['checkados'] = $checkados;
           
           $html = $this->renderView('PmcIntranetBundle:Servico:mostrarFuncionarios.html.twig',
                                                    array('data' => $data )); 
           die(json_encode(array('html' => $html )));       
       } 
    }            
}