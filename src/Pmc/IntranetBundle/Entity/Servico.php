<?php

namespace Pmc\IntranetBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Servico
 *
 * @ORM\Table(name="servico", indexes={@ORM\Index(name="fk_secretaria_servico_idx", columns={"secretaria_id"}), @ORM\Index(name="fk_responsavel_idx", columns={"usuario_id"})})
 * @ORM\Entity
 */
class Servico
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
     * @ORM\Column(name="nome", type="string", length=100, nullable=false)
     */
    private $nome;

    /**
     * @var string
     *
     * @ORM\Column(name="conteudo", type="text", nullable=true)
     */
    private $conteudo;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="data", type="datetime", nullable=false)
     */
    private $data;

    /**
     * @var boolean
     *
     * @ORM\Column(name="ativo", type="boolean", nullable=false)
     */
    private $ativo;

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
     * @var \Pmc\IntranetBundle\Entity\Secretaria
     *
     * @ORM\ManyToOne(targetEntity="Pmc\IntranetBundle\Entity\Secretaria")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="secretaria_id", referencedColumnName="id")
     * })
     */
    private $secretaria;



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
     * @return Servico
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
     * Set conteudo
     *
     * @param string $conteudo
     * @return Servico
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
     * Set data
     *
     * @param \DateTime $data
     * @return Servico
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
     * Set ativo
     *
     * @param boolean $ativo
     * @return Servico
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
     * Set usuario
     *
     * @param \Pmc\IntranetBundle\Entity\Usuario $usuario
     * @return Servico
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
     * Set secretaria
     *
     * @param \Pmc\IntranetBundle\Entity\Secretaria $secretaria
     * @return Servico
     */
    public function setSecretaria(\Pmc\IntranetBundle\Entity\Secretaria $secretaria = null)
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
}
