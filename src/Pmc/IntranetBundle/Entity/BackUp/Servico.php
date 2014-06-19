<?php

namespace Pmc\IntranetBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Servico
 *
 * @ORM\Table(name="servico", indexes={@ORM\Index(name="fk_secretaria_servico_idx", columns={"secretaria_id"})})
 * @ORM\Entity(repositoryClass="Pmc\IntranetBundle\Entity\ServicoRepository")
 */
class Servico
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
    private $ativo = true;

    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    private $solicitacaoServico;   
    
    /**
     * @var \Secretaria
     *
     * @ORM\ManyToOne(targetEntity="Secretaria")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="secretaria_id", referencedColumnName="id")
     * })
     */
    private $secretaria;

    /**
     * @var \Doctrine\Common\Collections\Collection
     *
     * @ORM\ManyToMany(targetEntity="Usuario", inversedBy="servico")
     * @ORM\JoinTable(name="responsavel_servico",
     *   joinColumns={
     *     @ORM\JoinColumn(name="servico_id", referencedColumnName="id")
     *   },
     *   inverseJoinColumns={
     *     @ORM\JoinColumn(name="usuario_id", referencedColumnName="id")
     *   }
     * )
     */
    private $responsavel;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->responsavel = new \Doctrine\Common\Collections\ArrayCollection();
        $this->solicitacaoServico = new \Doctrine\Common\Collections\ArrayCollection();
        $this->data = new \DateTime('now');
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
     * Add solicitacaoServico
     *
     * @param \Pmc\IntranetBundle\Entity\SolicitacaoServico $solicitacaoServico
     * @return Servico
     */
    public function addSolicitacaoServico(\Pmc\IntranetBundle\Entity\SolicitacaoServico $solicitacaoServico)
    {
        $this->solicitacaoServico[] = $solicitacaoServico;

        return $this;
    }

    /**
     * Remove solicitacaoServico
     *
     * @param \Pmc\IntranetBundle\Entity\SolicitacaoServico $solicitacaoServico
     */
    public function removeSolicitacaoServico(\Pmc\IntranetBundle\Entity\SolicitacaoServico $solicitacaoServico)
    {
        $this->solicitacaoServico->removeElement($solicitacaoServico);
    }

    /**
     * Get solicitacaoServico
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getSolicitacaoServico()
    {
        return $this->solicitacaoServico;
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

    /**
     * Add responsavel
     *
     * @param \Pmc\IntranetBundle\Entity\Usuario $responsavel
     * @return Servico
     */
    public function addResponsavel(\Pmc\IntranetBundle\Entity\Usuario $responsavel)
    {
        $this->responsavel[] = $responsavel;

        return $this;
    }

    /**
     * Remove responsavel
     *
     * @param \Pmc\IntranetBundle\Entity\Usuario $responsavel
     */
    public function removeResponsavel(\Pmc\IntranetBundle\Entity\Usuario $responsavel)
    {
        $this->responsavel->removeElement($responsavel);
    }

    /**
     * Get responsavel
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getResponsavel()
    {
        return $this->responsavel;
    }
    
    public function __toString() 
    {
        return $this->nome;
    }
}
