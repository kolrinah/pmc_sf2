<?php

namespace Pmc\IntranetBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Pmc\IntranetBundle\Entity\Banner;
use Pmc\IntranetBundle\Form\BannerType;

class AdministrativoController extends Controller
{
    /*
     * Lista los Banners existentes en el sistema
     */
    public function adminBannersAction(Request $request)
    {       
        // CHEQUEO DE PERMISOS
        if ( !($this->get('security.context')->isGranted('ROLE_PRENSA')) and
             !($this->get('security.context')->isGranted('ROLE_ADMIN')) )
            return $this->render('PmcIntranetBundle:Templates:prohibido.html.twig');
        
        $data = $this->get('serviciosComunes')->rutinaInicio();
        
        $data['titulo'] = 'Banners';
        $data['uri'] = $request->getUri(); 

       // $defaultData = array();
        $formulario = $this->createFormBuilder()
                
        ->add('patron', 'text', array('required' => false,
                                          'attr' => array('placeholder'=>'Escreva seu padrão de pesquisa',
                                                                'class'=>'form-control', 
                                                            'maxlength'=>'20', 
                                                                'title'=>'Padrão de pesquisa')))
                
        ->add('activo', 'choice', array(   'label' => 'Filtrar por:',
                                            'required' => true,
                                             'choices' => array('0' => 'Mostrar solo Ativos',
                                                                '1' => 'Mostrar solo Inativos',
                                                                '3' => 'Todos'),
                                            'multiple' => false,
                                            'expanded' => true ))
                
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
        
        $activo = (isset($filtros['activo']) and ($filtros['activo']) != '')? 
                                 $filtros['activo']:3;
        
        $firstResult =(isset($filtros['puntero']))? $filtros['puntero']: 0;
                
        $em = $this->getDoctrine()->getManager();        
        $data['bannerAdmin'] = $em->createQuery('SELECT b
                                                 FROM PmcIntranetBundle:Banner b 
                                                 WHERE (b.nome LIKE :patron or
                                                        b.url LIKE :patron)
                                                 AND b.ativo != :activo
                                                 ORDER BY b.ordem ASC, b.id DESC')
                                  ->setParameters(array('patron' => "%$patron%",
                                                        'activo' => $activo))
                                  ->setFirstResult($firstResult)
                                  ->setMaxResults(5)                   
                                  ->getResult();        
        
        // EXAMINAMOS SI LA PETICION VIENE DE AJAX
        if (!($this->getRequest()->isXmlHttpRequest()))  
            return $this->render('PmcIntranetBundle:Administrativo:adminBanners.html.twig',
                    array('formulario' => $formulario->createView(), 
                                'data' => $data ));
        else
            return $this->render('PmcIntranetBundle:Administrativo:banners.html.twig',
                                    array('data' => $data ));
    }

    /*
     * Método AJAX para Activar o desactivar Banner
     */
    public function activarBannerAction(Request $request)
    {
       // Verifica si la petición No es de AJAX  
       if (!($this->getRequest()->isXmlHttpRequest())) 
          return $this->render('PmcIntranetBundle:Templates:prohibido.html.twig');
       
       if ($request->isMethod('POST')) 
       {  
           $idBanner = $request->request->get('idBanner'); 
           $activar = $request->request->get('activar'); 

           // Buscamos el Banner
           $em = $this->getDoctrine()->getManager();
       
           $banner = $em->getRepository('PmcIntranetBundle:Banner')
                        ->find($idBanner);
           
           if (!$banner) die(json_encode(array('error'=>'Objeto não encontrado')));
                      
           $banner->setAtivo($activar);
           
           $error='';                 
           try { $em->flush();
           } catch (\Exception $e) { // Atrapa Error del servidor
                    if(stristr($e->getMessage(), 'Foreign key violation'))
                      $error = 'Verifique que no posea Dependencias.';                   
                    else if(stristr($e->getMessage(), 'Unique violation'))                  
                      $error='Objeto duplicado no Servidor'; 
                    else $error = $e->getMessage();               
            die(json_encode(array('error'=>$error)));   
           } 
           // Logró ser Actualizado
         
//BITACORA $this->_bitacoraEliminarProyecto($datos);
           
           die(json_encode(array('html' => $this->_refrescarBanners() )));     
       }       
    }    
    
    /*
     * Método AJAX para Actualizar orden de los Banner
     */
    public function actualizaOrdenAction(Request $request)
    {
       // Verifica si la petición No es de AJAX  
       if (!($this->getRequest()->isXmlHttpRequest())) 
          return $this->render('PmcIntranetBundle:Templates:prohibido.html.twig');
       
       if ($request->isMethod('POST')) 
       {  
           $orden = $request->request->get('orden'); 
           
           // Buscamos todos los Banners
           $em = $this->getDoctrine()->getManager();           
           $banner = array(); $i = 1;
           foreach ($orden as $o)
           {
                $banner[$i] = $em->getRepository('PmcIntranetBundle:Banner')
                                 ->find($o);
                if ($banner[$i]) $banner[$i]->setOrdem($i);
                $i++;
           }           
           $error='';           
           try { $em->flush();
           } catch (\Exception $e) { // Atrapa Error del servidor
                    if(stristr($e->getMessage(), 'Foreign key violation'))
                      $error = 'Verifique que no posea Dependencias.';                   
                    else if(stristr($e->getMessage(), 'Unique violation'))                  
                      $error='Objeto duplicado no Servidor'; 
                    else $error = $e->getMessage();               
            die(json_encode(array('error'=>$error)));   
           } 
           // Logró ser Actualizado
//BITACORA $this->_bitacoraEliminarProyecto($datos);
           
           die(json_encode(array('html' => $this->_refrescarBanners() )));   
       }       
    } 
    
   /*
     * Método AJAX para excluir un Banner
     */
    public function excluirBannerAction(Request $request)
    {
       // Verifica si la petición No es de AJAX  
       if (!($this->getRequest()->isXmlHttpRequest())) 
          return $this->render('PmcIntranetBundle:Templates:prohibido.html.twig');
       
       if ($request->isMethod('POST')) 
       {  
           $idBanner = $request->request->get('idBanner');           

           // Buscamos el Banner
           $em = $this->getDoctrine()->getManager();
       
           $banner = $em->getRepository('PmcIntranetBundle:Banner')
                        ->find($idBanner);           
           
           if (!$banner) die(json_encode(array('error'=>'Objeto não encontrado')));
           
           // Si el banner posee imagen
           if (strlen(trim($banner->getImagem())) > 0)
           {   // Eliminamos la imagen del Servidor
               $banner->removerImagem($this->container->getParameter('path_banners')); 
           }
           
           $error='';
           $em->remove($banner);            
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
//BITACORA $this->_bitacoraEliminarProyecto($datos);
           
           die(json_encode(array('html' => $this->_refrescarBanners() )));      
       }       
    }        
    
    /*
     * Método para Insertar Nuevo Banner
     */
    public function novoBannerAction(Request $request)
    {
       // CHEQUEO DE PERMISOS
       if ( !($this->get('security.context')->isGranted('ROLE_PRENSA')) and
            !($this->get('security.context')->isGranted('ROLE_ADMIN')))
            return $this->render('PmcIntranetBundle:Templates:prohibido.html.twig');
              
       $data = $this->get('serviciosComunes')->rutinaInicio();
       $data['titulo'] = 'Banners';
       $data['accion'] = 'novo';
              
       $banner = new Banner();
       
       $formulario = $this->createForm(new BannerType(), $banner);
             
       if ($request->isMethod('POST'))
       {   
           $formulario->bind($request); 
           
           if ($formulario->isValid()) 
           {  // Si hay imagen         
              if ($banner->getImagem() != null) 
              {
                 $path_image = $this->container->getParameter('path_banners');
                 $banner->uploadImagem($path_image);
                 // Ajustamos el tamaño de la imagen subida a máximo 193x101pxls
                 $rutaCompleta = $path_image.$banner->getImagem();
                 $this->get('serviciosComunes')->ajustarImagen($rutaCompleta, 193, 101);                 
              }
              
              // ACTUALIZAMOS EN BD
              $error = false;
              $em = $this->getDoctrine()->getManager();
              $em->persist($banner);
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
//// INSERTAR EN BITACORA              
                // ACTUALIZACIÓN SATISFACTORIA REDIRECCIONAMOS A BANNERS
                $this->get('session')->getFlashBag()->add('info', 'o Banner foi criado com sucesso');
                return $this->redirect($this->generateUrl('adminBanners'));  
              }  
           }   
       }     
       return $this->render('PmcIntranetBundle:Administrativo:crudBanner.html.twig',array(
                         'formulario' => $formulario->createView(),   
                               'data' => $data )); 
    }     
    
