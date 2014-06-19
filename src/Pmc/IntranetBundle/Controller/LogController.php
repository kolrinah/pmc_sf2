<?php

namespace Pmc\IntranetBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
//use Symfony\Component\HttpFoundation\Request;

class LogController extends Controller
{
    /*
     * Lista la Bitácora
     */
    public function listarAction()
    { 
        $data = $this->get('serviciosComunes')->rutinaInicio();
        
        $em = $this->getDoctrine()->getManager();
        $query = $em->createQuery('SELECT b,u 
                                 FROM PmcIntranetBundle:Log b
                                 JOIN b.usuario u
                                 ORDER BY b.date DESC');

        $log = $query->getResult();
        
        if (!$log) return $this->render('PmcIntranetBundle:Templates:prohibido.html.twig');
        
        $data['log'] = $log;
  
        return $this->render('PmcIntranetBundle:Templates:log.html.twig', 
                  array('data'=>$data));
    }

}