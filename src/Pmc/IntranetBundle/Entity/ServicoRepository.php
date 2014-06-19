<?php

namespace Pmc\IntranetBundle\Entity;

use Doctrine\ORM\EntityRepository;

class ServicoRepository extends EntityRepository
{
    /*
     * Obtenemos servicios filtrados ordenados por secretaria
     */
    public function getServicosFiltrados($filtros)
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
                     ->createQuery('SELECT s,sc
                                    FROM PmcIntranetBundle:Servico s
                                    JOIN s.secretaria sc
                                    WHERE (
                                           s.nome LIKE :patron or
                                           s.conteudo LIKE :patron or                                            
                                           sc.nome LIKE :patron
                                           )
                                    AND ( s.ativo != :status )
                                    AND ( sc.ativo != :status )
                                    AND ( sc.id IN (:secretarias) )                                     
                                    ORDER BY s.nome ASC')
                     ->setParameters(array('status' => $status,
                                           'patron' => "%$patron%",
                                      'secretarias' => $secretarias ))                 
                     ->getResult();          
    }
    
    /*
     * Obtenemos las solicitudes de servicios para el usuario
     */
    public function getSolicitacoesServicosFiltrados($filtros)
    {
         $usuario = $filtros['usuario'];
                 
         $patron = (isset($filtros['patron']) and trim($filtros['patron']) != '')? 
                                   trim($filtros['patron']):'';
         
         $status = (isset($filtros['status']) and $filtros['status'] != '')? 
                                   $filtros['status']: 3;
        
         $secretarias = (isset($filtros['secretarias']))? $filtros['secretarias']:
                                     $this->getEntityManager()
                                          ->createQuery("SELECT s.id
                                                         FROM PmcIntranetBundle:Secretaria s")
                                          ->getArrayResult();  
                   
         return $this->getEntityManager()
                     ->createQuery('SELECT so, us, sv, sc, ur, co, uc
                                    FROM PmcIntranetBundle:SolicitacaoServico so
                                    JOIN so.usuario us
                                    JOIN so.servico sv
                                    JOIN sv.secretaria sc
                                    LEFT JOIN sv.responsavel ur
                                    LEFT JOIN so.comentarios co
                                    LEFT JOIN co.usuario uc
                                    WHERE (
                                           so.titulo LIKE :patron or
                                           so.conteudo LIKE :patron or
                                           so.codigoInterno LIKE :patron or
                                           sv.nome LIKE :patron
                                           )
                                    AND ( (us = :usuario) or 
                                          (:usuario MEMBER OF sv.responsavel) )
                                    AND ( (so.abertoUsuario != :status) or
                                          (so.abertoSecretaria != :status) )
                                    AND ( sc.id IN (:secretarias) )      
                                    ORDER BY so.abertoUsuario DESC, so.abertoSecretaria DESC,
                                             so.urgente DESC, so.data DESC, co.data ASC')
                     ->setParameters(array('usuario' => $usuario,
                                            'status' => $status,
                                            'patron' => "%$patron%",
                                       'secretarias' => $secretarias ))                 
                     ->getResult();        
    }
    
}

?>