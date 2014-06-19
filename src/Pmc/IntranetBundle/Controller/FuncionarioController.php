<?php

namespace Pmc\IntranetBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Pmc\IntranetBundle\Entity\Usuario;
use Pmc\IntranetBundle\Form\FiltrosPublicacoesType;
use Pmc\IntranetBundle\Form\UsuarioType;
use Pmc\IntranetBundle\Form\MeuPerfilType;
use Pmc\IntranetBundle\Form\CambiarClaveType;

class FuncionarioController extends Controller
{
    /*
     * Lista los Funcionarios existentes en el sistema
     */
    public function listarFuncionariosAction(Request $request)
    {           
        $data = $this->get('serviciosComunes')->rutinaInicio();
        
        $data['titulo'] = 'Funcionários';
        $data['uri'] = $request->getUri(); 

        $formulario = $this->createForm(new FiltrosPublicacoesType());
        $data['uri'] = $request->getUri(); 
        // SOLO SI EL USUARIO ES ADMIN O PRENSA PUEDE FILTRAR FUNCIONARIOS POR STATUS        
        $data['filtroStatus'] =  !($this->get('security.context')->isGranted('ROLE_PRENSA'))?
                                  'oculto':'';
        
        $filtros = array();
        if ($request->isMethod('POST'))
        {        
           $filtros = $request->request->get('form');
           //die(json_encode($filtros));
           $formulario->bind($request); 
        }  
        $filtros['filtroStatus'] = $data['filtroStatus'];
        $firstResult =(isset($filtros['puntero']))? $filtros['puntero']: 0;
        
        $filtros['user'] = $this->getUser();
        $query = $this->getDoctrine()->getRepository('PmcIntranetBundle:Usuario')
                                                ->getUsuariosFiltrados($filtros);      
        
        $data['usuarios'] = $this->get('serviciosComunes')
                                 ->paginador($query, $firstResult);
        
        // EXAMINAMOS SI LA PETICION VIENE DE AJAX
        if (!($this->getRequest()->isXmlHttpRequest()))  
            return $this->render('PmcIntranetBundle:Usuario:listaUsuarios.html.twig',
                    array('formulario' => $formulario->createView(), 
                                'data' => $data ));
        else
            return $this->render('PmcIntranetBundle:Usuario:usuarios.html.twig',
                                    array('data' => $data ));
    }
    
    /*
     * Método para Insertar Nuevo Usuario
     */
    public function novoFuncionarioAction(Request $request)
    {
       // CHEQUEO DE PERMISOS
       if ( !($this->get('security.context')->isGranted('ROLE_PRENSA')) )
            return $this->render('PmcIntranetBundle:Templates:prohibido.html.twig');
              
       $data = $this->get('serviciosComunes')->rutinaInicio();
       $data['titulo'] = 'Funcionário';
       $data['accion'] = 'novo';
       $data['uri'] = $request->getUri();
       
       $usuario = new Usuario();

       //Seleccionamos los roles que puede asignar según su nivel
       $roles = $this->getDoctrine()->getManager()
                     ->createQuery('SELECT r
                                    FROM PmcIntranetBundle:Role r
                                    WHERE r.id <= (SELECT MAX(rl.id)
                                                   FROM PmcIntranetBundle:Usuario u                                    
                                                   JOIN u.role rl
                                                   WHERE u = :user)
                                    ORDER BY r.id ASC')
                     ->setParameter('user', $this->getUser())                     
                     ->getResult();
       
       $formulario = $this->createForm(new UsuarioType($roles), $usuario);
             
       if ($request->isMethod('POST'))
       {   
           $formulario->bind($request); 
           
           if ($formulario->isValid()) 
           {  // Si hay imagen         
              if ($usuario->getFoto() != null) 
              {
                 $path_image = $this->container->getParameter('path_retratos');
                 $usuario->uploadFoto($path_image);
                 // Ajustamos el tamaño de la imagen subida a máximo 150x150pxls
                 $rutaCompleta = $path_image.$usuario->getFoto();
                 $this->get('serviciosComunes')->ajustarImagen($rutaCompleta, 150, 150);
                 $this->get('serviciosComunes')->recortarImagen($rutaCompleta, 150, 150);
              }
              
              $usuario = $this->_valoresIniciales($usuario);
              
              // ACTUALIZAMOS EN BD
              $error = false;
              $em = $this->getDoctrine()->getManager();
              $em->persist($usuario);
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
                $data['module'] = 'Funcionario';
                $data['description'] = 'Novo Usuário id: '.$usuario->getId().
                                       ', "'.substr($usuario->getNome(), 0, 20).'...".';
                $this->get('serviciosComunes')->cadastrarLog($data); 
                
                $this->get('serviciosComunes')->enviarCorreioAtivacao($usuario);
                
                // ACTUALIZACIÓN SATISFACTORIA REDIRECCIONAMOS A LISTADO 
                $this->get('session')->getFlashBag()
                                     ->add('info', 'o Usuário foi criado com sucesso'); 
                
                return $this->redirect($this->generateUrl('funcionarios'));  
              }  
           }   
       }     
       return $this->render('PmcIntranetBundle:Usuario:crudUsuario.html.twig',array(
                         'formulario' => $formulario->createView(),   
                               'data' => $data )); 
    }     
    
