<?php

namespace Pmc\IntranetBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Security\Core\SecurityContext;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;

class AccessController extends Controller
{
    public function loginAction(Request $request)
    {           
        if (($this->getRequest()->getPathInfo() =='/login') && 
            ($this->get('security.context')->isGranted('ROLE_USER')))
        {
           return $this->redirect($this->generateUrl('home'), 301);          
        }
       
        /*$pdo=$this->get('session.handler.pdo');
        $pdo->gc(1200); // Establece a 20 minutos la recolección de Basura en sesiones */       
        
        $session = $request->getSession(); 
        
        $this->_controlIntentos($session);
        $error = false;                
        // Obtiene el error de login si lo hay
        if ($request->attributes->has(SecurityContext::AUTHENTICATION_ERROR)) 
        {            
            $error = $request->attributes->get(
                            SecurityContext::AUTHENTICATION_ERROR
                      );
        } 
        else 
        {
          $error = $session->get(SecurityContext::AUTHENTICATION_ERROR);                    
          $session->remove(SecurityContext::AUTHENTICATION_ERROR);          
        }    
        
        // PREPARAMOS EL FORMULARIO
        $defaultData = array();
        $formulario = $this->createFormBuilder($defaultData)
        ->add('_username', 'email', array('attr'=>array('placeholder'=>'Correo Electrónico',
                                                              'class'=>'span4', 
                                                          'maxlength'=>'40', 
                                                           'required'=>'required',
                                        'value'=>$session->get(SecurityContext::LAST_USERNAME),
                                                              'title'=>'Correo Electrónico')))
                
        ->add('_password', 'password', array('attr'=>array('placeholder'=>'Senha',
                                                              'class'=>'span4', 
                                                          'maxlength'=>'40', 
                                                           'required'=>'required',
                                                              'title'=>'Senha')))
        ->add('captcha', 'captcha', array('label'=>' ',
                                     'expiration'=> 15,
                               'background_color'=> [255, 255, 255],
                                         'width' => 190,
                                        'height' => 40,
                                        'length' => 6,
                                          'attr' => array('placeholder'=>'Inserte el código',                                                                
                                                            'maxlength'=>'10', 
                                                             'required'=>'required',
                                                                'title'=>'Código Captcha')))
        ->getForm();
        
        // SI VIENE VIA POST
        if ($request->isMethod('POST')) 
        {    
            $formulario->bind($request);
          
            if ($formulario->isValid())
            {
                $data = $request->request->get('form');
        
                $usuario = strtolower(trim($data['_username']));
                $clave = $data['_password'];                
               
               /*
                * Verifica la procedencia del usuario para 
                * determinar si se autentica con LDAP o contra BD
                */    
                if ((substr($usuario,-12)==='@mcti.gob.ve') &&
                    ($this->_validarLDAP($usuario, $clave)))       
                {
                    return new RedirectResponse(
                      $this->generateUrl('check_path', array('_username' => $usuario,
                                                             '_password' => '24121973')));  
                }
                if (substr($usuario,-12)==='@mcti.gob.ve') $clave=uniqid();
        
                return new RedirectResponse(
                     $this->generateUrl('check_path', array('_username' => $usuario,
                                                            '_password' => $clave ))); 
            }
        }        
        
        return $this->render('PmcIntranetBundle:Access:login.html.twig',
                     array('formulario' => $formulario->createView(),
                                'error' => $error));            
    }
    
    /*
     * Pagina inicial del Sistema
     */
    public function principalAction()
    {    
      // CIERRA LA SESION LUEGO DE 10 MINUTOS DE INACTIVIDAD  
      $session = $this->get('Session');
      
      if (time() - $session->getMetadataBag()->getLastUsed()>600)
      {
          $session->invalidate();
          return $this->redirect($this->generateUrl('logout'), 301);          
      }      
      
      $this->getDoctrine()              
           ->getRepository('PmcIntranetBundle:Usuario')           
           ->limpiarTentativasById($this->getUser()->getId());//Limpiamos intentos fallidos de entrada
     
      return $this->render('PmcIntranetBundle:Access:principal.html.twig');
    }
       
