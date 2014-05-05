<?php

namespace Pmc\IntranetBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Publicacao
 *
 * @ORM\Table(name="publicacao", indexes={@ORM\Index(name="fk_postante_idx", columns={"usuario_id"}), @ORM\Index(name="fk_tipo_idx", columns={"tipo_id"})})
 * @ORM\Entity(repositoryClass="Pmc\IntranetBundle\Entity\PublicacaoRepository")
 */
class Publicacao
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="data", type="datetime", nullable=false)
     */
    private $data;

    /**
     * @var string
     *
     * @ORM\Column(name="titulo", type="text", nullable=false)
     */
    private $titulo;

    /**
     * @var string
     *
     * @ORM\Column(name="sumario", type="text", nullable=true)
     */
    private $sumario;

    /**
     * @var string
     *
     * @ORM\Column(name="conteudo", type="text", nullable=true)
     */
    private $conteudo;

    /**
     * @var string
     *
     * @ORM\Column(name="video", type="text", nullable=true)
     */
    private $video;

    /**
     * @var string
     *
     * @ORM\Column(name="imagem", type="string", length=50, nullable=true)
     */
    private $imagem;

    /**
     * @var boolean
     *
     * @ORM\Column(name="ativo", type="boolean", nullable=false)
     */
    private $ativo;

    /**
     * @var boolean
     *
     * @ORM\Column(name="publico", type="boolean", nullable=false)
     */
    private $publico;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="data_evento", type="date", nullable=true)
     */
    private $dataEvento;

    /**
     * @var \Pmc\IntranetBundle\Entity\TipoPublicacao
     *
     * @ORM\ManyToOne(targetEntity="Pmc\IntranetBundle\Entity\TipoPublicacao")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="tipo_id", referencedColumnName="id")
     * })
     */
    private $tipo;

    /**
     * @var \Pmc\IntranetBundle\Entity\Usuario
     *
     * @ORM\ManyToOne(targetEntity="Pmc\IntranetBundle\Entity\Usuario")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="usuario_id", referencedColumnName="id")
     * })
     */
    private $usuario;



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
     * @return Publicacao
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
     * @return Publicacao
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
     * Set sumario
     *
     * @param string $sumario
     * @return Publicacao
     */
    public function setSumario($sumario)
    {
        $this->sumario = $sumario;

        return $this;
    }

    /**
     * Get sumario
     *
     * @return string 
     */
    public function getSumario()
    {
        return $this->sumario;
    }

    /**
     * Set conteudo
     *
     * @param string $conteudo
     * @return Publicacao
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
     * Set video
     *
     * @param string $video
     * @return Publicacao
     */
    public function setVideo($video)
    {
        $this->video = $video;

        return $this;
    }

    /**
     * Get video
     *
     * @return string 
     */
    public function getVideo()
    {
        return $this->video;
    }

    /**
     * Set imagem
     *
     * @param string $imagem
     * @return Publicacao
     */
    public function setImagem($imagem)
    {
        $this->imagem = $imagem;

        return $this;
    }

    /**
     * Get imagem
     *
     * @return string 
     */
    public function getImagem()
    {
        return $this->imagem;
    }

    /**
     * Set ativo
     *
     * @param boolean $ativo
     * @return Publicacao
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
     * Set publico
     *
     * @param boolean $publico
     * @return Publicacao
     */
    public function setPublico($publico)
    {
        $this->publico = $publico;

        return $this;
    }

    /**
     * Get publico
     *
     * @return boolean 
     */
    public function getPublico()
    {
        return $this->publico;
    }

    /**
     * Set dataEvento
     *
     * @param \DateTime $dataEvento
     * @return Publicacao
     */
    public function setDataEvento($dataEvento)
    {
        $this->dataEvento = $dataEvento;

        return $this;
    }

    /**
     * Get dataEvento
     *
     * @return \DateTime 
     */
    public function getDataEvento()
    {
        return $this->dataEvento;
    }

    /**
     * Set tipo
     *
     * @param \Pmc\IntranetBundle\Entity\TipoPublicacao $tipo
     * @return Publicacao
     */
    public function setTipo(\Pmc\IntranetBundle\Entity\TipoPublicacao $tipo = null)
    {
        $this->tipo = $tipo;

        return $this;
    }

    /**
     * Get tipo
     *
     * @return \Pmc\IntranetBundle\Entity\TipoPublicacao 
     */
    public function getTipo()
    {
        return $this->tipo;
    }

    /**
     * Set usuario
     *
     * @param \Pmc\IntranetBundle\Entity\Usuario $usuario
     * @return Publicacao
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
