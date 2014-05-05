<?php

namespace Pmc\IntranetBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Security\Core\SecurityContext;
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
        
        $session = $request->getSession();  
        
        $this->_attempsControl($session);
        $error = false;                
        // Obtiene el error de login si lo hay
        if ($request->attributes->has(SecurityContext::AUTHENTICATION_ERROR)) 
        {            
            $error = $request->attributes->get(
                            SecurityContext::AUTHENTICATION_ERROR );
        } 
        else 
        {
          $error = $session->get(SecurityContext::AUTHENTICATION_ERROR);                    
          $session->remove(SecurityContext::AUTHENTICATION_ERROR);          
        } 
        
        // PREPARAMOS EL FORMULARIO
        $defaultData = array();
        $formulario = $this->createFormBuilder($defaultData)                
        ->add('_username', 'email', array('attr'=>array('placeholder'=>'E-mail',
                                                              'class'=>'form-control', 
                                                          'maxlength'=>'50', 
                                                           'required'=>'required',
                                        'value'=>$session->get(SecurityContext::LAST_USERNAME),
                                                              'title'=>'E-mail')))
                
        ->add('_password', 'password', array('attr'=>array('placeholder'=>'Senha',
                                                                 'class'=>'form-control', 
                                                             'maxlength'=>'20', 
                                                              'required'=>'required',
                                                                 'title'=>'Senha')))
    /*    ->add('captcha', 'captcha', array('label'=>' ',
                                     'expiration'=> 15,
                               'background_color'=> [255, 255, 255],
                                         'width' => 190,
                                        'height' => 40,
                                        'length' => 6,
                                          'attr' => array('placeholder'=>'Inserte el código',                                                                
                                                            'maxlength'=>'10', 
                                                             'required'=>'required',
                                                                'title'=>'Código Captcha')))*/
        ->getForm();        
  
        
        return $this->render('PmcIntranetBundle:Access:login.html.twig',
                     array('formulario' => $formulario->createView(),
                                'error' => $error));           
    }
    
    /*
     * This method controls the maximum 
     * number of failed password attempts
     */
    private function _attempsControl($session)
    {
        $email = $session->get(SecurityContext::LAST_USERNAME);
        // Checks if an error has occurred
        $errorOccurred=$session->has('_security.last_error'); 
        
        $pdo = $this->get('session.handler.pdo');   /* Read the PDO */
        $wrongPassword = (strpos(strtoupper(
                        $pdo->read($session->getId())), 'BAD CREDENTIAL')===FALSE)? FALSE:TRUE;
        
        /* Register the fault */
        if ($errorOccurred && $wrongPassword)
        {
            /*
             * Checks if email exists
             */            
            $users = $this->getDoctrine()
                             ->getRepository('PmcIntranetBundle:Usuario');    
            
            $user = $users->findOneByEmail($email);
            if(!$user) return 'Email inválido';
            
            $id = $user->getId();
            $counter = $user->getTentativas();
            $counter++;
            
            $users = $this->getDoctrine()
                          ->getManager();
            
            $user = $users->getRepository('PmcIntranetBundle:Usuario')
                          ->find($id);
                      
            if ($counter > 2) 
            {
               $user->setTempoEspera(time());
               // REGISTRA LA ACCION EN LA BITACORA
            /*   $bitacora= new Bitacora();
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
               }  */
            }
            $user->setTentativas($counter);
            $users->flush();            
        }
        return true;
    }        
}