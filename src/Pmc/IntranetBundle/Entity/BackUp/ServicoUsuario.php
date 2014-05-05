<?php

namespace Pmc\IntranetBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * ServicoUsuario
 *
 * @ORM\Table(name="servico_usuario", indexes={@ORM\Index(name="fk_servico_idx", columns={"servico_id"}), @ORM\Index(name="fk_requerente_idx", columns={"usuario_id"})})
 * @ORM\Entity
 */
class ServicoUsuario
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
     * @ORM\Column(name="aberto", type="boolean", nullable=false)
     */
    private $aberto;

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
     * @var \Pmc\IntranetBundle\Entity\Servico
     *
     * @ORM\ManyToOne(targetEntity="Pmc\IntranetBundle\Entity\Servico")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="servico_id", referencedColumnName="id")
     * })
     */
    private $servico;



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
     * Set titulo
     *
     * @param string $titulo
     * @return ServicoUsuario
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
     * @return ServicoUsuario
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
     * Set aberto
     *
     * @param boolean $aberto
     * @return ServicoUsuario
     */
    public function setAberto($aberto)
    {
        $this->aberto = $aberto;

        return $this;
    }

    /**
     * Get aberto
     *
     * @return boolean 
     */
    public function getAberto()
    {
        return $this->aberto;
    }

    /**
     * Set usuario
     *
     * @param \Pmc\IntranetBundle\Entity\Usuario $usuario
     * @return ServicoUsuario
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

    /**
     * Set servico
     *
     * @param \Pmc\IntranetBundle\Entity\Servico $servico
     * @return ServicoUsuario
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
}
