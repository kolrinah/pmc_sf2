<?php

namespace Pmc\IntranetBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Comentario
 *
 * @ORM\Table(name="comentario", indexes={@ORM\Index(name="fk_publicacao_idx", columns={"publicacao_id"}), @ORM\Index(name="fk_comentarista_idx", columns={"usuario_id"})})
 * @ORM\Entity
 */
class Comentario
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
     * @ORM\Column(name="comentario", type="text", nullable=false)
     */
    private $comentario;

    /**
     * @var \Pmc\IntranetBundle\Entity\Publicacao
     *
     * @ORM\ManyToOne(targetEntity="Pmc\IntranetBundle\Entity\Publicacao")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="publicacao_id", referencedColumnName="id")
     * })
     */
    private $publicacao;

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
     * @return Comentario
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
     * Set comentario
     *
     * @param string $comentario
     * @return Comentario
     */
    public function setComentario($comentario)
    {
        $this->comentario = $comentario;

        return $this;
    }

    /**
     * Get comentario
     *
     * @return string 
     */
    public function getComentario()
    {
        return $this->comentario;
    }

    /**
     * Set publicacao
     *
     * @param \Pmc\IntranetBundle\Entity\Publicacao $publicacao
     * @return Comentario
     */
    public function setPublicacao(\Pmc\IntranetBundle\Entity\Publicacao $publicacao = null)
    {
        $this->publicacao = $publicacao;

        return $this;
    }

    /**
     * Get publicacao
     *
     * @return \Pmc\IntranetBundle\Entity\Publicacao 
     */
    public function getPublicacao()
    {
        return $this->publicacao;
    }

    /**
     * Set usuario
     *
     * @param \Pmc\IntranetBundle\Entity\Usuario $usuario
     * @return Comentario
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