    /*
     * Activación de cuenta
     */
    public function activarCuentaAction()
    {
       if ($this->get('security.context')->isGranted('ROLE_USER'))
       {
          return $this->redirect($this->generateUrl('home'), 301);          
       }
        
       $request=$this->getRequest();
       if ($request->isMethod('GET')) 
       {
          $cuenta= $request->query->get('cuenta');
          $salt= $request->query->get('salt');
          
          $usuario = $this->getDoctrine()
                           ->getRepository('SisproBundle:Usuario')
                           ->findOneBy(array('correo' => $cuenta,
                                               'salt' => $salt));          
          
          if (!$usuario) return $this->render('SisproBundle:Plantillas:prohibido.html.twig');
          
          /*
           * Verificamos si la cuenta es del MCTI para solamente Activarla 
           * sin cambiar la contraseña
           */         
          if ($usuario->isMcti())
          {
             if ($usuario->getTiempoBloqueo()!=0)
             {
                return $this->render('SisproBundle:Plantillas:prohibido.html.twig');
             }
             $usuario->setActivo(true);
             $em = $this->getDoctrine()->getManager();                          
             try {       
                $em->flush();
             } catch (\Exception $e) { // Atrapa Error del servidor
               die('Error: '.$e->getMessage());
             }
             
             $data['titulo']="Bienvenido";
             $data['mensaje']="Su cuenta ha sido activada satisfactoriamente en el sistema.";                          
             return $this->render('SisproBundle:Plantillas:mensajes.html.twig', $data);
          }           
          $formulario = $this->createForm(new CambiarClaveType(), $usuario);
       }
       
       if ($request->isMethod('POST'))
       {
          $paquete = $request->request->get('mcti_sisprobundle_cambiarclavetype');
          $paquete['clave']['first']=trim($paquete['clave']['first']);
          $paquete['clave']['second']=trim($paquete['clave']['second']);
          $request->request->set('mcti_sisprobundle_activarcuentatype',$paquete);
          
          $usuario = $this->getDoctrine()
                          ->getRepository('SisproBundle:Usuario')
                          ->findOneByCorreo($paquete['correo']);          
           
          if (!$usuario) die('Error: Usuario no encontrado');
          $formulario = $this->createForm(new CambiarClaveType(), $usuario);
          $formulario->bind($request);            
           
          if ($formulario->isValid())
          {  
             $usuario = $this->getDoctrine()
                             ->getRepository('SisproBundle:Usuario')
                             ->findOneByCorreo($paquete['correo']);
              
             if (!$usuario) die('Error: Usuario no encontrado');
             
             $encoder = $this->get('security.encoder_factory')
                             ->getEncoder($usuario);
            
             $usuario->setSalt(md5(time()));
             $passwordCodificado = $encoder->encodePassword($usuario->getClave(),
                                                            $usuario->getSalt());
             $usuario->setClave($passwordCodificado);
             $usuario->setActivo(true);

             $em = $this->getDoctrine()->getManager();
             
             try {       
                $em->flush();
             } catch (\Exception $e) { // Atrapa Error del servidor
               die('Error: '.$e->getMessage());
             }
             
             // REGISTRA LA ACCION EN LA BITACORA
             $bitacora= new Bitacora();
             $registro='Activación de cuenta de Usuario: '.$usuario->getCorreo().'.';
             
             $bitacora->setRegistro($registro);
             $bitacora->setEntidad('Usuario');
             $bitacora->setAccion('UPDATE');
             $bitacora->setUsuario($usuario);
             $bitacora->setIp($this->get('request')->getClientIp());
             $bitacora->setUserAgent($this->getRequest()->headers->get('user-agent'));
             $bitacora->setFecha(new \DateTime('now'));
                     
             $em = $this->getDoctrine()->getManager();
             $em->persist($bitacora);            
             try {       
                   $em->flush();
             } catch (\Exception $e) { // Atrapa Error del servidor
               die('Ocurrió un error al registrar en la Bitácora: '.$e->getMessage());  
             }              
             
             $data['titulo']="Bienvenido";
             $data['mensaje']="Su cuenta ha sido activada satisfactoriamente en el sistema.";                          
             return $this->render('PmcIntranetBundle:Plantillas:mensajes.html.twig', $data);
          } 
       }
       return $this->render('PmcIntranetBundle:Access:activar.html.twig',
                      array('formulario' => $formulario->createView()));
    }
    
    /*
     * Salida del Sistema
     */    
    public function salidaAction()
    {
        $session = $this->get('Session');        
        $session->invalidate();
        return $this->redirect($this->generateUrl('logout'), 301);        
    }

    /*
     * Con este método controlamos 
     * el máximo número de intentos fallidos de contraseña
     */
    private function _controlIntentos($session)
    {
        $correo=$session->get(SecurityContext::LAST_USERNAME);
        $huboError=$session->has('_security.last_error'); /* Verificamos si hubo Error */
        
        $pdo=$this->get('session.handler.pdo');   /* Leemos el PDO */
        $claveErrada = (strpos(strtoupper(
                        $pdo->read($session->getId())), 'BAD CREDENTIAL')===FALSE)? FALSE:TRUE;
        
        /* Registramos el fallo de contraseña */
        if ($huboError && $claveErrada)
        {
            /*
             * Verficamos la existencia del Correo
             */            
            $usuarios = $this->getDoctrine()
                             ->getRepository('PmcIntranetBundle:Usuario');    
            
            $usuario = $usuarios->findOneByCorreo($correo);
            if(!$usuario) return 'Correo No válido';
            
            $id=$usuario->getId();
            $contador=$usuario->getIntentos();
            $contador++;
            
            $usuarios = $this->getDoctrine()
                             ->getManager();
            
            $usuario=$usuarios->getRepository('PmcIntranetBundle:Usuario')
                              ->find($id);
                      
            if ($contador > 2) 
            {
               $usuario->setTiempoBloqueo(time());
               // REGISTRA LA ACCION EN LA BITACORA
               $bitacora= new Bitacora();
               $registro='Bloqueo de cuenta de Usuario: '.$usuario->getCorreo().', por ';
               $registro.='exceder el número de intentos fallidos de contraseña';
               
               $bitacora->setRegistro($registro);
               $bitacora->setEntidad('Usuario');
               $bitacora->setAccion('UPDATE');
               $bitacora->setUsuario($usuario);
               $bitacora->setIp($this->get('request')->getClientIp());
               $bitacora->setUserAgent($this->getRequest()->headers->get('user-agent'));
               $bitacora->setFecha(new \DateTime('now'));
                       
               $em = $this->getDoctrine()->getManager();
               $em->persist($bitacora);            
               try {       
                     $em->flush();
               } catch (\Exception $e) { // Atrapa Error del servidor
                 die('Ocurrió un error al registrar en la Bitácora: '.$e->getMessage());  
               }  
            }
            $usuario->setIntentos($contador);
            $usuarios->flush();            
        }
        return true;
    }    

}   