<?php

namespace Pmc\IntranetBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class HomeController extends Controller
{
    public function homeAction()
    {
        setlocale(LC_ALL,'pt_BR');
        locale_set_default('pt_BR');
        
        // Registramos el último acceso del usuario
        $this->getDoctrine()              
             ->getRepository('PmcIntranetBundle:Usuario')           
             ->registraUltimoAcessoUsuario($this->getUser());
        
        //Limpiamos intentos fallidos de entrada
        $this->getDoctrine()              
             ->getRepository('PmcIntranetBundle:Usuario')           
             ->limpiarTentativas($this->getUser());
     
        $data =array();
        
        //Buscamos los Banners Activos
        $em = $this->getDoctrine()->getManager();
        $query = $em->createQuery(
                        'SELECT b FROM PmcIntranetBundle:Banner b 
                         WHERE b.ativo = :ativo 
                         ORDER BY b.ordem, b.nome ASC')
                    ->setParameter('ativo', true);

        $data['banners'] = $query->getResult();
        
        //Buscamos las publicaciones que debemos ver
        $data['publications'] = $this->getDoctrine()
                                     ->getRepository('PmcIntranetBundle:Publicacao')
                                     ->getPublicacaoHomeUser($this->getUser());
        
        
        return $this->render('PmcIntranetBundle:Home:home.html.twig',array(
                                'data'=>$data ));        
    }
}
