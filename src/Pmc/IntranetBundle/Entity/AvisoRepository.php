<?php

namespace Pmc\IntranetBundle\Entity;

use Doctrine\ORM\EntityRepository;

class AvisoRepository extends EntityRepository
{
    /*
     * Obtenemos Avisos filtrados ordenados por antigüedad
     */
    public function getAvisosFiltrados($filtros)
    {    
         $patron = (isset($filtros['patron']) and trim($filtros['patron']) != '')? 
                                   trim($filtros['patron']):'';
         
         $usuario = (isset($filtros['usuario']) and $filtros['usuario'] != '')? 
                                   $filtros['usuario']:'';
                  
         $status = (isset($filtros['status']) and $filtros['status'] != '')? 
                                   $filtros['status']: 3;
         
         $secretarias = (isset($filtros['secretarias']))? $filtros['secretarias']:
                                     $this->getEntityManager()
                                          ->createQuery("SELECT s.id
                                                         FROM PmcIntranetBundle:Secretaria s")
                                          ->getArrayResult();  
                   
         return $this->getEntityManager()
                     ->createQuery('SELECT a, u, s, d, ud, c, cu
                                    FROM PmcIntranetBundle:Aviso a
                                    JOIN a.usuario u
                                    JOIN u.secretaria s
                                    LEFT JOIN a.destinatarios d
                                    LEFT JOIN d.usuario ud
                                    LEFT JOIN a.comentarios c
                                    LEFT JOIN c.usuario cu
                                    WHERE (
                                            (u = :user) or
                                            (ud = :user)
                                          )                                    
                                    AND  (
                                           a.titulo LIKE :patron or
                                           a.conteudo LIKE :patron or
                                           c.comentario LIKE :patron or
                                           cu.nome LIKE :patron or
                                           u.nome LIKE :patron
                                          )
                                    AND ( d.leido != :status )
                                    AND ( s.id IN (:secretarias) )                                     
                                    ORDER BY a.data DESC, d.leido ASC, a.importante DESC, u.nome ASC')
                     ->setParameters(array(  'user' => $usuario,
                                           'status' => $status,
                                           'patron' => "%$patron%",
                                      'secretarias' => $secretarias ))                 
                     ->getResult();          
    }
    
    /*
     * Obtenemos los avisos No leidos dirijidos al usuario
     */
    public function cantidadAvisosPendientes($usuario)
    {
        return $this->getEntityManager()
                     ->createQuery('SELECT COUNT(a.id)
                                    FROM PmcIntranetBundle:Aviso a
                                    JOIN a.usuario u
                                    LEFT JOIN a.destinatarios d
                                    LEFT JOIN d.usuario ud                                    
                                    WHERE (ud = :user)
                                    AND ( d.leido = :status )' )
                     ->setParameters(array(  'user' => $usuario,
                                           'status' => false ))                 
                     ->getSingleScalarResult();          
    }
    
}

?>