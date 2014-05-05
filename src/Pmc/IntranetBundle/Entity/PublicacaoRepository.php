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
                  ->createQuery('SELECT p, up, t, c, uc
                                 FROM PmcIntranetBundle:Publicacao p
                                 JOIN p.usuario up
                                 LEFT JOIN p.comentarios c
                                 LEFT JOIN c.usuario uc
                                 JOIN up.secretaria s
                                 JOIN p.tipo t
                                 WHERE (up = :user AND t.id = 1)
                                 OR (:user MEMBER OF up.seguidor AND t.id = 1)                                 
                                 OR (s != :secretaria AND
                                     p.publico = :public AND
                                     p.ativo = :ativo AND t.id != 1)
                                 OR (s = :secretaria AND
                                     p.ativo = :ativo AND t.id != 1) 
                                 ORDER BY p.data, c.data DESC')
                  ->setParameters(array('user' => $user,
                                  'secretaria' => $user->getSecretaria(),
                                      'public' => true,
                                       'ativo' => true ))
                  ->getResult();  
    }    
}
?>