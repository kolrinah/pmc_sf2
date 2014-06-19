<?php

namespace Pmc\IntranetBundle\Entity;
use Doctrine\ORM\EntityRepository;

class PublicacaoRepository extends EntityRepository
{
    /*
     * Obtenemos las publicaciones que puede ver el usuario
     * en su página de inicio y/o aplicando filtro rojo
     */
    public function getPublicacoesHomeUser(Usuario $user, $filtros = array())
    {        
        $patron = (isset($filtros['patron']) and trim($filtros['patron']) != '')? 
                                 trim($filtros['patron']):'';
        
        return $this->getEntityManager()
                  ->createQuery('SELECT p, up, s, t, c, uc
                                 FROM PmcIntranetBundle:Publicacao p
                                 JOIN p.usuario up
                                 LEFT JOIN p.comentarios c
                                 LEFT JOIN c.usuario uc
                                 JOIN up.secretaria s
                                 JOIN p.tipo t
                                 WHERE ( (p.titulo LIKE :patron) or
                                         (p.resumo LIKE :patron) or
                                         (p.conteudo LIKE :patron) or
                                         (up.nome LIKE :patron) )
                                 AND
                                 ( (up = :user AND t.id = 1)
                                 OR (:user MEMBER OF up.seguidor AND t.id = 1)                                 
                                 OR (s != :secretaria AND
                                     p.publico = :public AND
                                     p.ativo = :ativo AND t.id != 1)
                                 OR (s = :secretaria AND
                                     p.ativo = :ativo AND t.id != 1) )
                                 ORDER BY p.data DESC, c.data ASC')
                  ->setParameters(array('user' => $user,
                                  'secretaria' => $user->getSecretaria(),
                                      'patron' => "%$patron%",
                                      'public' => true,
                                       'ativo' => true ))     
                  ->getResult();  
    }        
    
    /*
     * Obtenemos las publicaciones de tipo mensaje 
     * que puede ver el usuario
     */    
    public function getMensagensByUser(Usuario $user, $filtros)
    {
      $patron = (isset($filtros['patron']) and trim($filtros['patron']) != '')? 
                                 trim($filtros['patron']):'';
      
      $secretarias = (isset($filtros['secretarias']))? $filtros['secretarias']:
                                   $this->getEntityManager()
                                        ->createQuery("SELECT s.id
                                                       FROM PmcIntranetBundle:Secretaria s")
                                        ->getArrayResult();
                  
      return $this->getEntityManager()
                  ->createQuery("SELECT p, up, s, t, c, uc
                                 FROM PmcIntranetBundle:Publicacao p
                                 JOIN p.usuario up
                                 LEFT JOIN p.comentarios c
                                 LEFT JOIN c.usuario uc
                                 JOIN up.secretaria s
                                 JOIN p.tipo t
                                 WHERE ( (up = :user AND t.id = 1) or
                                         (:user MEMBER OF up.seguidor AND t.id = 1) )
                                 AND ( p.titulo LIKE :patron or
                                       p.resumo LIKE :patron or 
                                       p.conteudo LIKE :patron or
                                       up.nome LIKE :patron or
                                       uc.nome LIKE :patron
                                     )
                                 AND ( s.id IN (:secretarias) )
                                 ORDER BY p.data DESC, c.data ASC")
                  ->setParameters(array('user' => $user,
                                      'patron' => "%$patron%",
                                 'secretarias' => $secretarias ) )
                  ->getResult();         
    }
    
    /*
     * Obtenemos las publicaciones Especiales que el usuario puede ver
     */
    public function getPublicacoesEspeciaisByUser(Usuario $user, $tipo, $filtros)
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
                   ->createQuery('SELECT p, up, s, t, c, uc
                                  FROM PmcIntranetBundle:Publicacao p
                                  JOIN p.usuario up
                                  LEFT JOIN p.comentarios c
                                  LEFT JOIN c.usuario uc
                                  JOIN up.secretaria s
                                  JOIN p.tipo t
                                  WHERE (
                                         (up = :user and t.id = :tipo) or
                                         (p.ativo != :status and t.id = :tipo and
                                          s.id != :secretariaUser) or
                                         (s.id = :secretariaUser and t.id = :tipo )
                                        )
                                  AND ( p.titulo LIKE :patron or
                                        p.resumo LIKE :patron or 
                                        p.conteudo LIKE :patron or
                                        up.nome LIKE :patron or
                                        uc.nome LIKE :patron
                                      )
                                  AND ( s.id IN (:secretarias) ) 
                                  AND ( p.ativo != :status )
                                  ORDER BY p.data DESC, c.data ASC')
                   ->setParameters(array('user' => $user,                                      
                                         'tipo' => $tipo->getId(),
                                       'status' => $status,
                               'secretariaUser' => $user->getSecretaria(),
                                       'patron' => "%$patron%",
                                  'secretarias' => $secretarias ))                   
                   ->getResult();  
    }       
}
?>