    /*
     * Método para Editar Usuario id
     */
    public function editarFuncionarioAction($id)
    {
       // CHEQUEO DE PERMISOS
       if ( !($this->get('security.context')->isGranted('ROLE_PRENSA')) and
            !($id === null)   )
            return $this->render('PmcIntranetBundle:Templates:prohibido.html.twig');
              
       $data = $this->get('serviciosComunes')->rutinaInicio();
       $data['titulo'] = 'Funcionário';
       $data['accion'] = 'editar';
              
       $usuario = $this->getDoctrine()
                       ->getRepository('PmcIntranetBundle:Usuario')
                       ->find($id);
       if (!$usuario)  return $this->render('PmcIntranetBundle:Templates:prohibido.html.twig');
       
       //Seleccionamos los roles que puede asignar según su nivel
       $roles = $this->getDoctrine()->getManager()
                     ->createQuery('SELECT r
                                    FROM PmcIntranetBundle:Role r
                                    WHERE r.id <= (SELECT MAX(rl.id)
                                                   FROM PmcIntranetBundle:Usuario u                                    
                                                   JOIN u.role rl
                                                   WHERE u = :user)
                                    ORDER BY r.id ASC')
                     ->setParameter('user', $this->getUser())                     
                     ->getResult();
       
       $formulario = $this->createForm(new UsuarioType($roles), $usuario);
       
       $request = $this->getRequest();       
       $data['uri'] = $request->getUri();
       
       if ($request->isMethod('POST'))
       {        
           $imagemOriginal = $formulario->getData()->getFoto();
           $emailOriginal = $formulario->getData()->getEmail();
                   
           $formulario->bind($request); 
           
           if ($formulario->isValid()) 
           {           
              if (null == $usuario->getFoto()) 
              {  // La imagen original no se modifica, recuperar su ruta
                 $usuario->setFoto($imagemOriginal);
              } 
              else 
              {  // La imagen se ha modificado              
                 $path_image = $this->container->getParameter('path_retratos');
                 $usuario->uploadFoto($path_image);
                 // Ajustamos el tamaño de la imagen subida a máximo 193x101pxls
                 $rutaCompleta = $path_image.$usuario->getFoto();
                 $this->get('serviciosComunes')->ajustarImagen($rutaCompleta, 150, 150);
                 $this->get('serviciosComunes')->recortarImagen($rutaCompleta, 150, 150);
                 // Borrar la imagen anterior
                 @unlink($path_image.$imagemOriginal);
              }
              
              if ($emailOriginal != $usuario->getEmail())
              {
                  $user =  explode('@', $usuario->getEmail());        
                  $usuario->setUser($user[0]); // Establecemos el usuario a partir del email
              }
              
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
                         $error='Email ou Usuário duplicado no Servidor';                 
                      else $error = $e->getMessage();           
                      
                $this->get('session')->getFlashBag()->add('error', $error);
              } 
              
              if (!$error)
              {
                // Registramos en bitácora
                $data['action'] = 'UPDATE';
                $data['module'] = 'Funcionario';
                $data['description'] = 'Edição do Usuário id: '.$usuario->getId().
                                       ', "'.substr($usuario->getNome(), 0, 20).'...".';
                $this->get('serviciosComunes')->cadastrarLog($data); 
                
                // ACTUALIZACIÓN SATISFACTORIA REDIRECCIONAMOS A Funcionario
                $this->get('session')->getFlashBag()->add('info', 'o Usuário foi atualizado com sucesso');
                return $this->redirect($this->generateUrl('funcionarios'));  
              }  
           }   
       }     
       return $this->render('PmcIntranetBundle:Usuario:crudUsuario.html.twig',array(
                         'formulario' => $formulario->createView(),   
                               'data' => $data )); 
    }    
    
