<?php

namespace Pmc\IntranetBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * ComentarioSolicitacaoServico
 *
 * @ORM\Table(name="comentario_solicitacao_servico", indexes={@ORM\Index(name="fk_comentarista_solicitacao_idx", columns={"usuario_id"}), @ORM\Index(name="fk_comentario_solicitacao_servico_idx", columns={"solicitacao_servico_id"})})
 * @ORM\Entity
 */
class ComentarioSolicitacaoServico
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
     * @var \Usuario
     *
     * @ORM\ManyToOne(targetEntity="Usuario")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="usuario_id", referencedColumnName="id")
     * })
     */
    private $usuario;

    /**
     * @var \SolicitacaoServico
     *
     * @ORM\ManyToOne(targetEntity="SolicitacaoServico")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="solicitacao_servico_id", referencedColumnName="id")
     * })
     */
    private $solicitacaoServico;



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
     * @return ComentarioSolicitacaoServico
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
     * @return ComentarioSolicitacaoServico
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
     * Set usuario
     *
     * @param \Pmc\IntranetBundle\Entity\Usuario $usuario
     * @return ComentarioSolicitacaoServico
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
     * Set solicitacaoServico
     *
     * @param \Pmc\IntranetBundle\Entity\SolicitacaoServico $solicitacaoServico
     * @return ComentarioSolicitacaoServico
     */
    public function setSolicitacaoServico(\Pmc\IntranetBundle\Entity\SolicitacaoServico $solicitacaoServico = null)
    {
        $this->solicitacaoServico = $solicitacaoServico;

        return $this;
    }

    /**
     * Get solicitacaoServico
     *
     * @return \Pmc\IntranetBundle\Entity\SolicitacaoServico 
     */
    public function getSolicitacaoServico()
    {
        return $this->solicitacaoServico;
    }
}
