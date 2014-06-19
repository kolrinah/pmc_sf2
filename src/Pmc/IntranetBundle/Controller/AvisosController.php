<?php

namespace Pmc\IntranetBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Pmc\IntranetBundle\Entity\Aviso;
use Pmc\IntranetBundle\Entity\AvisoDestinatario;
use Pmc\IntranetBundle\Entity\AvisoComentario;
use Pmc\IntranetBundle\Form\AvisoType;
use Pmc\IntranetBundle\Form\FiltroAvisosType;

class AvisosController extends Controller
{
    /*
     * Filtra Avisos del usuario o para el usuario
     */
    public function listarMeusAvisosAction(Request $request)
    {                       
        $data = $this->get('serviciosComunes')->rutinaInicio();
        $data['uri'] = $request->getUri();         
        $data['titulo'] = 'Avisos';
        
        $formulario = $this->createForm(new FiltroAvisosType());
           
        $filtros = array();
        if ($request->isMethod('POST'))
        {        
           $filtros = $request->request->get('form');            
           $formulario->bind($request); 
        }
        $filtros['usuario'] = $this->getUser();
        
        $query = $this->getDoctrine()->getRepository('PmcIntranetBundle:Aviso')
                                     ->getAvisosFiltrados($filtros);
        
        $firstResult =(isset($filtros['puntero']))? $filtros['puntero']: 0;
        $data['avisos'] = $this->get('serviciosComunes')
                               ->paginador($query, $firstResult);
        
        // EXAMINAMOS SI LA PETICION VIENE DE AJAX
        if (!($this->getRequest()->isXmlHttpRequest()))
           return $this->render('PmcIntranetBundle:Aviso:listaAvisos.html.twig',
                                    array('formulario' => $formulario->createView(),
                                                'data' => $data ));
        else
           return $this->render('PmcIntranetBundle:Aviso:avisos.html.twig',
                                    array('data' => $data ));
    }
    
   /*
     * Método AJAX para excluir un Aviso
     */
    public function excluirAvisoAction(Request $request)
    {
       // Verifica si la petición No es de AJAX  
       if (!($this->getRequest()->isXmlHttpRequest())) 
          return $this->render('PmcIntranetBundle:Templates:prohibido.html.twig');
       
       if ($request->isMethod('POST')) 
       {  
           $id = $request->request->get('id');

           // Buscamos el Link
           $em = $this->getDoctrine()->getManager();
       
           $aviso = $em->getRepository('PmcIntranetBundle:Aviso')
                       ->find($id);           
           
           if (!$aviso) die(json_encode(array('error'=>'Objeto não encontrado')));
           
           $clon = $aviso->getTitulo(); //Clonamos titulo antes de eliminarlo
           
           $error='';
           $em->remove($aviso);            
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
           $data['module'] = 'Avisos';
           $data['description'] = 'Exclusão do aviso id: '.$id.
                                 ', "'.substr($clon, 0, 20).'...".';
           $this->get('serviciosComunes')->cadastrarLog($data);
           
           die(json_encode(array('html' => '' )));      
       }       
    }     
    
