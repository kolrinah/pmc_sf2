<?php

namespace Pmc\IntranetBundle\Entity;
use Doctrine\ORM\EntityRepository;

class PublicacaoRepository extends EntityRepository
{
    /*
     * Obtenemos las publicaciones que puede ver el usuario
     * en su página de inicio
     */
    public function getPublicacaoHomeUser(Usuario $user)
    {
      return $this->getEntityManager()
                  ->createQuery('SELECT p, u, t
                                 FROM PmcIntranetBundle:Publicacao p
                                 JOIN p.usuario u
                                 
                                 JOIN u.secretaria s
                                 JOIN p.tipo t
                                 WHERE (u = :user AND t.id = 1)
                                 OR (:user MEMBER OF u.seguidor AND t.id = 1)                                 
                                 OR (s != :secretaria AND
                                     p.publico = :public AND
                                     p.ativo = :ativo AND t.id != 1)
                                 OR (s = :secretaria AND
                                     p.ativo = :ativo AND t.id != 1) 
                                 ORDER BY p.data DESC')
                  ->setParameters(array('user' => $user,
                                  'secretaria' => $user->getSecretaria(),
                                      'public' => true,
                                       'ativo' => true ))
                  ->getResult();  
    }    
}
?>