<?php
class DB {

    protected $db_name = BASE_PO;
    protected $db_user = USUARIO_PO;
    protected $db_pass = CLAVE_PO;
    protected $db_host = HOST_PO;
    protected $db_port = PORT_PO;
    protected $db = null;
    public $db_error = "";
    public $db_conexion = false;

    public function __construct()
    {
        try {
            $this->db = new \PDO("pgsql:host={$this->db_host};port={$this->db_port};dbname={$this->db_name};options='--client_encoding=UTF8'", $this->db_user, $this->db_pass);
            $this->db_conexion = true;
        } catch (\PDOException $e) {
            $this->db_error = $e->getMessage();
            $this->db_conexion = false;
        }

    }

    public static function returnConexion()
    {
        $conexion = new static();
        return $conexion->db;
    }

    public static function executeSelectAll($sql)
    {
        $conexion = self::returnConexion();
        return $conexion->query($sql)->fetchAll();
    }

    public static function executeSelectOne($sql)
    {
        $conexion = self::returnConexion();
        return $conexion->query($sql)->fetch();
    }

    public static function executeCommand($sql)
    {
        $conexion = self::returnConexion();
        return $conexion->exec($sql);
    }

    public static function conexion()
    {
        $conexion = self::returnConexion();
        return $conexion;
    }
}