    /*
     * Método AJAX para marcar aviso como leido
     */
    public function lerAvisoAction(Request $request)
    {
       // Verifica si la petición No es de AJAX  
       if (!($this->getRequest()->isXmlHttpRequest())) 
          return $this->render('PmcIntranetBundle:Templates:prohibido.html.twig');
       
       if ($request->isMethod('POST')) 
       {  
           $id = $request->request->get('id');            

           // Buscamos el aviso
           $em = $this->getDoctrine()->getManager();       
           $destino = $em->createQuery('SELECT d
                                      FROM PmcIntranetBundle:AvisoDestinatario d
                                      JOIN d.usuario u
                                      JOIN d.aviso a                                      
                                      WHERE a.id = :id
                                      AND u = :user')
                       ->setParameter('id', $id)
                       ->setParameter('user', $this->getUser())
                       ->getOneOrNullResult();
           
           if ($destino == null) die(json_encode(array('error'=>'Objeto não encontrado')));
           
           $aviso = $destino->getAviso(); // Clonamos registro para registrar en bitácora
           
           $destino->setLeido(true);
           $error='';                 
           try { $em->flush();
           } catch (\Exception $e) { // Atrapa Error del servidor
                    if(stristr($e->getMessage(), 'Foreign key violation'))
                      $error = 'Verifique que no posea Dependencias.';
                    else if(stristr($e->getMessage(), 'integrity constraint violation: 1062 '))
                      $error = 'O usuário já está sendo seguido.';                     
                    else if(stristr($e->getMessage(), 'Unique violation'))                  
                      $error='Objeto duplicado no Servidor'; 
                    else $error = $e->getMessage();               
            die(json_encode(array('error'=>$error)));   
           } 
           // Registramos en bitácora
           $data['action'] = 'UPDATE';
           $data['module'] = 'Avisos';
           $data['description'] = 'Leitura do aviso id: '.$aviso->getId().
                                 ', "'.substr($aviso->getTitulo(), 0, 20).'...".';
           $this->get('serviciosComunes')->cadastrarLog($data);
           
           die(json_encode(array('id' => $id )));   
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
           
           $comentario = new AvisoComentario();
           
           $aviso = $this->getDoctrine()
                              ->getRepository('PmcIntranetBundle:Aviso')
                              ->find($id);
           
           $comentario->setComentario($postar);
           $comentario->setAviso($aviso);
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
           $data['module'] = 'Avisos';
           $data['description'] = 'Novo comentário id: '.$comentario->getId().
                                  ', "'.substr($comentario->getComentario(), 0, 20).'...",'.
                                  ' em aviso id: '.$aviso->getId().', "'.
                                  substr($aviso->getTitulo(), 0, 20).'...".';
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
                                      FROM PmcIntranetBundle:AvisoComentario c
                                      JOIN c.usuario u
                                      JOIN c.aviso a                                      
                                      WHERE a.id = :id
                                      ORDER BY c.data ASC')
                       ->setParameter('id', $id);
    
           $p['comentarios'] = $query->getResult();
        
           return $this->render('PmcIntranetBundle:Templates:comentarios.html.twig',array(
                                'p'=>$p )) ;
       }       
    }    
    
    /*
     * Método AJAX para excluir comentario de un Aviso
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
       
           $comentario = $em->getRepository('PmcIntranetBundle:AvisoComentario')
                            ->find($id);
           
           if (!$comentario) die(json_encode(array('error'=>'Objeto não encontrado')));
           
           $aviso = $comentario->getAviso();
           $clon = $comentario->getComentario();
           
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
           $data['module'] = 'Avisos';
           $data['description'] = 'Exclusão do comentário id: '.$id.
                                  ', "'.substr($clon, 0, 20).'..." em aviso id: '.$aviso->getId().
                                  ', "'.substr($aviso->getTitulo(), 0, 20).'...".';
           $this->get('serviciosComunes')->cadastrarLog($data);
          
           // Refrescamos los comentarios
           $query = $em->createQuery('SELECT c, u
                                      FROM PmcIntranetBundle:AvisoComentario c
                                      JOIN c.usuario u
                                      JOIN c.aviso a
                                      WHERE a = :aviso
                                      ORDER BY c.data DESC')
                       ->setParameter('aviso', $aviso);
    
           $p['comentarios'] = $query->getResult();        
           
           $html = $this->renderView('PmcIntranetBundle:Templates:comentarios.html.twig',array(
                                'p'=>$p )) ;
           
           die(json_encode(array('id' => $aviso->getId(),
                               'html' => $html)));   
       }       
    }        
    
    /*
     * Método para Crear Nuevo Aviso
     */
    public function novoAvisoAction(Request $request)
    {              
       $data = $this->get('serviciosComunes')->rutinaInicio();
       $data['titulo'] = 'Aviso';
       $data['accion'] = 'novo';
       $data['uri'] = $request->getUri();
              
       $formulario = $this->createForm(new AvisoType()); //OJO: No lo asocio con el Objeto Aviso
       
       $request = $this->getRequest();
       if ($request->isMethod('POST'))
       {   
           $formulario->bind($request); 
           
           if ($formulario->isValid()) 
           {  
              // DEBEMOS PERSISTIR PRIMERO EL OBJETO AVISO Y LUEGO EL DE DESTINATARIOS
              $formAviso = $formulario->getData(); // Recuperamos los valores que vienen del formulario
              $aviso = new Aviso();
              
              $aviso->setTitulo($formAviso->getTitulo());
              $aviso->setConteudo($formAviso->getConteudo());
              $aviso->setData(new \DateTime('now'));
              $aviso->setUsuario($this->getUser());
              $aviso->setImportante($formAviso->getImportante());
              
              // PERSISTIMOS EN BD
              $error = false;
              $em = $this->getDoctrine()->getManager();
              $em->persist($aviso);
            
              $formDestinos = $formAviso->getDestinatarios();
              foreach ($formDestinos as $destino)
              {
                  $usuario = $this->getDoctrine()
                                  ->getRepository('PmcIntranetBundle:Usuario')
                                  ->find($destino->getUsuario());
                  $destinatario = new AvisoDestinatario();
                  $destinatario->setAviso($aviso);
                  $destinatario->setUsuario($usuario);
                  $destinatario->setLeido(false);                      
                  // PERSISTIMOS EN BD                                            
                  $em->persist($destinatario);                      
              }                      
              try {
                    $em->flush();
                  } catch (\Exception $e) { // Atrapa Error del servidor
                              if(stristr($e->getMessage(), 'Integrity constraint violation: 1048'))                          
                                 $error ='Todos os campos tem que ser preenchidos corretamente.';
                              else if(stristr($e->getMessage(), 
                                      'Integrity constraint violation: 1062 Duplicate entry'))                  
                                 $error='Usuário repetido em destinatários';
                              else $error = $e->getMessage();           
                              
                        $this->get('session')->getFlashBag()->add('error', $error);
               }
                  
               if (!$error) // SI HUBO ERROR CON LOS DESTINATARIOS, ELIMINAMOS EL AVISO
               {
                   // Registramos en bitácora
                   $data['action'] = 'INSERT';
                   $data['module'] = 'Avisos';
                   $data['description'] = 'Novo aviso id: '.$aviso->getId().
                                          ', "'.substr($aviso->getTitulo(), 0, 20).'...".';
                   $this->get('serviciosComunes')->cadastrarLog($data);
                   
                   // ACTUALIZACIÓN SATISFACTORIA REDIRECCIONAMOS A SERVICIOS
                   $this->get('session')->getFlashBag()->add('info', 'o Aviso foi enviado com sucesso');
                   return $this->redirect($this->generateUrl('meusAvisos'));                      
               }                             
           }   
       }     
       return $this->render('PmcIntranetBundle:Aviso:crudAviso.html.twig',array(
                         'formulario' => $formulario->createView(),   
                               'data' => $data )); 
    }    
    
    /*
     * Ajax para autocompletar el usuario
     */
    public function autocompletarUsuarioAction(Request $request)
    {
         if (!($this->getRequest()->isXmlHttpRequest()))
            return $this->render('PmcIntranetBundle:Templates:prohibido.html.twig');
         
         $frase = $request->request->get('frase');  
         $usuario = $this->getUser();
         $secretaria = $usuario->getSecretaria();
         
         if ($this->get('security.context')->isGranted('ROLE_SECRETARIO'))
         {
            $usuarios = $this->getDoctrine()->getManager()
                          ->createQuery( 'SELECT u.id, u.nome, u.email, u.foto, s.nome as secretaria
                                          FROM PmcIntranetBundle:Usuario u
                                          JOIN u.secretaria s
                                          LEFT JOIN u.role r
                                          WHERE u.ativo = :ativo  
                                          AND ( r.id != 6)
                                          AND ( u != :user)
                                          AND (u.nome LIKE :frase )
                                          ORDER BY u.nome ASC' )
                          ->setParameters(array('ativo' => true,
                                                 'user' => $usuario,
                                                'frase' => "%$frase%"))
                          ->setMaxResults(15)
                          ->getResult();             
         }
         elseif ($this->get('security.context')->isGranted('ROLE_ADJUNTO'))
         {
            $usuarios = $this->getDoctrine()->getManager()
                          ->createQuery( 'SELECT u.id, u.nome, u.email, u.foto, s.nome as secretaria
                                          FROM PmcIntranetBundle:Usuario u
                                          JOIN u.secretaria s
                                          LEFT JOIN u.role r
                                          WHERE u.ativo = :ativo  
                                          AND ( (r.id != 3) or ( (r.id = 3) AND (s = :secretaria) ) )
                                          AND ( r.id != 4)
                                          AND ( r.id != 6)
                                          AND ( u != :user)
                                          AND (u.nome LIKE :frase )
                                          ORDER BY u.nome ASC' )
                          ->setParameters(array('ativo' => true,
                                                 'user' => $usuario,
                                           'secretaria' => $secretaria,
                                                'frase' => "%$frase%"))
                          ->setMaxResults(15)
                          ->getResult();
         } 
         else // ROLE_USER
         {
            $usuarios = $this->getDoctrine()->getManager()
                          ->createQuery( 'SELECT u.id, u.nome, u.email, u.foto, s.nome as secretaria
                                          FROM PmcIntranetBundle:Usuario u
                                          JOIN u.secretaria s
                                          LEFT JOIN u.role r
                                          WHERE u.ativo = :ativo  
                                          AND ( (r.id != 2) or ( (r.id = 2) AND (s = :secretaria) ) )
                                          AND (r.id != 3) 
                                          AND ( r.id != 4)
                                          AND ( r.id != 6)
                                          AND ( u != :user)
                                          AND (u.nome LIKE :frase )
                                          ORDER BY u.nome ASC' )
                          ->setParameters(array('ativo' => true,
                                                 'user' => $usuario,
                                           'secretaria' => $secretaria,
                                                'frase' => "%$frase%"))
                          ->setMaxResults(15)
                          ->getResult();
         }
         
         die(json_encode($usuarios));
    }
}