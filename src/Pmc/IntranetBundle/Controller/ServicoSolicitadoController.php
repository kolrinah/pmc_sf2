<?php

namespace Pmc\IntranetBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Pmc\IntranetBundle\Form\FiltrosPublicacoesType;
use Pmc\IntranetBundle\Entity\SolicitacaoServico;
use Pmc\IntranetBundle\Entity\ComentarioSolicitacaoServico;
use Pmc\IntranetBundle\Form\SolicitacaoServicoType;

class ServicoSolicitadoController extends Controller
{
    /*
     * Lista las Solicitudes de Servicios del usuario y del responsable
     */
    public function listarServicosSolicitadosAction(Request $request)
    {           
        $data = $this->get('serviciosComunes')->rutinaInicio();
        
        $data['titulo'] = 'Solicitações';
        $data['uri'] = $request->getUri(); 

        $formulario = $this->createForm(new FiltrosPublicacoesType());
        $data['uri'] = $request->getUri(); 
        
        $filtros = array();
        if ($request->isMethod('POST'))
        {        
           $filtros = $request->request->get('form');           
           $formulario->bind($request); 
        }          
        $filtros['usuario'] = $this->getUser();
        
        $query = $this->getDoctrine()->getRepository('PmcIntranetBundle:Servico')
                                     ->getSolicitacoesServicosFiltrados($filtros); 
        
        $firstResult =(isset($filtros['puntero']))? $filtros['puntero']: 0;
        $data['solicitacoesServicos'] = $this->get('serviciosComunes')
                                             ->paginador($query, $firstResult);
        
        // EXAMINAMOS SI LA PETICION VIENE DE AJAX
        if (!($this->getRequest()->isXmlHttpRequest()))  
            return $this->render('PmcIntranetBundle:SolicitacaoServico:listaSolicitacoesServicos.html.twig',
                    array('formulario' => $formulario->createView(), 
                                'data' => $data ));
        else
            return $this->render('PmcIntranetBundle:SolicitacaoServico:solicitacoesServicos.html.twig',
                                    array('data' => $data ));
    }
    
    /*
     * Método para finalizar Solicitud
     */
    public function finalizarSolicitacaoAction(Request $request)
    {
       // Verifica si la petición No es de AJAX  
       if (!($this->getRequest()->isXmlHttpRequest()) or
           !($request->isMethod('POST')) )
           return $this->render('PmcIntranetBundle:Templates:prohibido.html.twig');
              
       $id = $request->request->get('id');
       
       $solicitacao = $this->getDoctrine()
                           ->getRepository('PmcIntranetBundle:SolicitacaoServico')
                           ->find($id);        
       
       $isUsuario = ($this->getUser() == $solicitacao->getUsuario())? true: false; 
       $responsables = $solicitacao->getServico()->getResponsavel();
       
       $isResponsable = $this->get('serviciosComunes')
                             ->inCollection($this->getUser(), $responsables);
       
       if (!$solicitacao and !$isUsuario and !$isResponsable) 
          die(json_encode(array('error'=>'Usuário sem permissão para finalizar a solicitação')));
          
       $data = $this->get('serviciosComunes')->rutinaInicio();
       
       if ($isUsuario) $solicitacao->setAbertoUsuario(false);
       if ($isResponsable) $solicitacao->setAbertoSecretaria(false);
       
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
           die(json_encode(array('error'=>$error)));  
       } 
              
