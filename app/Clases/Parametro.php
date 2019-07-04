<?php
/**
 * Created by PhpStorm.
 * User: josue
 * Date: 19/03/19
 * Time: 03:00 PM
 */

namespace App\Clases;


class Parametro
{
    private $host_smtp_novazys;
    private $usuario_smtp_novazys;
    private $contrasena_smtp_novazys;
    private $puerto_smtp_novazys;
    private $metodo_smtp_novazys;
    private $easy_atv_server_prod;
    private $easy_atv_server_test;


    public function __construct($host_smtp_novazys,$usuario_smtp_novazys,$contrasena_smtp_novazys,$puerto_smtp_novazys,$metodo_smtp_novazys,$easy_atv_server_prod,$easy_atv_server_test)
    {
        $this->host_smtp_novazys=$host_smtp_novazys;
        $this->usuario_smtp_novazys=$usuario_smtp_novazys;
        $this->contrasena_smtp_novazys=$contrasena_smtp_novazys;
        $this->puerto_smtp_novazys=$puerto_smtp_novazys;
        $this->metodo_smtp_novazys=$metodo_smtp_novazys;
        $this->easy_atv_server_prod=$easy_atv_server_prod;
        $this->easy_atv_server_test=$easy_atv_server_test;

    }
    public function setValues()
    {
            $path = base_path('.env');
            $parametros=array('SMTP_HOST'=>$this->host_smtp_novazys,'SMTP_USERNAME'=>$this->usuario_smtp_novazys,'SMTP_CONTRASENA'=>$this->contrasena_smtp_novazys,'SMTP_PUERTO'=>$this->puerto_smtp_novazys,'SMTP_METODO'=>$this->metodo_smtp_novazys,'EASY_ATV_SERVER_PROD'=>$this->easy_atv_server_prod,'EASY_ATV_SERVER_TEST'=>$this->easy_atv_server_test);
            foreach ($parametros as $key =>$value)
            {
                file_put_contents($path, str_replace(
                    $key.'='.env($key), $key.'='.$value, file_get_contents($path)
                ));
            }
            return 1;
    }
}
