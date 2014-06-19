<?php

namespace Pmc\IntranetBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Pmc\IntranetBundle\Entity\Publicacao;
use Pmc\IntranetBundle\Form\PostarMensagemType;
use Pmc\IntranetBundle\Form\FiltrosPublicacoesType;
use Pmc\IntranetBundle\Form\PublicacaoType;

class SocialController extends Controller
{
    /*
     * Filtra publicaciones según el tipo introducido
     */
    public function filtrarAction($tipo)
    {        
        // VERIFICACION DEL TIPO DE PUBLICACION
        $tipo = substr($tipo, 0, -2); // Eliminamos los posibles plurales
        $em = $this->getDoctrine()->getManager();
        $query = $em->createQuery('SELECT t
                                   FROM PmcIntranetBundle:TipoPublicacao t                                  
                                   WHERE t.tipo LIKE :tipo')
                    ->setParameter('tipo', "%$tipo%")
                    ->getOneOrNullResult();
        
        if ($query == null ) // SI NO ENCUENTRA RESULTADOS ABORTA
            return $this->render('PmcIntranetBundle:Templates:prohibido.html.twig');
        $tipo = $query; // Reemplazamos $tipo por el objeto
        
        $data = $this->get('serviciosComunes')->rutinaInicio();
        
        $formulario = $this->createForm(new FiltrosPublicacoesType());
        
        $publicacao = new Publicacao();        
        $formPostarMensagem = $this->createForm(new PostarMensagemType(), $publicacao); 
        
        $request = $this->getRequest();
                
        $data['tipo'] = $tipo; 
        $data['uri'] = $request->getUri();         
        
        $data['filtroStatus'] = ( !($this->get('security.context')->isGranted('ROLE_ADJUNTO')) or
                                  ($tipo->getId() == 1) )? 'oculto':'';
                
        $filtros = array();
        if ($request->isMethod('POST'))
        {        
           $filtros = $request->request->get('form');            
           $formulario->bind($request); 
        }           
        $filtros['filtroStatus'] = $data['filtroStatus'];
        
        // Dependiendo del tipo de publicación, llamamos a la consulta
        $query = $this->getDoctrine()->getRepository('PmcIntranetBundle:Publicacao');
        
        $query = ($tipo->getId() == 1)? // SI SON MENSAJES
            $query->getMensagensByUser($this->getUser(), $filtros) :
            $query->getPublicacoesEspeciaisByUser($this->getUser(), $tipo, $filtros);    
        
        $firstResult =(isset($filtros['puntero']))? $filtros['puntero']: 0;
        $data['publications'] = $this->get('serviciosComunes')
                                     ->paginador($query, $firstResult);
        
        // EXAMINAMOS SI LA PETICION VIENE DE AJAX
        if (!($this->getRequest()->isXmlHttpRequest()))         
           return $this->render('PmcIntranetBundle:Social:publicacoesFiltradasUsuario.html.twig',                                  
                                    array('formulario' => $formulario->createView(), 
                                  'formPostarMensagem' => $formPostarMensagem->createView(),                                        
                                                'data' => $data ));  
        else
           return $this->render('PmcIntranetBundle:Templates:publications.html.twig',
                                    array('data' => $data ));   
    }
    
    /*
     * Muestra los usuarios a quienes sigo
     */
    public function minhaRedeAction(Request $request)
    {
        $data = $this->get('serviciosComunes')->rutinaInicio();
        
        $data['titulo'] = 'Minha rede';
        $data['uri'] = $request->getUri(); 
        $data['filtroStatus'] = 'oculto'; // APLICAMOS ESTE FILTRO SOLO PARA LAS PUBLICACIONES
                
        $formulario = $this->createForm(new FiltrosPublicacoesType());
        $filtros = array();
        if ($request->isMethod('POST'))
        {        
           $filtros = $request->request->get('form');   
           
           //die(json_encode($filtros));
           $formulario->bind($request); 
        }  
        
        $patron = (isset($filtros['patron']) and trim($filtros['patron']) != '')? 
                                 trim($filtros['patron']):'';
      
        $secretarias = (isset($filtros['secretarias']))? $filtros['secretarias']:
                                   $this->getDoctrine()->getManager()
                                        ->createQuery("SELECT s.id
                                                       FROM PmcIntranetBundle:Secretaria s")
                                        ->getArrayResult();
                
        //Buscamos los usuarios a quienes seguimos
        $em = $this->getDoctrine()->getManager();
        $query = $em->createQuery(
                        'SELECT u, s FROM PmcIntranetBundle:Usuario u
                         JOIN u.secretaria s                         
                         WHERE u.ativo = :ativo
                         AND :user MEMBER OF u.seguidor
                         AND ( u.nome LIKE :patron or
                               u.cargo LIKE :patron or 
                               u.email LIKE :patron
                              )
                         AND ( s.id IN (:secretarias) )
                         ORDER BY u.nome ASC')
                    ->setParameters(array('ativo' => true,
                                           'user' => $this->getUser(),
                                         'patron' => "%$patron%",
                                    'secretarias' => $secretarias))
                    ->getResult();
        
        $firstResult =(isset($filtros['puntero']))? $filtros['puntero']: 0;
        $data['usuarios'] = $this->get('serviciosComunes')
                                 ->paginador($query, $firstResult);
        
        // EXAMINAMOS SI LA PETICION VIENE DE AJAX
        if (!($this->getRequest()->isXmlHttpRequest()))         
           return $this->render('PmcIntranetBundle:Social:minhaRede.html.twig',array(
                             'formulario' => $formulario->createView(), 
                                   'data' => $data ));                
        else
           return $this->render('PmcIntranetBundle:Social:usuarios.html.twig',
                                    array('data' => $data ));
    }
    
    /*
     * Método AJAX para seguir o dejar de seguir a un usuario
     */
    public function seguirAction(Request $request)
    {
       // Verifica si la petición No es de AJAX  
       if (!($this->getRequest()->isXmlHttpRequest())) 
          return $this->render('PmcIntranetBundle:Templates:prohibido.html.twig');
       
       if ($request->isMethod('POST')) 
       {  
           $idUser = $request->request->get('idUser'); 
           $seguir = $request->request->get('seguir'); 

           // Buscamos el usuario
           $em = $this->getDoctrine()->getManager();
       
           $usuario = $em->getRepository('PmcIntranetBundle:Usuario')
                         ->find($idUser);
           
           if (!$usuario) die(json_encode(array('error'=>'Objeto não encontrado')));
           
           if ($seguir == 0)
               $usuario->removeSeguidor($this->getUser());
           else
               $usuario->addSeguidor($this->getUser());
           
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
           die(json_encode(array('id' => $idUser )));   
       }       
    }
 
    /*
     * Método para Insertar Nueva Publicación
     */
    public function novaPublicacaoAction($tipo)
    {
        // VERIFICACION DEL TIPO DE PUBLICACION
        $tipo = substr($tipo, 0, -2); // Eliminamos los posibles plurales
        $em = $this->getDoctrine()->getManager();
        $query = $em->createQuery('SELECT t
                                   FROM PmcIntranetBundle:TipoPublicacao t                                  
                                   WHERE t.tipo LIKE :tipo')
                    ->setParameter('tipo', "%$tipo%")
                    ->getOneOrNullResult();
        
        if ($query == null ) // SI NO ENCUENTRA RESULTADOS ABORTA
            return $this->render('PmcIntranetBundle:Templates:prohibido.html.twig');
        $tipo = $query; // Reemplazamos $tipo por el objeto       
           
       // VERIFICAMOS PERMISOS
       if (!($this->get('security.context')->isGranted('ROLE_ADJUNTO')) )
          return $this->render('PmcIntranetBundle:Templates:prohibido.html.twig');
       
       $data = $this->get('serviciosComunes')->rutinaInicio();
       // PREPARAMOS EL FORMULARIO
       $request = $this->getRequest(); 
       $data['tipo'] = $tipo;
       $data['usuario'] = array($this->getUser());   
       $data['uri'] = $request->getUri();
       
       $publicacao = new Publicacao();        
       $formulario = $this->createForm(new PublicacaoType($data), $publicacao);       
       
       $data['accion'] = 'Criar';
       
       if ($request->isMethod('POST'))
       {   
           $formulario->bind($request); 
           
           if ($formulario->isValid()) 
           {  
              $data['publicacao'] = $publicacao;
              $inserir = $this->_inserirPublicacao($data);
              if ( $inserir === true ) // ALL IS GOOD REDIRECT
              {
                 // Registramos en bitácora
                 $data['action'] = 'INSERT';
                 $data['module'] = 'Social';
                 $data['description'] = 'Nova publicação id: '.$publicacao->getId().
                                       ', "'.substr($publicacao->getTitulo(), 0, 20).'...", tipo: "'.
                                        $tipo->getTipo().'".';
                 $this->get('serviciosComunes')->cadastrarLog($data); 
                
                 return $this->redirect($this->generateUrl('filtrar', 
                   array('tipo'=>mb_convert_case($tipo->getTipo(), \MB_CASE_LOWER, 'utf-8'))));  
              }
           } 
       }        
       return $this->render('PmcIntranetBundle:Social:crudPublicacao.html.twig',array(
                         'formulario' => $formulario->createView(),   
                               'data' => $data )); 
    }         
    
    /*
     * Método para Editar Publicación
     */
    public function editarPublicacaoAction($tipo, $id)
    {
        // VERIFICACION DEL TIPO DE PUBLICACION
        $tipo = substr($tipo, 0, -2); // Eliminamos los posibles plurales
        $em = $this->getDoctrine()->getManager();
        $query = $em->createQuery('SELECT t
                                   FROM PmcIntranetBundle:TipoPublicacao t                                  
                                   WHERE t.tipo LIKE :tipo')
                    ->setParameter('tipo', "%$tipo%")
                    ->getOneOrNullResult();
        
        if ($query == null ) // SI NO ENCUENTRA RESULTADOS ABORTA
            return $this->render('PmcIntranetBundle:Templates:prohibido.html.twig');
        $tipo = $query; // Reemplazamos $tipo por el objeto       
           
       // VERIFICAMOS PERMISOS
       if ( !($this->get('security.context')->isGranted('ROLE_ADJUNTO')) or ($tipo->getId()==1) ) 
          return $this->render('PmcIntranetBundle:Templates:prohibido.html.twig');
       
       $data = $this->get('serviciosComunes')->rutinaInicio();
       // PREPARAMOS EL FORMULARIO
       $data['tipo'] = $tipo;
       $data['usuario'] = array($this->getUser());       
       
       $publicacao = $this->getDoctrine()
                          ->getRepository('PmcIntranetBundle:Publicacao')
                          ->find($id);
       if (!$publicacao)  return $this->render('PmcIntranetBundle:Templates:prohibido.html.twig');
       
       // VERIFICAMOS PERMISOS PARA MODIFICAR LA PUBLICACIÓN
       if (!( ( ($this->get('security.context')->isGranted('ROLE_ADJUNTO')) and 
                ($publicacao->getTipo()->getId() != 1 ) and
                ($publicacao->getUsuario()->getSecretaria() == $this->getUser()->getSecretaria()) ) or
              ( ($this->get('security.context')->isGranted('ROLE_PRENSA')) and 
               ($publicacao->getTipo()->getId() != 1 ) ) )
          ) 
          return $this->render('PmcIntranetBundle:Templates:prohibido.html.twig');       
       
       $formulario = $this->createForm(new PublicacaoType($data), $publicacao);       
       
       $request = $this->getRequest(); 
       $data['accion'] = 'Editar';
       $data['uri'] = $request->getUri();
     
       if ($request->isMethod('POST'))
       {   
           $data['imagemOriginal'] = $formulario->getData()->getImagem();
           
           $formulario->bind($request); 
           
           if ($formulario->isValid()) 
           {  
              $data['publicacao'] = $publicacao;
              $editar = $this->_editarPublicacao($data);
              if ( $editar === true ) // ALL IS GOOD REDIRECT
              {    
                 // Registramos en bitácora
                 $data['action'] = 'UPDATE';
                 $data['module'] = 'Social';
                 $data['description'] = 'Edição da publicação id: '.$publicacao->getId().
                                        ', tipo: "'.$tipo->getTipo().
                                        '", "'.substr($publicacao->getTitulo(), 0, 20).'...".';
                 $this->get('serviciosComunes')->cadastrarLog($data); 
                
                 return $this->redirect($this->generateUrl('filtrar', 
                      array('tipo'=>mb_convert_case($tipo->getTipo(), \MB_CASE_LOWER, 'utf-8'))));
              }
           }   
       }     
       return $this->render('PmcIntranetBundle:Social:crudPublicacao.html.twig',array(
                         'formulario' => $formulario->createView(),   
                               'data' => $data )); 
    }    
    
    /*
     * Método privado para insertar la publicación
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
        
                // Si hay imagen         
        if ($publicacao->getImagem() != null) 
        {
           $path_image = $this->container->getParameter('path_imagens');
           $publicacao->uploadImagem($path_image);
           // Ajustamos el tamaño de la imagen subida a máximo 800x600px
           $rutaCompleta = $path_image.$publicacao->getImagem();
           $this->get('serviciosComunes')->ajustarImagen($rutaCompleta);                 
        }
        
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
        
        // INSERCIÓN SATISFACTORIA
/// BITACORA
        $this->get('session')->getFlashBag()->add('info', 'A publicação foi criada com sucesso');            
        return true;
    }
    
    /*
     * Método privado para editar la publicación
     */
    private function _editarPublicacao($data)
    {
        $publicacao = $data['publicacao'];
        $imagemOriginal = $data['imagemOriginal'];
        
        if (null == $publicacao->getImagem()) 
        {   // La imagen original no se modifica, recuperar su ruta
            $publicacao->setImagem($imagemOriginal);
        }
        else               
        {  // La imagen se ha modificado
           $path_image = $this->container->getParameter('path_imagens');
           $publicacao->uploadImagem($path_image);
           // Ajustamos el tamaño de la imagen subida a máximo 800x600px
           $rutaCompleta = $path_image.$publicacao->getImagem();
           $this->get('serviciosComunes')->ajustarImagen($rutaCompleta);  
           // Borrar la imagen anterior
           @unlink($path_image.$imagemOriginal);
        }        
        
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
          return false;
        } 
        
        // INSERCIÓN SATISFACTORIA
/// BITACORA
        $this->get('session')->getFlashBag()->add('info', 'A publicação foi editada com sucesso');            
        return true;
    }      
}