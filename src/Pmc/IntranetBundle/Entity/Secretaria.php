<?php

namespace Pmc\IntranetBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Secretaria
 *
 * @ORM\Table(name="secretaria")
 * @ORM\Entity(repositoryClass="Pmc\IntranetBundle\Entity\SecretariaRepository")
 */
class Secretaria
{
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
    private $nome = '';

    /**
     * @var string
     *
     * @ORM\Column(name="endereco", type="string", length=100, nullable=true)
     */
    private $endereco;

    /**
     * @var string
     *
     * @ORM\Column(name="telefone", type="string", length=13, nullable=true)
     */
    private $telefone;

    /**
     * @var string
     *
     * @ORM\Column(name="url_site", type="string", length=100, nullable=true)
     */
    private $urlSite = 'http://www.carapicuiba.sp.gov.br/index.php';

    /**
     * @var string
     *
     * @ORM\Column(name="mapa", type="text", nullable=true)
     */
    private $mapa;

    /**
     * @var string
     *
     * @ORM\Column(name="email", type="string", length=100, nullable=true)
     */
    private $email;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="data_cadastro", type="date", nullable=false)
     */
    private $dataCadastro;

    /**
     * @var boolean
     *
     * @ORM\Column(name="ativo", type="boolean", nullable=false)
     */
    private $ativo = true;

    /**
     * @var \Usuario
     *
     * @ORM\ManyToOne(targetEntity="Usuario")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="usuario_id", referencedColumnName="id")
     * })
     */
    private $secretario;    

    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    private $servicos;
    
    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    private $usuarios;    

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->servicos = new \Doctrine\Common\Collections\ArrayCollection();
        $this->usuarios = new \Doctrine\Common\Collections\ArrayCollection();
        $this->dataCadastro = new \DateTime('now');
        $this->ativo = true;
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
     * @return Secretaria
     */
    public function setNome($nome)
    {
        $this->nome = $nome;

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
     * Set endereco
     *
     * @param string $endereco
     * @return Secretaria
     */
    public function setEndereco($endereco)
    {
        $this->endereco = $endereco;

        return $this;
    }

    /**
     * Get endereco
     *
     * @return string 
     */
    public function getEndereco()
    {
        return $this->endereco;
    }

    /**
     * Set telefone
     *
     * @param string $telefone
     * @return Secretaria
     */
    public function setTelefone($telefone)
    {
        $this->telefone = $telefone;

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
     * Set urlSite
     *
     * @param string $urlSite
     * @return Secretaria
     */
    public function setUrlSite($urlSite)
    {
        $this->urlSite = $urlSite;

        return $this;
    }

    /**
     * Get urlSite
     *
     * @return string 
     */
    public function getUrlSite()
    {
        return $this->urlSite;
    }

    /**
     * Set mapa
     *
     * @param string $mapa
     * @return Secretaria
     */
    public function setMapa($mapa)
    {
        $this->mapa = $mapa;

        return $this;
    }

    /**
     * Get mapa
     *
     * @return string 
     */
    public function getMapa()
    {
        return $this->mapa;
    }

    /**
     * Set email
     *
     * @param string $email
     * @return Secretaria
     */
    public function setEmail($email)
    {
        $this->email = $email;

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
     * Set dataCadastro
     *
     * @param \DateTime $dataCadastro
     * @return Secretaria
     */
    public function setDataCadastro($dataCadastro)
    {
        $this->dataCadastro = $dataCadastro;

        return $this;
    }

    /**
     * Get dataCadastro
     *
     * @return \DateTime 
     */
    public function getDataCadastro()
    {
        return $this->dataCadastro;
    }

    /**
     * Set ativo
     *
     * @param boolean $ativo
     * @return Secretaria
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
     * Set secretario
     *
     * @param \Pmc\IntranetBundle\Entity\Usuario $secretario
     * @return Comentario
     */
    public function setSecretario(\Pmc\IntranetBundle\Entity\Usuario $secretario = null)
    {
        $this->secretario = $secretario;

        return $this;
    }

    /**
     * Get secretario
     *
     * @return \Pmc\IntranetBundle\Entity\Usuario 
     */
    public function getSecretario()
    {
        return $this->secretario;
    }
    
    /**
     * Add servicos
     *
     * @param \Pmc\IntranetBundle\Entity\Servico $servicos
     * @return Secretaria
     */
    public function addServico(\Pmc\IntranetBundle\Entity\Servico $servicos)
    {
        $this->servicos[] = $servicos;

        return $this;
    }

    /**
     * Remove servicos
     *
     * @param \Pmc\IntranetBundle\Entity\Servico $servicos
     */
    public function removeServico(\Pmc\IntranetBundle\Entity\Servico $servicos)
    {
        $this->servicos->removeElement($servicos);
    }

    /**
     * Get servico
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getServicos()
    {
        return $this->servicos;
    }
    
    /**
     * Add usuarios
     *
     * @param \Pmc\IntranetBundle\Entity\Usuario $usuarios
     * @return Secretaria
     */
    public function addUsuario(\Pmc\IntranetBundle\Entity\Usuario $usuarios)
    {
        $this->usuarios[] = $usuarios;

        return $this;
    }

    /**
     * Remove usuarios
     *
     * @param \Pmc\IntranetBundle\Entity\Usuario $usuarios
     */
    public function removeUsuario(\Pmc\IntranetBundle\Entity\Usuario $usuarios)
    {
        $this->usuarios->removeElement($usuarios);
    }

    /**
     * Get usuarios
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getUsuarios()
    {
        return $this->usuarios;
    }    
    
    /**
     * @inheritDoc
     */      
    public function __toString()
    {
       return $this->getNome();
    }      
}
