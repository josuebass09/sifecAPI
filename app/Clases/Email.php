<?php
/**
 * Created by PhpStorm.
 * User: josue
 * Date: 19/03/19
 * Time: 03:00 PM
 */

namespace App\Clases;


class Email
{
    private $xml_firmado;
    private $xml_respuesta_hacienda;
    private $clave;
    private $email_receptor;
    private $nombre_receptor;
    private $tipo_comprobante;
    private $nombre_emi;
    private $numeracion;
    private $n_comercial;

    public function __construct($xml_firmado,$xml_respuesta_hacienda,$clave,$email_receptor,$nombre_receptor,$tipo_comprobante,$nombre_emi,$numeracion,$n_comercial)
    {
        $this->xml_firmado=$xml_firmado;
        $this->xml_respuesta_hacienda=$xml_respuesta_hacienda;
        $this->clave=$clave;
        $this->email_receptor=$email_receptor;
        $this->nombre_receptor=$nombre_receptor;
        $this->tipo_comprobante=$tipo_comprobante;
        $this->nombre_emi=$nombre_emi;
        $this->numeracion=$numeracion;
        $this->n_comercial=$n_comercial;
    }

    /**
     * @return mixed
     */
    public function getXmlFirmado()
    {
        return $this->xml_firmado;
    }

    /**
     * @return mixed
     */
    public function getXmlRespuestaHacienda()
    {
        return $this->xml_respuesta_hacienda;
    }

    /**
     * @return mixed
     */
    public function getClave()
    {
        return $this->clave;
    }

    /**
     * @return mixed
     */
    public function getEmailReceptor()
    {
        return $this->email_receptor;
    }

    /**
     * @return mixed
     */
    public function getNombreReceptor()
    {
        return $this->nombre_receptor;
    }

    /**
     * @return mixed
     */
    public function getTipoComprobante()
    {
        return $this->tipo_comprobante;
    }

    /**
     * @return mixed
     */
    public function getNombreEmi()
    {
        return $this->nombre_emi;
    }

    /**
     * @return mixed
     */
    public function getNumeracion()
    {
        return $this->numeracion;
    }

    /**
     * @return mixed
     */
    public function getNComercial()
    {
        return $this->n_comercial;
    }


}