    /*
     * Método para Editar Mi Perfil Usuario id
     */
    public function perfilFuncionarioAction($user)
    {
       $usuario = $this->getDoctrine()
                       ->getRepository('PmcIntranetBundle:Usuario')
                       ->findOneByUser($user);
       if (!$usuario or ($this->getUser() != $usuario) )  // Solo el mismo usuario puede entrar
          return $this->render('PmcIntranetBundle:Templates:prohibido.html.twig');
       
       $data = $this->get('serviciosComunes')->rutinaInicio();
       $data['titulo'] = 'Meu Cadastro';
       $data['accion'] = 'editar';
       $data['usuario'] = $usuario;

       $formulario = $this->createForm(new MeuPerfilType(), $usuario);
       
       $request = $this->getRequest();
       if ($request->isMethod('POST'))
       {        
           $imagemOriginal = $formulario->getData()->getFoto();
           
           $formulario->bind($request); 
           
           if ($formulario->isValid()) 
           {           
              if (null == $usuario->getFoto()) 
              {  // La imagen original no se modifica, recuperar su ruta
                 $usuario->setFoto($imagemOriginal);
              } 
              else 
              {  // La imagen se ha modificado              
                 $path_image = $this->container->getParameter('path_retratos');
                 $usuario->uploadFoto($path_image);
                 // Ajustamos el tamaño de la imagen subida a máximo 193x101pxls
                 $rutaCompleta = $path_image.$usuario->getFoto();
                 $this->get('serviciosComunes')->ajustarImagen($rutaCompleta, 150, 150);
                 $this->get('serviciosComunes')->recortarImagen($rutaCompleta, 150, 150);
                 // Borrar la imagen anterior
                 @unlink($path_image.$imagemOriginal);
              }
              
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
                         $error='Email ou Usuario duplicado no Servidor';                 
                      else $error = $e->getMessage();           
                      
                $this->get('session')->getFlashBag()->add('error', $error);
              } 
              
              if (!$error)
              {
                // Registramos en bitácora
                $data['action'] = 'UPDATE';
                $data['module'] = 'Funcionario';
                $data['description'] = 'Edição do perfil de Usuário id: '.$usuario->getId().
                                       ', "'.substr($usuario->getNome(), 0, 20).'..."';
                $this->get('serviciosComunes')->cadastrarLog($data);
                
                // ACTUALIZACIÓN SATISFACTORIA REDIRECCIONAMOS A BANNERS
                $this->get('session')->getFlashBag()->add('info', 'Cadastro atualizado com sucesso');
                return $this->redirect($this->generateUrl('home'));  
              }  
           }   
       }     
       return $this->render('PmcIntranetBundle:Usuario:crudPerfil.html.twig',array(
                         'formulario' => $formulario->createView(),   
                               'data' => $data )); 
    }        
    
   /*
     * Cambiar Contraseña
     */
    public function cambiarSenhaAction(Request $request)
    {               
       if ($request->isMethod('GET')) 
       {
          $cuenta= $request->query->get('conta');
          $salt= $request->query->get('geral');
          
          $usuario = $this->getDoctrine()
                          ->getRepository('PmcIntranetBundle:Usuario')
                          ->findOneBy(array('email' => $cuenta,
                                             'salt' => $salt));       
          if (!$usuario) 
             return $this->render('PmcIntranetBundle:Templates:serverMessage.html.twig', 
                         array('titulo'=>'Erro', 'mensaje' =>'Dados inválidos.' ));
          
          $formulario = $this->createForm(new CambiarClaveType(), $usuario);
       }
       
       if ($request->isMethod('POST'))
       {  
          $formulario = $this->createForm(new CambiarClaveType());
          $formulario->bind($request);            
           
          if ($formulario->isValid())
          {  
             $form = $formulario->getData(); //OBTENEMOS LOS VALORES DEL FORMULARIO
             // Identificamos al usuario
             $usuario = $this->getDoctrine()
                             ->getRepository('PmcIntranetBundle:Usuario')
                             ->findOneByEmail($form['email']); 
             if (!$usuario) 
                 return $this->render('PmcIntranetBundle:Templates:serverMessage.html.twig', 
                         array('titulo'=>'Erro', 'mensaje' =>'Email inválido.' ));
                          
             $encoder = $this->get('security.encoder_factory')
                             ->getEncoder($usuario);
            
             $usuario->setSalt(md5(time()));
             $passwordCodificado = $encoder->encodePassword($form['senha'],
                                                            $usuario->getSalt());
             $usuario->setSenha($passwordCodificado);
             $usuario->setAtivo(true);

             $em = $this->getDoctrine()->getManager();             
             try {       
                $em->flush();
             } catch (\Exception $e) { // Atrapa Error del servidor
               return $this->render('PmcIntranetBundle:Templates:serverMessage.html.twig', 
                         array('titulo'=>'Erro', 'mensaje' =>$e->getMessage() ));                 
             }             
             // Registramos en bitácora
             $data['usuario'] = $usuario;
             $data['action'] = 'UPDATE';
             $data['module'] = 'Access';
             $data['description'] = 'Senha alterada com sucesso.';
             $this->get('serviciosComunes')->cadastrarLog($data);      
             
             $data['titulo']="Sucesso";
             $data['mensaje']="Sua senha foi definida com sucesso no sistema.";                          
             return $this->render('PmcIntranetBundle:Templates:serverMessage.html.twig', $data);
          } 
       }
       return $this->render('PmcIntranetBundle:Access:ativar.html.twig',
                      array('formulario' => $formulario->createView()));
    }          
    
    /*
     * Método para Iniciar a un usuario y crear una clave y salt inicial
     */
    private function _valoresIniciales($usuario)
    {
       $usuario->setAtivo(\FALSE); // CREAMOS AL USUARIO INACTIVO PARA ACTIVARLO VIA EMAIL
           
       $user =  explode('@', $usuario->getEmail()); 
       
       $usuario->setUser($user[0]); // Establecemos el usuario a partir del email
       
       // CODIFICAMOS LA CLAVE INICIAL
       $encoder = $this->get('security.encoder_factory')
                       ->getEncoder($usuario);
       $usuario->setSalt(md5(time()));
       
       $passwordCodificado = $encoder->encodePassword('24121973', $usuario->getSalt() );  

       $usuario->setSenha($passwordCodificado); 
        
       return $usuario;
    }  
    

}