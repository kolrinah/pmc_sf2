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
    private $data = 'CURRENT_TIMESTAMP';

    /**
     * @var string
     *
     * @ORM\Column(name="titulo", type="text", nullable=false)
     */
    private $titulo;

    /**
     * @var string
     *
     * @ORM\Column(name="resumo", type="text", nullable=true)
     */
    private $resumo;

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
    private $ativo = '1';

    /**
     * @var boolean
     *
     * @ORM\Column(name="publico", type="boolean", nullable=false)
     */
    private $publico = '1';

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="data_evento", type="date", nullable=true)
     */
    private $dataEvento;

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
     * @var \TipoPublicacao
     *
     * @ORM\ManyToOne(targetEntity="TipoPublicacao")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="tipo_id", referencedColumnName="id")
     * })
     */
    private $tipo;


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
        $this->dataEvento = new \DateTime('now');
        $this->ativo = true;
        $this->publico = false;        
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
     * Set resumo
     *
     * @param string $resumo
     * @return Publicacao
     */
    public function setResumo($resumo)
    {
        $this->resumo = $resumo;
        return $this;
    }

    /**
     * Get resumo
     *
     * @return string 
     */
    public function getResumo()
    {
        return $this->resumo;
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
     * Add comentarios
     *
     * @param \Pmc\IntranetBundle\Entity\Comentario $comentarios
     * @return Publicacao
     */
    public function addComentario(\Pmc\IntranetBundle\Entity\Comentario $comentarios)
    {
        $this->comentarios[] = $comentarios;

        return $this;
    }

    /**
     * Remove comentarios
     *
     * @param \Pmc\IntranetBundle\Entity\Comentario $comentarios
     */
    public function removeComentario(\Pmc\IntranetBundle\Entity\Comentario $comentarios)
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
    
    /* * * * * * * * * * * * * * * * * * * * * * * 
     * METODOS CREADOS MANUALMENTE
     */

    // Método para subir la foto
    public function uploadImagem($directorioDestino)
    {
        if (null === $this->imagem) return;
                
        $nombreArchivoFoto = uniqid().'.'.
                             $this->imagem->getClientOriginalExtension();
        $this->_crearDirectorio($directorioDestino);
        $this->imagem->move($directorioDestino, $nombreArchivoFoto);
        $this->setImagem($nombreArchivoFoto);
    }
    
    // METODO PARA REMOVER LA FOTO FISICAMENTE
    public function removerImagem($directorioImagen)
    {  
       $imagen =  $directorioImagen.$this->imagem;
       if (file_exists($imagen)) @unlink($imagen);         
       return true;
    }
    
    // METODO PARA CREAR DIRECTORIO
    private function _crearDirectorio($ruta)
    {
       if (!file_exists($ruta)) @mkdir($ruta, 0777, true);
       $this->_crearArchivoIndex($ruta);
    }

    //METODO PARA CREAR ARCHIVOS INDEX EN CARPETA
    private function _crearArchivoIndex($carpeta)
    {
       $archivo = $carpeta.'index.html';
       $contenido = "<html><head><title>403 Proibida</title></head>".
                    "<body>Ação Proibida.</body></html>";

       // CREAMOS EL ARCHIVO SI NO EXISTE
       if (!file_exists($archivo))
       {
          if (!$handle = @fopen($archivo, 'c')) die("No pudo abrir/crear el archivo");
          if (@fwrite($handle, $contenido) === FALSE) die("No pudo escribir en archivo index");            
          @fclose($handle);
       }
       return true;
    }        
}
