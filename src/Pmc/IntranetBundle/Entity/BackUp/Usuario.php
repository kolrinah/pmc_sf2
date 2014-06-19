<?php

namespace Pmc\IntranetBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\AdvancedUserInterface;

/**
 * Usuario
 *
 * @ORM\Table(name="usuario", uniqueConstraints={@ORM\UniqueConstraint(name="id_UNIQUE", columns={"id"}), @ORM\UniqueConstraint(name="email_UNIQUE", columns={"email"}), @ORM\UniqueConstraint(name="user_UNIQUE", columns={"user"})}, indexes={@ORM\Index(name="fk_usuario_secretaria_idx", columns={"secretaria_id"})})
 * @ORM\Entity(repositoryClass="Pmc\IntranetBundle\Entity\UsuarioRepository")
 */
class Usuario implements AdvancedUserInterface, \Serializable
{
    /**
     * @var integer 15 minutos de bloqueo
     */
    const TEMPO_ESPERA = 900;    
    
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="nome", type="string", length=100, nullable=false)
     */
    private $nome;

    /**
     * @var string
     *
     * @ORM\Column(name="cargo", type="string", length=50, nullable=true)
     */
    private $cargo;

    /**
     * @var string
     *
     * @ORM\Column(name="telefone", type="string", length=45, nullable=true)
     */
    private $telefone;

    /**
     * @var string
     *
     * @ORM\Column(name="foto", type="string", length=200, nullable=true)
     */
    private $foto;

    /**
     * @var string
     *
     * @ORM\Column(name="email", type="string", length=150, nullable=false)
     */
    private $email;

    /**
     * @var string
     *
     * @ORM\Column(name="user", type="string", length=50, nullable=false)
     */
    private $user;

    /**
     * @var string
     *
     * @ORM\Column(name="senha", type="string", length=200, nullable=false)
     */
    private $senha = '5950c3628ec1e0973e66625d90240ec661b7fb3b';

    /**
     * @var string
     *
     * @ORM\Column(name="salt", type="string", length=200, nullable=false)
     */
    private $salt = '636faccb4a9d532e63a3c7b00b4fb164';

    /**
     * @var integer
     *
     * @ORM\Column(name="tentativas", type="smallint", nullable=false)
     */
    private $tentativas = '0';

    /**
     * @var integer
     *
     * @ORM\Column(name="tempo_espera", type="integer", nullable=false)
     */
    private $tempoEspera = '0';

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="ultimo_acesso", type="datetime", nullable=true)
     */
    private $ultimoAcesso;

    /**
     * @var string
     *
     * @ORM\Column(name="matricula", type="string", length=25, nullable=true)
     */
    private $matricula;

    /**
     * @var string
     *
     * @ORM\Column(name="rg", type="string", length=25, nullable=true)
     */
    private $rg;

    /**
     * @var string
     *
     * @ORM\Column(name="cpf", type="string", length=25, nullable=true)
     */
    private $cpf;

    /**
     * @var boolean
     *
     * @ORM\Column(name="ativo", type="boolean", nullable=false)
     */
    private $ativo = true;
    
    /**
     * @var boolean
     *
     * @ORM\Column(name="banido", type="boolean", nullable=false)
     */
    private $banido = false;    

    /**
     * @var \Pmc\IntranetBundle\Entity\Secretaria
     *
     * @ORM\ManyToOne(targetEntity="Pmc\IntranetBundle\Entity\Secretaria")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="secretaria_id", referencedColumnName="id")
     * })
     */
    private $secretaria;

    /**
     * @var \Doctrine\Common\Collections\Collection
     *
     * @ORM\ManyToMany(targetEntity="Pmc\IntranetBundle\Entity\Servico", mappedBy="responsavel")
     */
    private $servico;

    /**
     * @var \Doctrine\Common\Collections\Collection
     *
     * @ORM\ManyToMany(targetEntity="Pmc\IntranetBundle\Entity\Usuario", inversedBy="usuario")
     * @ORM\JoinTable(name="seguidor",
     *   joinColumns={
     *     @ORM\JoinColumn(name="usuario_id", referencedColumnName="id")
     *   },
     *   inverseJoinColumns={
     *     @ORM\JoinColumn(name="seguidor_id", referencedColumnName="id")
     *   }
     * )
     */
    private $seguidor;

    /**
     * @var \Doctrine\Common\Collections\Collection
     *
     * @ORM\ManyToMany(targetEntity="Pmc\IntranetBundle\Entity\Role", inversedBy="usuario")
     * @ORM\JoinTable(name="usuario_role",
     *   joinColumns={
     *     @ORM\JoinColumn(name="usuario_id", referencedColumnName="id")
     *   },
     *   inverseJoinColumns={
     *     @ORM\JoinColumn(name="role_id", referencedColumnName="id")
     *   }
     * )
     */
    private $role;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->servico = new \Doctrine\Common\Collections\ArrayCollection();
        $this->seguidor = new \Doctrine\Common\Collections\ArrayCollection();
        $this->role = new \Doctrine\Common\Collections\ArrayCollection;        
        $this->ativo = false; // Crea al usuario inactivo para validarlo vía correo
        $this->banido = false; 
        $this->tempoEspera = 0;
        $this->tentativas = 0;   
    }


    /**
     * Get id
     *
     * @return integer 
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set nome
     *
     * @param string $nome
     * @return Usuario
     */
    public function setNome($nome)
    {
        $this->nome = \mb_convert_case(\trim($nome),\MB_CASE_TITLE, "UTF-8"); 

        return $this;
    }

    /**
     * Get nome
     *
     * @return string 
     */
    public function getNome()
    {
        return $this->nome;
    }

    /**
     * Set cargo
     *
     * @param string $cargo
     * @return Usuario
     */
    public function setCargo($cargo)
    {
        $this->cargo = \mb_convert_case(\trim($cargo),\MB_CASE_TITLE, "UTF-8");

        return $this;
    }

    /**
     * Get cargo
     *
     * @return string 
     */
    public function getCargo()
    {
        return $this->cargo;
    }

    /**
     * Set telefone
     *
     * @param string $telefone
     * @return Usuario
     */
    public function setTelefone($telefone)
    {
        $this->telefone = \trim($telefone);

        return $this;
    }

    /**
     * Get telefone
     *
     * @return string 
     */
    public function getTelefone()
    {
        return $this->telefone;
    }

    /**
     * Set foto
     *
     * @param string $foto
     * @return Usuario
     */
    public function setFoto($foto)
    {
        $this->foto = $foto;

        return $this;
    }

    /**
     * Get foto
     *
     * @return string 
     */
    public function getFoto()
    {
        return $this->foto;
    }

    /**
     * Set email
     *
     * @param string $email
     * @return Usuario
     */
    public function setEmail($email)
    {
        $this->email = \mb_convert_case(\trim($email),\MB_CASE_LOWER, "UTF-8");                

        return $this;
    }

    /**
     * Get email
     *
     * @return string 
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * Set user
     *
     * @param string $user
     * @return Usuario
     */
    public function setUser($user)
    {
        $this->user = $user;

        return $this;
    }

    /**
     * Get user
     *
     * @return string 
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * Set senha
     *
     * @param string $senha
     * @return Usuario
     */
    public function setSenha($senha)
    {
        $this->senha = $senha;

        return $this;
    }

    /**
     * Get senha
     *
     * @return string 
     */
    public function getSenha()
    {
        return $this->senha;
    }

    /**
     * Set salt
     *
     * @param string $salt
     * @return Usuario
     */
    public function setSalt($salt)
    {
        $this->salt = $salt;

        return $this;
    }

    /**
     * Get salt
     *
     * @return string 
     */
    public function getSalt()
    {
        return $this->salt;
    }

    /**
     * Set tentativas
     *
     * @param integer $tentativas
     * @return Usuario
     */
    public function setTentativas($tentativas)
    {
        $this->tentativas = $tentativas;

        return $this;
    }

    /**
     * Get tentativas
     *
     * @return integer 
     */
    public function getTentativas()
    {
        return $this->tentativas;
    }

    /**
     * Set tempoEspera
     *
     * @param integer $tempoEspera
     * @return Usuario
     */
    public function setTempoEspera($tempoEspera)
    {
        $this->tempoEspera = $tempoEspera;

        return $this;
    }

    /**
     * Get tempoEspera
     *
     * @return integer 
     */
    public function getTempoEspera()
    {
        return $this->tempoEspera;
    }

    /**
     * Set ultimoAcesso
     *
     * @param \DateTime $ultimoAcesso
     * @return Usuario
     */
    public function setUltimoAcesso($ultimoAcesso)
    {
        $this->ultimoAcesso = $ultimoAcesso;

        return $this;
    }

    /**
     * Get ultimoAcesso
     *
     * @return \DateTime 
     */
    public function getUltimoAcesso()
    {
        return $this->ultimoAcesso;
    }

    /**
     * Set matricula
     *
     * @param string $matricula
     * @return Usuario
     */
    public function setMatricula($matricula)
    {
        $this->matricula = $matricula;

        return $this;
    }

    /**
     * Get matricula
     *
     * @return string 
     */
    public function getMatricula()
    {
        return $this->matricula;
    }

    /**
     * Set rg
     *
     * @param string $rg
     * @return Usuario
     */
    public function setRg($rg)
    {
        $this->rg = $rg;

        return $this;
    }

    /**
     * Get rg
     *
     * @return string 
     */
    public function getRg()
    {
        return $this->rg;
    }

    /**
     * Set cpf
     *
     * @param string $cpf
     * @return Usuario
     */
    public function setCpf($cpf)
    {
        $this->cpf = $cpf;

        return $this;
    }

    /**
     * Get cpf
     *
     * @return string 
     */
    public function getCpf()
    {
        return $this->cpf;
    }

    /**
     * Set ativo
     *
     * @param boolean $ativo
     * @return Usuario
     */
    public function setAtivo($ativo)
    {
        $this->ativo = $ativo;

        return $this;
    }

    /**
     * Get ativo
     *
     * @return boolean 
     */
    public function getAtivo()
    {
        return $this->ativo;
    }

    /**
     * Set banido
     *
     * @param boolean $banido
     * @return Usuario
     */
    public function setBanido($banido)
    {
        $this->banido = $banido;

        return $this;
    }

    /**
     * Get banido
     *
     * @return boolean 
     */
    public function getBanido()
    {
        return $this->banido;
    }
    
    /**
     * Set secretaria
     *
     * @param \Pmc\IntranetBundle\Entity\Secretaria $secretaria
     * @return Usuario
     */
    public function setSecretaria(\Pmc\IntranetBundle\Entity\Secretaria $secretaria)
    {
        $this->secretaria = $secretaria;

        return $this;
    }

    /**
     * Get secretaria
     *
     * @return \Pmc\IntranetBundle\Entity\Secretaria 
     */
    public function getSecretaria()
    {
        return $this->secretaria;
    }

    /**
     * Add servico
     *
     * @param \Pmc\IntranetBundle\Entity\Servico $servico
     * @return Usuario
     */
    public function addServico(\Pmc\IntranetBundle\Entity\Servico $servico)
    {
        $this->servico[] = $servico;

        return $this;
    }

    /**
     * Remove servico
     *
     * @param \Pmc\IntranetBundle\Entity\Servico $servico
     */
    public function removeServico(\Pmc\IntranetBundle\Entity\Servico $servico)
    {
        $this->servico->removeElement($servico);
    }

    /**
     * Get servico
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getServico()
    {
        return $this->servico;
    }

    /**
     * Add seguidor
     *
     * @param \Pmc\IntranetBundle\Entity\Usuario $seguidor
     * @return Usuario
     */
    public function addSeguidor(\Pmc\IntranetBundle\Entity\Usuario $seguidor)
    {
        $this->seguidor[] = $seguidor;

        return $this;
    }

    /**
     * Remove seguidor
     *
     * @param \Pmc\IntranetBundle\Entity\Usuario $seguidor
     */
    public function removeSeguidor(\Pmc\IntranetBundle\Entity\Usuario $seguidor)
    {
        $this->seguidor->removeElement($seguidor);
    }

    /**
     * Get seguidor
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getSeguidor()
    {
        return $this->seguidor;
    }

    /**
     * Add role
     *
     * @param \Pmc\IntranetBundle\Entity\Role $role
     * @return Usuario
     */
    public function addRole(\Pmc\IntranetBundle\Entity\Role $role)
    {
        $this->role[] = $role;

        return $this;
    }

    /**
     * Remove role
     *
     * @param \Pmc\IntranetBundle\Entity\Role $role
     */
    public function removeRole(\Pmc\IntranetBundle\Entity\Role $role)
    {
        $this->role->removeElement($role);
    }

    /**
     * Get role
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getRole()
    {
        return $this->role;
    }
    
    // METODOS AGREGADOS CON UserInterface
    /**
     * @inheritDoc
     */
    public function eraseCredentials() {
        
    }

    /**
     * @inheritDoc
     */    
    public function getPassword() 
    {
        return $this->senha;        
    }

    /**
     * @inheritDoc
     */
    public function getRoles() 
    {
        //return array('ROLE_USER'); 
        return $this->role->toArray();        
    }

    /**
     * @inheritDoc
     */    
    public function getUsername()
    {
        return $this->user;        
    }

    /**
     * @inheritDoc
     */    
    public function isAccountNonExpired() 
    {
        return true;
    }

    /**
     * @inheritDoc
     */    
    public function isAccountNonLocked() 
    {
      if ($this->tempoEspera>0) /* Si se encuentra bloqueado */
      {            
          if ( time() > ($this->tempoEspera + self::TEMPO_ESPERA ) )
          {
            $this->tempoEspera = 0; /* Desbloqueo del usuario */   
            return true;
          }
          else { return false; }
      }
      return true;        
    }

    /**
     * @inheritDoc
     */    
    public function isCredentialsNonExpired() 
    {
        return true;
    }

    /**
     * @inheritDoc
     */    
    public function isEnabled() 
    {
        return $this->ativo;        
    }

    /**
     * @inheritDoc
     */    
    public function serialize() 
    {
        return serialize(array(
            $this->id,
        ));        
    }

    /**
     * @inheritDoc
     */    
    public function unserialize($serialized) 
    {
        list (
            $this->id,
        ) = unserialize($serialized);        
    }
    
    /**
     * @inheritDoc
     */      
    public function __toString()
    {
       return $this->getNome();
    }
    
    /* * * * * * * * * * * * * * * * * * * * * * * 
     * METODOS CREADOS MANUALMENTE
     */

    // Método para subir la foto
    public function uploadFoto($directorioDestino)
    {
        if (null === $this->foto) return;
                
        $nombreArchivoFoto = uniqid().'.'.
                             $this->foto->getClientOriginalExtension();
        $this->_crearDirectorio($directorioDestino);
        $this->foto->move($directorioDestino, $nombreArchivoFoto);
        $this->setFoto($nombreArchivoFoto);
    }
    
    // METODO PARA REMOVER LA FOTO FISICAMENTE
    public function removerFoto($directorioImagen)
    {  
       $imagen =  $directorioImagen.$this->foto;
       if (file_exists($imagen)) @unlink($imagen);         
       return true;
    }
    
    // METODO PARA CREAR DIRECTORIO
    private function _crearDirectorio($ruta)
    {
       if (!file_exists($ruta)) @mkdir($ruta, 0777, true);
       $this->_crearArchivoIndex($ruta);
    }

    //METODO PARA CREAR ARCHIVOS INDEX EN CARPETA
    private function _crearArchivoIndex($carpeta)
    {
       $archivo = $carpeta.'index.html';
       $contenido = "<html><head><title>403 Proibida</title></head>".
                    "<body>Ação Proibida.</body></html>";

       // CREAMOS EL ARCHIVO SI NO EXISTE
       if (!file_exists($archivo))
       {
          if (!$handle = @fopen($archivo, 'c')) die("No pudo abrir/crear el archivo");
          if (@fwrite($handle, $contenido) === FALSE) die("No pudo escribir en archivo index");            
          @fclose($handle);
       }
       return true;
    }    
    
}