       if (!$error)
       {
          // Registramos en bitácora
          $data['action'] = 'UPDATE';
          $data['module'] = 'ServicoSolicitado';
          $data['description'] = 'Solicitação de Serviço id: '.$solicitacao->getId().                                 
                                 ', "'.substr($solicitacao->getTitulo(), 0, 20).'..." finalizada.';          
          $this->get('serviciosComunes')->cadastrarLog($data); 
          
          $mensaje = '<span style="color: red">Finalizada com sucesso!</span>';
          die(json_encode(array('html'=>$mensaje)));  
       }  
    }

    /*
     * Método AJAX para postear un comentario
     */
    public function postarComentarioAction(Request $request)
    {
       // Verifica si la petición No es de AJAX  
       if (!($this->getRequest()->isXmlHttpRequest())) 
          return $this->render('PmcIntranetBundle:Templates:prohibido.html.twig');
       
       if ($request->isMethod('POST')) 
       { 
           $postar = trim($request->request->get('postar'));
           $id = $request->request->get('id');
           
           $comentario = new ComentarioSolicitacaoServico();
           
           $solicitacao = $this->getDoctrine()
                              ->getRepository('PmcIntranetBundle:SolicitacaoServico')
                              ->find($id);
           
           $comentario->setComentario($postar);
           $comentario->setSolicitacaoServico($solicitacao);
           $comentario->setUsuario($this->getUser());           
           $comentario->setData(new \DateTime('now'));
           
           $error ='';
           $em = $this->getDoctrine()->getManager();           
           $em->persist($comentario);
           try {       
                 $em->flush();
           } catch (\Exception $e) { // Atrapa Error del servidor
                 if(stristr($e->getMessage(), 'Not null violation'))                  
                    $error='Todos os campos tem que ser preenchidos corretamente.';                 
                 else if(stristr($e->getMessage(), 'Unique violation'))                  
                    $error='Objeto duplicado no Servidor';                 
                 else $error = $e->getMessage();
                 
                 die (json_encode(array('error'=>$error)));
           }           
           // Registramos en bitácora
           $data['action'] = 'INSERT';
           $data['module'] = 'ServicoSolicitado';
           $data['description'] = 'Novo comentário id: '.$comentario->getId().
                                  ', "'.substr($comentario->getComentario(), 0, 20).'...",'.
                                  ' em solicitação de serviço id: '.$solicitacao->getId().', "'.
                                  substr($solicitacao->getTitulo(), 0, 20).'...".'; 
           $this->get('serviciosComunes')->cadastrarLog($data);
           
           // REGISTRO EXITOSO
           die (json_encode(array('exito'=>$error))); 
       }       
    }

    /*
     * Método AJAX para refrescar caja de comentarios de una publicación
     */
    public function refrescarComentariosAction(Request $request)
    {
       // Verifica si la petición No es de AJAX  
       if (!($this->getRequest()->isXmlHttpRequest())) 
          return $this->render('PmcIntranetBundle:Templates:prohibido.html.twig');
       
       if ($request->isMethod('POST')) 
       {  
           $id = $request->request->get('id');           
           /*
            * Buscamos los comentarios de la publicación
            */
           $em = $this->getDoctrine()->getManager();
           $query = $em->createQuery('SELECT c, u
                                      FROM PmcIntranetBundle:ComentarioSolicitacaoServico c
                                      JOIN c.usuario u
                                      JOIN c.solicitacaoServico s                                      
                                      WHERE s.id = :id
                                      ORDER BY c.data ASC')
                       ->setParameter('id', $id);
    
           $p['comentarios'] = $query->getResult();
        
           return $this->render('PmcIntranetBundle:Templates:comentarios.html.twig',array(
                                'p'=>$p )) ;
       }       
    }    
    
    /*
     * Método AJAX para excluir comentario de una publicación
     */
    public function excluirComentarioAction(Request $request)
    {
       // Verifica si la petición No es de AJAX  
       if (!($this->getRequest()->isXmlHttpRequest())) 
          return $this->render('PmcIntranetBundle:Templates:prohibido.html.twig');
       
       if ($request->isMethod('POST')) 
       {  
           $id = $request->request->get('idComentario');           

           // Buscamos el comentario
           $em = $this->getDoctrine()->getManager();
       
           $comentario = $em->getRepository('PmcIntranetBundle:ComentarioSolicitacaoServico')
                            ->find($id);           
           
           if (!$comentario) die(json_encode(array('error'=>'Objeto não encontrado')));
           
           $solicitacao = $comentario->getSolicitacaoServico();           
           $clon = $comentario->getComentario(); //Clonamos nombre antes de eliminarla
           
           $error='';
           $em->remove($comentario);            
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
           $data['module'] = 'ServicoSolicitado';
           $data['description'] = 'Exclusão do comentário id: '.$id.
                                  ', "'.substr($clon, 0, 20).'..." em solicitação de serviço id: '.
                                  $solicitacao->getId().', "'.
                                  substr($solicitacao->getTitulo(), 0, 20).'...".';
           $this->get('serviciosComunes')->cadastrarLog($data);
          
           // Refrescamos los comentarios
           $query = $em->createQuery('SELECT c, u
                                      FROM PmcIntranetBundle:ComentarioSolicitacaoServico c
                                      JOIN c.usuario u
                                      JOIN c.solicitacaoServico s                                      
                                      WHERE s = :solicitacao
                                      ORDER BY c.data DESC')
                       ->setParameter('solicitacao', $solicitacao);
    
           $p['comentarios'] = $query->getResult();
        
           
           $html = $this->renderView('PmcIntranetBundle:Templates:comentarios.html.twig',array(
                                'p'=>$p )) ;
           
           die(json_encode(array('id' => $solicitacao->getId(),
                               'html' => $html)));   
       }       
    }    
    
    /*
     * Método para Crear Solicitud de Servico
     */
    public function solicitarServicoAction($id)
    {    
       // CHEQUEO DE PERMISOS
       if ($id === null) return $this->render('PmcIntranetBundle:Templates:prohibido.html.twig');
              
       $servico = $this->getDoctrine()
                       ->getRepository('PmcIntranetBundle:Servico')
                       ->find($id);
       if (!$servico or !$servico->getAtivo()) 
          return $this->render('PmcIntranetBundle:Templates:prohibido.html.twig');        
       
       $data = $this->get('serviciosComunes')->rutinaInicio();
       $data['titulo'] = 'Solicitação de Serviço';
       $data['accion'] = 'nova';
       
       $datos['servico'] = array($servico);
       $datos['secretaria'] = mb_convert_case($servico->getSecretaria()->getNome(), 
                                                \MB_CASE_UPPER, 'utf-8');
       
       $solicitacaoServico = new SolicitacaoServico();
       $formulario = $this->createForm(new SolicitacaoServicoType($datos), $solicitacaoServico);
       
       $request = $this->getRequest();
       if ($request->isMethod('POST'))
       {   
           $formulario->bind($request); 
           
           if ($formulario->isValid()) 
           {  
              $solicitacaoServico->setUsuario($this->getUser());
              // ACTUALIZAMOS EN BD
              $error = false;
              $em = $this->getDoctrine()->getManager();
              $em->persist($solicitacaoServico );
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
                 // GENERAMOS EL CODIGO INTERNO 
                 $a = sprintf('%03d', $servico->getId()); 
                 $b = sprintf('%07d', $solicitacaoServico->getId()); 
                 $solicitacaoServico->setCodigoInterno($a.'-'.$b);
                 $em->flush();

                // Registramos en bitácora
                $data['action'] = 'INSERT';
                $data['module'] = 'ServicoSolicitado';
                $data['description'] = 'Nova Solicitaçao de Serviço id: '.$solicitacaoServico->getId().
                                       ', "'.substr($solicitacaoServico->getTitulo(), 0, 20).'...".';
                $this->get('serviciosComunes')->cadastrarLog($data);                  
                 
                // ACTUALIZACIÓN SATISFACTORIA REDIRECCIONAMOS A SERVICIOS
                $this->get('session')->getFlashBag()
                     ->add('info', 'A Solicitação Nro. '.
                             $solicitacaoServico->getCodigoInterno().
                             ' foi criada com sucesso');
                return $this->redirect($this->generateUrl('servicos'));  
              }  
           }   
       }     
       return $this->render('PmcIntranetBundle:SolicitacaoServico:crudSolicitacaoServico.html.twig',array(
                         'formulario' => $formulario->createView(),   
                               'data' => $data )); 
    }
    
}