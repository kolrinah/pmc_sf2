<?php

namespace Pmc\IntranetBundle\Library;

class ServiciosComunes 
{
    private $container;

    public function __construct($container)
    {
        $this->container = $container;
    }

    /*
     * Rutina común al incio de las acciones
     */
    public function rutinaInicio()
    {   
        if ( !$this->container->get('security.context')->isGranted('ROLE_USER') )
           die('acceso denegado');
        
        date_default_timezone_set('America/Sao_Paulo'); 
        setlocale(LC_ALL,'pt_BR');
        locale_set_default('pt_BR');
        
        // Registramos el último acceso del usuario
        $this->container->get('doctrine')              
             ->getRepository('PmcIntranetBundle:Usuario')           
             ->registraUltimoAcessoUsuario($this->container->get('security.context')
                                                           ->getToken()->getUser());
        
        $data = array();
        
        //Buscamos los Banners Activos
        $em = $this->container->get('doctrine')->getManager();
        $query = $em->createQuery(
                        'SELECT b FROM PmcIntranetBundle:Banner b 
                         WHERE b.ativo = :ativo 
                         ORDER BY b.ordem ASC, b.id DESC')
                    ->setParameter('ativo', true);

        $data['banners'] = $query->getResult();
        
        return $data;
    }
    
    public function ajustarImagen($rutaImagen, $maxX=800, $maxY=600)
    {        
        $imagenInfo=@getimagesize($rutaImagen);
            
        if ($imagenInfo['0']>$maxX or $imagenInfo['1']>$maxY)
        {
            $config['image_library'] = 'gd2';
            $config['source_image'] = $rutaImagen;
            $config['create_thumb'] = FALSE;
            $config['maintain_ratio'] = TRUE;
            $config['width'] = $maxX;  
            $config['height'] = $maxY; 
            $config['master_dim'] = 'width';  
               
            $imagen = $this->container->get('imageLib');
            $imagen->initialize($config);
            if (!$imagen->resize())
            {
                $this->container->get('session')->getFlashBag()
                                ->add('error', $imagen->display_errors());
                return false;
            }
        } return true;
    }
    
    /*
     * Método para Corregir campo Video
     */
    public function modificarVideo($video)
    {   
       $nvoVideo = null; 
        // Si insertaron un iframe buscamos los atributos width y height para sustituirlos
       if (stristr(mb_convert_case($video, \MB_CASE_LOWER), '<iframe'))
       {
          // Buscamos si tiene atributo style
          $pos['style'] = strpos(mb_convert_case($video, \MB_CASE_LOWER),'style');
          if ($pos['style'] !== false)
          {
              $pos['sc1'] = strpos(mb_convert_case($video, \MB_CASE_LOWER),'"', 
                                                           $pos['style']);
              if ($pos['sc1'] !== false)
                  $pos['sc2'] = strpos(mb_convert_case($video, \MB_CASE_LOWER),'"', 
                                                           ($pos['sc1']+1));
              else 
              {
                  $pos['sc1'] = strpos(mb_convert_case($video, \MB_CASE_LOWER),'\'', 
                                                           ($pos['style']+1) );
                  if ($pos['sc1'] !== false)
                  $pos['sc2'] = strpos(mb_convert_case($video, \MB_CASE_LOWER),'\'', 
                                                           ($pos['sc1']+1) );
              }                  

              // Eliminamos el atributo style
              if (isset($pos['sc1']) and isset($pos['sc2']));
              {
                  if ($pos['sc1'] !== false and $pos['sc2'] !== false)
                  {  $nvoVideo = substr($video, 0, ($pos['style'])).                           
                                 substr($video, ($pos['sc2']+1));
                     $video = $nvoVideo; }
              }                               
          }
           
          // Buscamos el atributo witdh
          $pos['width'] = strpos(mb_convert_case($video, \MB_CASE_LOWER),'width');
          if ($pos['width'] !== false)
          {
              $pos['wc1'] = strpos(mb_convert_case($video, \MB_CASE_LOWER),'"', 
                                                           $pos['width']);
              if ($pos['wc1'] !== false)
                  $pos['wc2'] = strpos(mb_convert_case($video, \MB_CASE_LOWER),'"', 
                                                           ($pos['wc1']+1));
              else 
              {
                  $pos['wc1'] = strpos(mb_convert_case($video, \MB_CASE_LOWER),'\'', 
                                                           ($pos['width']+1) );
                  if ($pos['wc1'] !== false)
                  $pos['wc2'] = strpos(mb_convert_case($video, \MB_CASE_LOWER),'\'', 
                                                           ($pos['wc1']+1) );
              }                  

              // Eliminamos el atributo width encontrado
              if (isset($pos['wc1']) and isset($pos['wc2']));
              {
                  if ($pos['wc1'] !== false and $pos['wc2'] !== false)
                  {  $nvoVideo = substr($video, 0, $pos['width']).
                                 substr($video, ($pos['wc2']+1));                  
                  $video = $nvoVideo; }
              }              
          }
         
          // Buscamos el atributo height
          $pos['height'] = strpos(mb_convert_case($video, \MB_CASE_LOWER),'height');
          if ($pos['height'] !== false)
          {
              $pos['hc1'] = strpos(mb_convert_case($video, \MB_CASE_LOWER),'"', 
                                                           $pos['height']);
              if ($pos['hc1'] !== false)
                  $pos['hc2'] = strpos(mb_convert_case($video, \MB_CASE_LOWER),'"', 
                                                           ($pos['hc1']+1));
              else 
              {
                  $pos['hc1'] = strpos(mb_convert_case($video, \MB_CASE_LOWER),'\'', 
                                                           ($pos['height']+1) );
                  if ($pos['hc1'] !== false)
                  $pos['hc2'] = strpos(mb_convert_case($video, \MB_CASE_LOWER),'\'', 
                                                           ($pos['hc1']+1) );
              }                  

              // Eliminamos el atributo height encontrado
              if (isset($pos['hc1']) and isset($pos['hc2']));
              {
                  if ($pos['hc1'] !== false and $pos['hc2'] !== false)
                  {  $nvoVideo = substr($video, 0, $pos['height']).
                                 substr($video, ($pos['hc2']+1));                  
                  $video = $nvoVideo;    }
              }               
          }
          
          // TRUNCAMOS HASTA EL PRIMER </iframe>
          $pos['iframe'] = strpos(mb_convert_case($video, \MB_CASE_LOWER),'</iframe>');
          if ($pos['iframe'] !== false)
          {
              $nvoVideo = substr($video, 0, $pos['iframe']).'</iframe>';
          }
          
          // Buscamos la posición de SRC para insertar nuestras dimensiones
          $pos['src'] = strpos(mb_convert_case($video, \MB_CASE_LOWER),'src');
          if ($pos['src'] !== false)
             $nvoVideo = substr($video, 0, $pos['src']).
                         'style="width:100%; height:250px" '.substr($video, $pos['src']);          
          else return null;          
       }  
       
       // Si insertaron una url debemos trasformarla a iframe dependiendo del proveedor
       // Si el proveedor es youtube
       elseif (stristr(mb_convert_case($video, \MB_CASE_LOWER), 'www.youtube.com/watch?v=')) 
       {          
          $pos['youtube'] = strpos(mb_convert_case($video, \MB_CASE_LOWER),
                                                    'www.youtube.com/watch?v=');
          if ($pos['youtube'] !== false)
          {
              $pos['youtube'] = $pos['youtube'] + strlen('www.youtube.com/watch?v=');
              $nvoVideo = substr($video, $pos['youtube']);              
              
              $nvoVideo = '<iframe style="width:100%; height:250px" '.
                          'src="//www.youtube.com/embed/'.$nvoVideo.
                          '" frameborder="0" allowfullscreen></iframe>';
          }                      
       }
       // Si el proveedor es Vimeo
       elseif (stristr(mb_convert_case($video, \MB_CASE_LOWER), 'vimeo.com/')) 
       {          
          $pos['vimeo'] = strpos(mb_convert_case($video, \MB_CASE_LOWER), 'vimeo.com/');
          if ($pos['vimeo'] !== false)
          {
              $pos['vimeo'] = $pos['vimeo'] + strlen('vimeo.com/');
              $nvoVideo = substr($video, $pos['vimeo']);              
              
              $nvoVideo = '<iframe src="http://player.vimeo.com/video/'.$nvoVideo.
                      '" style="width:100%; height:100%" frameborder="0" '.'
                      webkitAllowFullScreen mozallowfullscreen allowFullScreen></iframe>';
          }                      
       }                     
       return $nvoVideo;        
    }    
}