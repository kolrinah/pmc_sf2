<?php

namespace Pmc\IntranetBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Aviso
 *
 * @ORM\Table(name="aviso", uniqueConstraints={@ORM\UniqueConstraint(name="id_UNIQUE", columns={"id"})}, indexes={@ORM\Index(name="fk_remetente_idx", columns={"usuario_id"})})
 * @ORM\Entity(repositoryClass="Pmc\IntranetBundle\Entity\AvisoRepository")
 */
class Aviso
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
     * @var \DateTime
     *
     * @ORM\Column(name="data", type="datetime", nullable=false)
     */
    private $data;

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
     * @ORM\Column(name="importante", type="boolean", nullable=false)
     */
    private $importante = false;

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
     * @var \Doctrine\Common\Collections\Collection
     */
    private $comentarios;

    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    private $destinatarios;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->comentarios = new \Doctrine\Common\Collections\ArrayCollection();
        $this->destinatarios = new \Doctrine\Common\Collections\ArrayCollection();
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
     * Set titulo
     *
     * @param string $titulo
     * @return Aviso
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
     * @return Aviso
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
     * @return Aviso
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
     * Set video
     *
     * @param string $video
     * @return Aviso
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
     * @return Aviso
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
     * Set importante
     *
     * @param boolean $importante
     * @return Aviso
     */
    public function setImportante($importante)
    {
        $this->importante = $importante;

        return $this;
    }

    /**
     * Get importante
     *
     * @return boolean 
     */
    public function getImportante()
    {
        return $this->importante;
    }

    /**
     * Add comentarios
     *
     * @param \Pmc\IntranetBundle\Entity\AvisoComentario $comentarios
     * @return Aviso
     */
    public function addComentario(\Pmc\IntranetBundle\Entity\AvisoComentario $comentarios)
    {
        $this->comentarios[] = $comentarios;

        return $this;
    }

    /**
     * Remove comentarios
     *
     * @param \Pmc\IntranetBundle\Entity\AvisoComentario $comentarios
     */
    public function removeComentario(\Pmc\IntranetBundle\Entity\AvisoComentario $comentarios)
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
     * Add destinatarios
     *
     * @param \Pmc\IntranetBundle\Entity\AvisoDestinatario $destinatarios
     * @return Aviso
     */
    public function addDestinatario(\Pmc\IntranetBundle\Entity\AvisoDestinatario $destinatarios)
    {
        $this->destinatarios[] = $destinatarios;

        return $this;
    }

    /**
     * Remove destinatarios
     *
     * @param \Pmc\IntranetBundle\Entity\AvisoDestinatario $destinatarios
     */
    public function removeDestinatario(\Pmc\IntranetBundle\Entity\AvisoDestinatario $destinatarios)
    {
        $this->destinatarios->removeElement($destinatarios);
    }

    /**
     * Get destinatarios
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getDestinatarios()
    {
        return $this->destinatarios;
    }

    /**
     * Set usuario
     *
     * @param \Pmc\IntranetBundle\Entity\Usuario $usuario
     * @return Aviso
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
       return $this->getTitulo();
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
