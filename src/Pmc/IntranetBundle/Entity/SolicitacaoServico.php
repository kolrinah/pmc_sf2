<?php

namespace Pmc\IntranetBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * SolicitacaoServico
 *
 * @ORM\Table(name="solicitacao_servico", indexes={@ORM\Index(name="fk_servico_idx", columns={"servico_id"}), @ORM\Index(name="fk_requerente_idx", columns={"usuario_id"})})
 * @ORM\Entity
 */
class SolicitacaoServico
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
     * @var \DateTime
     *
     * @ORM\Column(name="data", type="datetime", nullable=true)
     */
    private $data;

    /**
     * @var string
     *
     * @ORM\Column(name="titulo", type="string", length=100, nullable=false)
     */
    private $titulo;

    /**
     * @var string
     *
     * @ORM\Column(name="conteudo", type="text", nullable=true)
     */
    private $conteudo;

    /**
     * @var boolean
     *
     * @ORM\Column(name="aberto_usuario", type="boolean", nullable=false)
     */
    private $abertoUsuario = true;
    
    /**
     * @var boolean
     *
     * @ORM\Column(name="aberto_secretaria", type="boolean", nullable=false)
     */
    private $abertoSecretaria = true;    
    
    /**
     * @var boolean
     *
     * @ORM\Column(name="urgente", type="boolean", nullable=false)
     */
    private $urgente = false;    
    
    /**
     * @var string
     *
     * @ORM\Column(name="codigo_interno", type="string", length=20, nullable=false)
     */
    private $codigoInterno;    
    
    /**
     * @var \Servico
     *
     * @ORM\ManyToOne(targetEntity="Servico")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="servico_id", referencedColumnName="id")
     * })
     */
    private $servico;

    /**
     * @var \Usuario
     *
     * @ORM\ManyToOne(targetEntity="Usuario")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="usuario_id", referencedColumnName="id")
     * })
     */
    private $usuario;


    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    private $comentarios;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->comentarios = new \Doctrine\Common\Collections\ArrayCollection();
        $this->data = new \DateTime('now');
        $this->abertoSecretaria = true;  
        $this->abertoUsuario = true;  
        $this->codigoInterno = '000000';
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
     * Set data
     *
     * @param \DateTime $data
     * @return SolicitacaoServico
     */
    public function setData($data)
    {
        $this->data = $data;

        return $this;
    }

    /**
     * Get data
     *
     * @return \DateTime 
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * Set titulo
     *
     * @param string $titulo
     * @return SolicitacaoServico
     */
    public function setTitulo($titulo)
    {
        $this->titulo = $titulo;

        return $this;
    }

    /**
     * Get titulo
     *
     * @return string 
     */
    public function getTitulo()
    {
        return $this->titulo;
    }

    /**
     * Set conteudo
     *
     * @param string $conteudo
     * @return SolicitacaoServico
     */
    public function setConteudo($conteudo)
    {
        $this->conteudo = $conteudo;

        return $this;
    }

    /**
     * Get conteudo
     *
     * @return string 
     */
    public function getConteudo()
    {
        return $this->conteudo;
    }

    /**
     * Set abertoUsuario
     *
     * @param boolean $abertoUsuario
     * @return SolicitacaoServico
     */
    public function setAbertoUsuario($abertoUsuario)
    {
        $this->abertoUsuario = $abertoUsuario;

        return $this;
    }

    /**
     * Get abertoUsuario
     *
     * @return boolean 
     */
    public function getAbertoUsuario()
    {
        return $this->abertoUsuario;
    }

    /**
     * Set abertoSecretaria
     *
     * @param boolean $abertoSecretaria
     * @return SolicitacaoServico
     */
    public function setAbertoSecretaria($abertoSecretaria)
    {
        $this->abertoSecretaria = $abertoSecretaria;

        return $this;
    }

    /**
     * Get abertoSecretaria
     *
     * @return boolean 
     */
    public function getAbertoSecretaria()
    {
        return $this->abertoSecretaria;
    }
    
    /**
     * Set urgente
     *
     * @param boolean $urgente
     * @return SolicitacaoServico
     */
    public function setUrgente($urgente)
    {
        $this->urgente = $urgente;

        return $this;
    }

    /**
     * Get urgente
     *
     * @return boolean 
     */
    public function getUrgente()
    {
        return $this->urgente;
    }
    
    /**
     * Set codigoInterno
     *
     * @param string $codigoInterno
     * @return SolicitacaoServico
     */
    public function setCodigoInterno($codigoInterno)
    {
        $this->codigoInterno = $codigoInterno;

        return $this;
    }

    /**
     * Get codigoInterno
     *
     * @return string 
     */
    public function getCodigoInterno()
    {
        return $this->codigoInterno;
    }    
    
    /**
     * Add comentarios
     *
     * @param \Pmc\IntranetBundle\Entity\ComentarioSolicitacaoServico $comentarios
     * @return SolicitacaoServico
     */
    public function addComentario(\Pmc\IntranetBundle\Entity\ComentarioSolicitacaoServico $comentarios)
    {
        $this->comentarios[] = $comentarios;

        return $this;
    }

    /**
     * Remove comentarios
     *
     * @param \Pmc\IntranetBundle\Entity\ComentarioSolicitacaoServico $comentarios
     */
    public function removeComentario(\Pmc\IntranetBundle\Entity\ComentarioSolicitacaoServico $comentarios)
    {
        $this->comentarios->removeElement($comentarios);
    }

    /**
     * Get comentarios
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getComentarios()
    {
        return $this->comentarios;
    }

    /**
     * Set servico
     *
     * @param \Pmc\IntranetBundle\Entity\Servico $servico
     * @return SolicitacaoServico
     */
    public function setServico(\Pmc\IntranetBundle\Entity\Servico $servico = null)
    {
        $this->servico = $servico;

        return $this;
    }

    /**
     * Get servico
     *
     * @return \Pmc\IntranetBundle\Entity\Servico 
     */
    public function getServico()
    {
        return $this->servico;
    }

    /**
     * Set usuario
     *
     * @param \Pmc\IntranetBundle\Entity\Usuario $usuario
     * @return SolicitacaoServico
     */
    public function setUsuario(\Pmc\IntranetBundle\Entity\Usuario $usuario = null)
    {
        $this->usuario = $usuario;

        return $this;
    }

    /**
     * Get usuario
     *
     * @return \Pmc\IntranetBundle\Entity\Usuario 
     */
    public function getUsuario()
    {
        return $this->usuario;
    }
}
