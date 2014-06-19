<?php

namespace Pmc\IntranetBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Pmc\IntranetBundle\Entity\Comentario;
use Pmc\IntranetBundle\Entity\Publicacao;
use Pmc\IntranetBundle\Form\PostarMensagemType;

class HomeController extends Controller
{
    /*
     * Carga el Home del usuario
     */
    public function homeAction(Request $request)
    {
        $data = $this->get('serviciosComunes')->rutinaInicio();        
        $data['uri'] = $request->getUri(); 
        
        //Si la solicitud es VIA AJAX
        if ($this->getRequest()->isXmlHttpRequest())        
           $filtros = $request->request->get('form');
        else $filtros =array();
        
        //Buscamos las publicaciones que debemos ver
        $query = $this->getDoctrine()
                      ->getRepository('PmcIntranetBundle:Publicacao')
                      ->getPublicacoesHomeUser($this->getUser()); 
        
        $firstResult =(isset($filtros['puntero']))? $filtros['puntero']: 0;
        $data['publications'] = $this->get('serviciosComunes')
                                     ->paginador($query, $firstResult);
        
        $publicacao = new Publicacao();        
        $formPostarMensagem = $this->createForm(new PostarMensagemType(), $publicacao); 
        
        // SI LA SOLICITUD ES POST Y NO VIA AJAX ATIENDE POST DE MENSAJE        
        if (($request->isMethod('POST')) and !($this->getRequest()->isXmlHttpRequest()))
        {        
           $data['post'] = $request->request->get('form');
           
           $formPostarMensagem->bind($request); 
           
           if ($formPostarMensagem->isValid())
           { 
              $data['publicacao'] = $publicacao;
              $inserir = $this->_inserirPublicacao($data);
              if ( $inserir === true ) // ALL IS GOOD REDIRECT
              {
                 // Registramos en bitácora
                 $data['action'] = 'INSERT';
                 $data['module'] = 'Home';
                 $data['description'] = 'Nova publicação id: '.$publicacao->getId().
                                       ', "'.substr($publicacao->getTitulo(), 0, 20).
                                       '...", tipo: "Mensagem".';
                 $this->get('serviciosComunes')->cadastrarLog($data); 
                 
                 return $this->redirect($request->getUri());
              }
           }           
        }   
        
        // EXAMINAMOS SI LA PETICION VIENE DE AJAX
        if (!($this->getRequest()->isXmlHttpRequest()))           
           return $this->render('PmcIntranetBundle:Home:home.html.twig',array(
                         'formPostarMensagem' => $formPostarMensagem->createView(),                        
                                       'data' => $data ));        
        else
            return $this->render('PmcIntranetBundle:Templates:publications.html.twig',
                                    array('data' => $data ));   
    }
    
    /*
     * Método para aplicar filtro rojo
     */
    public function filtroRojoAction(Request $request)
    {
        if (!$request->isMethod('POST')) 
           return $this->redirect($this->generateUrl('home'), 301);
                
        $data = $this->get('serviciosComunes')->rutinaInicio();        
        $data['uri'] = $request->getUri(); 
        
        $filtros = $request->request->get('form');
        
        $data['patron'] = $filtros['patron'];
        
        //Buscamos las publicaciones que debemos ver
        $query = $this->getDoctrine()
                      ->getRepository('PmcIntranetBundle:Publicacao')
                      ->getPublicacoesHomeUser($this->getUser(), $filtros); 
        
        $firstResult =(isset($filtros['puntero']))? $filtros['puntero']: 0;
        $data['publications'] = $this->get('serviciosComunes')
                                     ->paginador($query, $firstResult);
        
        $publicacao = new Publicacao();        
        $formPostarMensagem = $this->createForm(new PostarMensagemType(), $publicacao); 
                
        // EXAMINAMOS SI LA PETICION VIENE DE AJAX
        if (!($this->getRequest()->isXmlHttpRequest()))           
           return $this->render('PmcIntranetBundle:Home:homeRojo.html.twig',array(
                         'formPostarMensagem' => $formPostarMensagem->createView(),                        
                                       'data' => $data ));        
        else // Mostrar Mais Rojo
            return $this->render('PmcIntranetBundle:Templates:publications.html.twig',
                                    array('data' => $data ));           
    }

    /*
     * Método privado para Insertar Publicación
     */
    private function _inserirPublicacao($data)
    {
        $publicacao = $data['publicacao'];
        // Proceso Especial si hay video incrustado
        if (trim($publicacao->getVideo()) != null)
        {
            $video = $this->get('serviciosComunes')->modificarVideo($publicacao->getVideo());                      
            
            if ($video == null)
            {
                $this->get('session')->getFlashBag()->add('error', 'Vídeo Inválido');
                return false;
            }
            else $publicacao->setVideo($video);           
        }
        
        // Proceso Especial si hay imagen
        if ($publicacao->getImagem() != null)
        {
            $path_image = $this->container->getParameter('path_imagens');
            $publicacao->uploadImagem($path_image);
            // Ajustamos el tamaño de la imagen subida a máximo 193x101pxls
            $rutaCompleta = $path_image.$publicacao->getImagem();
            $this->get('serviciosComunes')->ajustarImagen($rutaCompleta);
        }      
        
        $publicacao->setUsuario($this->getUser());
        $publicacao->setTipo($this->getDoctrine()
                                  ->getRepository('PmcIntranetBundle:TipoPublicacao')
                                  ->find(1));
        // ACTUALIZAMOS EN BD
        $error = false;
        $em = $this->getDoctrine()->getManager();       
        $em->persist($publicacao);
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
          return false;
        }