    /*
     * Método para Editar Banner id
     */
    public function editarBannerAction($id)
    {
       // CHEQUEO DE PERMISOS
       if ( !($this->get('security.context')->isGranted('ROLE_PRENSA')) and
            !($this->get('security.context')->isGranted('ROLE_ADMIN')) and
            !($id === null)   )
            return $this->render('PmcIntranetBundle:Templates:prohibido.html.twig');
              
       $data = $this->get('serviciosComunes')->rutinaInicio();
       $data['titulo'] = 'Banners';
       $data['accion'] = 'editar';
              
       $banner = $this->getDoctrine()
                      ->getRepository('PmcIntranetBundle:Banner')
                      ->find($id);
       if (!$banner)  return $this->render('PmcIntranetBundle:Templates:prohibido.html.twig');
       
       $formulario = $this->createForm(new BannerType(), $banner);
       
       $request = $this->getRequest();
       if ($request->isMethod('POST'))
       {        
           $imagemOriginal = $formulario->getData()->getImagem();
           
           $formulario->bind($request); 
           
           if ($formulario->isValid()) 
           {           
              if (null == $banner->getImagem()) 
              {  // La imagen original no se modifica, recuperar su ruta
                 $banner->setImagem($imagemOriginal);
              } 
              else 
              {  // La imagen se ha modificado              
                 $path_image = $this->container->getParameter('path_banners');
                 $banner->uploadImagem($path_image);
                 // Ajustamos el tamaño de la imagen subida a máximo 193x101pxls
                 $rutaCompleta = $path_image.$banner->getImagem();
                 $this->get('serviciosComunes')->ajustarImagen($rutaCompleta, 193, 101);
                 // Borrar la imagen anterior
                 @unlink($path_image.$imagemOriginal);
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
//// INSERTAR EN BITACORA              
                // ACTUALIZACIÓN SATISFACTORIA REDIRECCIONAMOS A BANNERS
                $this->get('session')->getFlashBag()->add('info', 'o Banner foi atualizado com sucesso');
                return $this->redirect($this->generateUrl('adminBanners'));  
              }  
           }   
       }     
       return $this->render('PmcIntranetBundle:Administrativo:crudBanner.html.twig',array(
                         'formulario' => $formulario->createView(),   
                               'data' => $data )); 
    }     
    
    
    /*
     * Método privado para obtener los banners ordenados por ordem
     */
    private function _refrescarBanners()
    {
        $em = $this->getDoctrine()->getManager();        
        $data['banners'] = $em->createQuery('SELECT b
                                             FROM PmcIntranetBundle:Banner b 
                                             WHERE b.ativo = :activo
                                             ORDER BY b.ordem ASC')
                              ->setParameters(array('activo' => true))
                              ->getResult();
        
        return $this->renderView('PmcIntranetBundle:Templates:banners.html.twig',
                            array('data' => $data )); 
    }
}