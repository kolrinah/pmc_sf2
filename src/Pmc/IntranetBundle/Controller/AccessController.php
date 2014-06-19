<?php

namespace Pmc\IntranetBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Security\Core\SecurityContext;
use Symfony\Component\HttpFoundation\Request;
use Pmc\IntranetBundle\Form\CambiarClaveType;

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
        ->add('_username', 'email', array('required'=>true,
                                              'attr'=>array('placeholder'=>'E-mail',
                                                              'class'=>'form-control', 
                                                          'maxlength'=>'50',                                                            
                                        'value'=>$session->get(SecurityContext::LAST_USERNAME),
                                                              'title'=>'E-mail')))
                
        ->add('_password', 'password', array('required'=>true,
                                                 'attr'=>array('placeholder'=>'Senha',
                                                                 'class'=>'form-control', 
                                                             'maxlength'=>'20',                                              
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
               $data['usuario'] = $user;
               $data['action'] = 'UPDATE';
               $data['module'] = 'Access';
               $data['description'] = 'Bloqueio da conta do usuário id: '.$user->getId().
                                      ', por excesso de número de tentativas frustradas.';
               $this->get('serviciosComunes')->cadastrarLog($data);  
            }
            $user->setTentativas($counter);
            $users->flush();            
        }
        return true;
    } 
    
    /*
     * Activación de cuenta
     */
    public function ativarContaAction(Request $request)
    { 
       if ($this->get('security.context')->isGranted('ROLE_USER'))
       {
          return $this->redirect($this->generateUrl('logout'), 301);
       }        
              
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
             $data['description'] = 'Ativação da conta do usuário id: '.$usuario->getId().'.';
             $this->get('serviciosComunes')->cadastrarLog($data);      
             
             $data['titulo']="Bem Vindo";
             $data['mensaje']="Sua senha foi definida com sucesso no sistema.";                          
             return $this->render('PmcIntranetBundle:Templates:serverMessage.html.twig', $data);
          } 
       }
       return $this->render('PmcIntranetBundle:Access:ativar.html.twig',
                      array('formulario' => $formulario->createView()));
    }     
    
    /*
     * Método de recuperación de contraseña
     */
    public function esqueciSenhaAction(Request $request)
    {
        if (($this->get('security.context')->isGranted('ROLE_USER')) or
             !($request->isMethod('POST')) )
           return $this->redirect($this->generateUrl('logout'), 301);
        
        $email = $request->request->get('email');
        $usuario = $this->getDoctrine()
                        ->getRepository('PmcIntranetBundle:Usuario')
                        ->findOneByEmail($email); 
        if (!$usuario or ($usuario->getAtivo()==0)) 
           return $this->render('PmcIntranetBundle:Templates:serverMessage.html.twig', 
                         array('titulo'=>'Erro', 'mensaje' =>'Conta do usuário inválida.' ));
        
        $this->get('serviciosComunes')->enviarCorreioAtivacao($usuario);
        
        // Registramos en bitácora
        $data['usuario'] = $usuario;
        $data['action'] = 'UPDATE';
        $data['module'] = 'Access';
        $data['description'] = 'Solicitação de recuperação de senha do usuário id: '.
                               $usuario->getId().'.';
        $this->get('serviciosComunes')->cadastrarLog($data);  
        
        $data['titulo']="Recuperação de senha";
        $data['mensaje']="Verifique seu e-mail e siga os passos ".
                         "do e-mail de ativação para recuperar sua senha.";
        return $this->render('PmcIntranetBundle:Templates:serverMessage.html.twig', $data);
    }
    
    /*
     * Método para intentar borrar cache de sistema
     */
    public function borrarCacheAction()
    {
        $dir = $this->container->getParameter('kernel.root_dir');
        
        $dev = $dir.'/cache/dev';
        $prod = $dir.'/cache/prod'; 
        $twig ='1';
        
        if (file_exists($dev)) $this->rrmdir($dev); 
        if (file_exists($prod)) $this->rrmdir($prod);
        if (file_exists($twig)) $this->rrmdir($twig); 
        
        return $this->render('PmcIntranetBundle:Templates:serverMessage.html.twig', 
                     array('titulo'=>'Exito', 'mensaje' =>'Caché eliminado satisfactoriamente'));
    }
    
    /*
     * Método para intentar borrar cache de sistema
     */
    public function borrarCacheTwigAction()
    {
        $twig ='1';
        
        if (file_exists($twig)) $this->rrmdir($twig); 
        
        return $this->render('PmcIntranetBundle:Templates:serverMessage.html.twig', 
                     array('titulo'=>'Exito', 'mensaje' =>'Caché eliminado satisfactoriamente'));
    }    
    
    public function rrmdir($dir) 
    { 
       if (is_dir($dir)) 
       { 
          $objects = scandir($dir); 
          foreach ($objects as $object) 
          { 
             if ($object != "." && $object != "..") 
             { 
                 if (filetype($dir."/".$object) == "dir") $this->rrmdir($dir."/".$object); 
                 else unlink($dir."/".$object); 
             } 
          } 
          reset($objects); 
          rmdir($dir); 
       } 
 } 
}