        // ACTUALIZACIÓN SATISFACTORIA REDIRECCIONAMOS A BANNERS
//// INSERTAR EN BITACORA              
        $this->get('session')->getFlashBag()->add('info', 'Publicado com sucesso');            
        return true;
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
           $idPublicacao = $request->request->get('idPublicacao');
           
           $comentario = new Comentario();
           
           $publicacao = $this->getDoctrine()
                              ->getRepository('PmcIntranetBundle:Publicacao')
                              ->find($idPublicacao);
           
           $comentario->setComentario($postar);
           $comentario->setPublicacao($publicacao);
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
           $data['module'] = 'Home';
           $data['description'] = 'Novo comentário id: '.$comentario->getId().
                                  ', "'.substr($comentario->getComentario(), 0, 20).'...",'.
                                  ' em publicação id: '.$publicacao->getId().', "'.
                                  substr($publicacao->getTitulo(), 0, 20).'...".'; 
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
           $idPublicacao = $request->request->get('idPublicacao');           
           /*
            * Buscamos los comentarios de la publicación
            */
           $em = $this->getDoctrine()->getManager();
           $query = $em->createQuery('SELECT c, u
                                      FROM PmcIntranetBundle:Comentario c
                                      JOIN c.usuario u
                                      JOIN c.publicacao p                                      
                                      WHERE p.id = :idPublicacao
                                      ORDER BY c.data')
                       ->setParameter('idPublicacao', $idPublicacao);
    
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
       
           $comentario = $em->getRepository('PmcIntranetBundle:Comentario')
                            ->find($id);           
           
           if (!$comentario) die(json_encode(array('error'=>'Objeto não encontrado')));
           
           $publicacion = $comentario->getPublicacao();
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
           // Logró ser eliminado
           // Registramos en bitácora
           $data['action'] = 'DELETE';
           $data['module'] = 'Home';
           $data['description'] = 'Exclusão do comentário id: '.$id.
                                  ', "'.substr($clon, 0, 20).'..." em publicação id: '.
                                  $publicacion->getId().', "'.
                                  substr($publicacion->getTitulo(), 0, 20).'...".';
           $this->get('serviciosComunes')->cadastrarLog($data);
          
           // Refrescamos los comentarios
           $query = $em->createQuery('SELECT c, u
                                      FROM PmcIntranetBundle:Comentario c
                                      JOIN c.usuario u
                                      JOIN c.publicacao p                                      
                                      WHERE p = :publicacao
                                      ORDER BY c.data DESC')
                       ->setParameter('publicacao', $publicacion);
    
           $p['comentarios'] = $query->getResult();
        
           
           $html = $this->renderView('PmcIntranetBundle:Templates:comentarios.html.twig',array(
                                'p'=>$p )) ;
           
           die(json_encode(array('id' => $publicacion->getId(),
                               'html' => $html)));   
       }       
    }

    /*
     * Método AJAX para excluir una publicación id
     */
    public function excluirPublicacaoAction(Request $request)
    {
       // Verifica si la petición No es de AJAX  
       if (!($this->getRequest()->isXmlHttpRequest())) 
          return $this->render('PmcIntranetBundle:Templates:prohibido.html.twig');
       
       if ($request->isMethod('POST')) 
       {  
           $id = $request->request->get('idPublicacao');           

           // Buscamos la publicación
           $em = $this->getDoctrine()->getManager();
       
           $publicacao = $em->getRepository('PmcIntranetBundle:Publicacao')
                            ->find($id);           
           
           if (!$publicacao) die(json_encode(array('error'=>'Objeto não encontrado')));
           
           $clon = $publicacao->getTitulo(); //Clonamos titulo antes de eliminarlo
           $tipo = $publicacao->getTipo()->getTipo();
           
           // Si la publicación posee imagen
           if (strlen(trim($publicacao->getImagem())) > 0)
           {   // Eliminamos la imagen del Servidor
               $publicacao->removerImagem($this->container->getParameter('path_imagens')); 
           }
           
           $error='';
           $em->remove($publicacao);            
           try { $em->flush();
           } catch (\Exception $e) { // Atrapa Error del servidor
                    if(stristr($e->getMessage(), 'Foreign key violation'))
                      $error = 'Verifique que no posea Dependencias.';                   
                    else if(stristr($e->getMessage(), 'Unique violation'))                  
                      $error='Objeto duplicado no Servidor'; 
                    else $error = $e->getMessage();               
            die(json_encode(array('error'=>$error)));   
           } 
           // Logró ser eliminado
           // Registramos en bitácora
           $data['action'] = 'DELETE';
           $data['module'] = 'Home';
           $data['description'] = 'Exclusão da publicação id: '.$id.
                                  ', tipo: "'.$tipo.
                                  '", "'.substr($clon, 0, 20).'...".';
           $this->get('serviciosComunes')->cadastrarLog($data);
           
           die(json_encode(array('id' => $id,
                               'html' => '')));   
       }
       
    }    
  
}