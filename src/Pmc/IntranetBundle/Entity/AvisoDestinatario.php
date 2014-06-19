<?php

namespace Pmc\IntranetBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * AvisoDestinatario
 *
 * @ORM\Table(name="aviso_destinatario", indexes={@ORM\Index(name="fk_aviso_destino_idx", columns={"aviso_id"}), @ORM\Index(name="fk_destinatario_idx", columns={"usuario_id"})})
 * @ORM\Entity
 */
class AvisoDestinatario
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
     * @var boolean
     *
     * @ORM\Column(name="leido", type="boolean", nullable=false)
     */
    private $leido = false;

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
     * Constructor
     */
    public function __construct()
    {
        $this->leido = false;
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
     * Set leido
     *
     * @param boolean $leido
     * @return AvisoDestinatario
     */
    public function setLeido($leido)
    {
        $this->leido = $leido;

        return $this;
    }

    /**
     * Get leido
     *
     * @return boolean 
     */
    public function getLeido()
    {
        return $this->leido;
    }

    /**
     * Set aviso
     *
     * @param \Pmc\IntranetBundle\Entity\Aviso $aviso
     * @return AvisoDestinatario
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
     * @return AvisoDestinatario
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
     * @inheritDoc
     */      
    public function __toString()
    {
       return $this->getUsuario()->getNome();
    }        
}
