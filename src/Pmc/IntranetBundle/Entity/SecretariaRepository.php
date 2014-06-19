<?php

namespace Pmc\IntranetBundle\Entity;

use Doctrine\ORM\EntityRepository;

class SecretariaRepository extends EntityRepository
{
    /*
     * Obtenemos secretarias filtradas ordenados por nombre
     */
    public function getSecretariasFiltradas($filtros)
    {    
         $patron = (isset($filtros['patron']) and trim($filtros['patron']) != '')? 
                                   trim($filtros['patron']):'';
         
         $mostrar = ($filtros['filtroStatus'] == 'oculto')? 0: 3;
         
         $status = (isset($filtros['status']) and $filtros['status'] != '')? 
                                   $filtros['status']: $mostrar;
        
         $secretarias = (isset($filtros['secretarias']))? $filtros['secretarias']:
                                     $this->getEntityManager()
                                          ->createQuery("SELECT s.id
                                                         FROM PmcIntranetBundle:Secretaria s")
                                          ->getArrayResult();  
          
         return $this->getEntityManager()
                     ->createQuery('SELECT s, b, sv, u
                                    FROM PmcIntranetBundle:Secretaria s
                                    JOIN s.secretario b
                                    LEFT JOIN s.servicos sv
                                    LEFT JOIN s.usuarios u
                                    WHERE (
                                           s.nome LIKE :patron or
                                           s.endereco LIKE :patron or 
                                           b.nome LIKE :patron or
                                           sv.nome LIKE :patron
                                           )
                                    AND ( s.ativo != :status )                                    
                                    AND ( s.id IN (:secretarias) )                                     
                                    ORDER BY s.nome ASC')
                     ->setParameters(array('status' => $status,
                                           'patron' => "%$patron%",
                                      'secretarias' => $secretarias ))         
                     ->getResult();          
    }
    
}

?>