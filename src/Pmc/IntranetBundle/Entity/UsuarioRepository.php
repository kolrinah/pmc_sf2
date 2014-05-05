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
}

?>