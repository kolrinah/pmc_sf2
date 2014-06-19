<?php

namespace Pmc\IntranetBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * AvisoComentario
 *
 * @ORM\Table(name="aviso_comentario", uniqueConstraints={@ORM\UniqueConstraint(name="id_UNIQUE", columns={"id"})}, indexes={@ORM\Index(name="fk_aviso_idx", columns={"aviso_id"}), @ORM\Index(name="fk_comentarista_aviso_idx", columns={"usuario_id"})})
 * @ORM\Entity
 */
class AvisoComentario
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
     * @var \Aviso
     *
     * @ORM\ManyToOne(targetEntity="Aviso")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="aviso_id", referencedColumnName="id")
     * })
     */
    private $aviso;

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
     * @return AvisoComentario
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
     * @return AvisoComentario
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
     * Set aviso
     *
     * @param \Pmc\IntranetBundle\Entity\Aviso $aviso
     * @return AvisoComentario
     */
    public function setAviso(\Pmc\IntranetBundle\Entity\Aviso $aviso = null)
    {
        $this->aviso = $aviso;

        return $this;
    }

    /**
     * Get aviso
     *
     * @return \Pmc\IntranetBundle\Entity\Aviso 
     */
    public function getAviso()
    {
        return $this->aviso;
    }

    /**
     * Set usuario
     *
     * @param \Pmc\IntranetBundle\Entity\Usuario $usuario
     * @return AvisoComentario
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
