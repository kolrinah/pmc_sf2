<?php

namespace Pmc\IntranetBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * LeiDecreto
 *
 * @ORM\Table(name="lei_decreto")
 * @ORM\Entity
 */
class LeiDecreto
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
     * @var integer
     *
     * @ORM\Column(name="ano", type="smallint", nullable=false)
     */
    private $ano;

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
     * @var boolean
     *
     * @ORM\Column(name="lei", type="boolean", nullable=false)
     */
    private $lei;

    /**
     * @var string
     *
     * @ORM\Column(name="arquivo", type="string", length=50, nullable=false)
     */
    private $arquivo;



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
     * Set ano
     *
     * @param integer $ano
     * @return LeiDecreto
     */
    public function setAno($ano)
    {
        $this->ano = $ano;

        return $this;
    }

    /**
     * Get ano
     *
     * @return integer 
     */
    public function getAno()
    {
        return $this->ano;
    }

    /**
     * Set nome
     *
     * @param string $nome
     * @return LeiDecreto
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
     * @return LeiDecreto
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
     * Set lei
     *
     * @param boolean $lei
     * @return LeiDecreto
     */
    public function setLei($lei)
    {
        $this->lei = $lei;

        return $this;
    }

    /**
     * Get lei
     *
     * @return boolean 
     */
    public function getLei()
    {
        return $this->lei;
    }

    /**
     * Set arquivo
     *
     * @param string $arquivo
     * @return LeiDecreto
     */
    public function setArquivo($arquivo)
    {
        $this->arquivo = $arquivo;

        return $this;
    }

    /**
     * Get arquivo
     *
     * @return string 
     */
    public function getArquivo()
    {
        return $this->arquivo;
    }
}
