<?php

namespace Pmc\IntranetBundle\Entity;

use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\Exception\LockedException;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\NoResultException;

class UsuarioRepository extends EntityRepository implements UserProviderInterface
{
    public function loadUserByUsername($username)
    {
        $q = $this
            ->createQueryBuilder('u')
            ->select('u, r')
            ->leftJoin('u.role', 'r')                
            ->where('u.user = :username OR u.email = :email')
            ->setParameter('username', $username)
            ->setParameter('email', $username)
            ->getQuery() ;
        
        try {
            // El método Query::getSingleResult() lanza una excepción
            // si no hay algún registro que coincida con los criterios.
            $user = $q->getSingleResult();            
        } catch (NoResultException $e) {
            $message = sprintf(
                'Incapaz de encontrar un Admin Activo PmcIntranetBundle:Usuario objeto identificado por "%s".',
                $username
            );
            throw new UsernameNotFoundException($message, 0, $e);
        }        
        /* Si el usuario se encuentra bloqueado lanzamos una excepcion */
        if ($this->_usuarioEspera($user)) throw new LockedException('Excedeu o número de tentativas frustradas',0);
        
        return $user;        
    }   
    
    public function refreshUser(UserInterface $user)
    {
        $class = get_class($user);
        if (!$this->supportsClass($class)) {
            throw new UnsupportedUserException(
                sprintf(
                    'Instances of "%s" are not supported.',
                    $class
                )
            );
        }
        return $this->find($user->getId());
    }

    public function supportsClass($class)
    {
        return $this->getEntityName() === $class
            || is_subclass_of($class, $this->getEntityName());
    }
    
    // Verificar si el usuario está bloqueado
    private function _usuarioEspera(UserInterface $user)
    {
        if ($user->getTempoEspera()>0) /* Si se encuentra bloqueado */
        {            
            if ( time() > ($user->getTempoEspera() + Usuario::TEMPO_ESPERA ) )
            {
               $q = $this
                    ->createQueryBuilder('u')
                    ->update()   
                    ->set('u.tempoEspera',':res')   /* Desbloquea el Usuario */
                    ->set('u.tentativas',':res')       /* Reinicia el Contador */
                    ->where('u.id= :iduser')   
                    ->setParameter('res', 0)
                    ->setParameter('iduser', $user->getId())
                    ->getQuery();               
               $q->execute();
               return false;
            }
            else { return true; }
        }
        return false;
    } 
    
    // Borra los intentos fallidos del usuario 
    public function limpiarTentativas(UserInterface $user)
    {
         $q = $this
                    ->createQueryBuilder('u')
                    ->update()   
                    ->set('u.tempoEspera',':reset')   /* Desbloquea el Usuario */
                    ->set('u.tentativas',':reset')       /* Reinicia el Contador */
                    ->where('u= :user')   
                    ->setParameter('reset', 0)
                    ->setParameter('user', $user)
                    ->getQuery();               
               $q->execute();   
          return true;
    }

    // Registra el último acceso del usuario
    public function registraUltimoAcessoUsuario(UserInterface $user)
    {         
         $q = $this->createQueryBuilder('u')
                   ->update()   
                   ->set('u.ultimoAcesso',':tempo')                    
                   ->where('u= :user')   
                   ->setParameter('tempo', new \DateTime('now'))
                   ->setParameter('user', $user)
                   ->getQuery();               
                 $q->execute();   
          return true;
    }  
    
    /*
     * Obtenemos usuarios filtrados ordenados por nombre
     */
    public function getUsuariosFiltrados($filtros)
    {
         $user = $filtros['user'];
         
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
                     ->createQuery('SELECT u, s, fans
                                    FROM PmcIntranetBundle:Usuario u
                                    JOIN u.secretaria s
                                    LEFT JOIN u.role r
                                    LEFT JOIN u.seguidor fans
                                    WHERE (
                                           u.nome LIKE :patron or
                                           u.cargo LIKE :patron or 
                                           u.telefone LIKE :patron or
                                           u.email LIKE :patron or
                                           u.user LIKE :patron or
                                           u.matricula LIKE :patron or
                                           u.rg LIKE :patron or
                                           u.cpf LIKE :patron
                                           )
                                    AND ( u.ativo != :status )
                                    AND ( s.id IN (:secretarias) ) 
                                    AND (u != :user )
                                    AND (r.id != 6)
                                    ORDER BY u.nome ASC')
                     ->setParameters(array('user' => $user,
                                         'status' => $status,
                                         'patron' => "%$patron%",
                                    'secretarias' => $secretarias ))              
                     ->getResult();          
    }
    
}

?>