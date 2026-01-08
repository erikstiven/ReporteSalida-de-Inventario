<?php
// Definición de la configuración de las conexiones en un solo define
define('DATABASE_CONFIG', [
    'conexion_1' => [
        //'db'     => 'minesadco2024',
        'db'     => 'logistica2025',
        'motor'  => 'pgsql',
        'host'   => '66.179.208.214',
        'port'   => '54320',
        'usuario'=> 'sisconti',
        'clave'  => 'JB34na4Ac'
    ]
]);

class Connection {
    private $connection;

    public function __construct(&$connection) {
        $this->connection = $connection;

        if (!$this->connection) {
            throw new Exception("Error de conexión a la base de datos: " . pg_last_error());
        }
    }
    public function getConnection() {
        return $this->connection;
    }
    public function query($query) {
        $result = pg_query($this->connection, $query);

        if (!$result) {
            throw new Exception("Error en la consulta: " . pg_last_error($this->connection));
        }

        return pg_fetch_all($result);
    }
}

// Uso

class Database {
    private $connections = [];
    private $principalDb = 'conexion_1'; // Base de datos principal

    public function __construct() {
        $this->initializeConnections();
    }

    // Método para inicializar las conexiones a todas las bases de datos
    public function initializeConnections() {
        $configs = DATABASE_CONFIG; // Usar la constante definida

        foreach ($configs as $key => $config) {
            $conn_string = "host=" . $config['host'] .
                " port=" . $config['port'] .
                " dbname=" . $config['db'] .
                " user=" . $config['usuario'] .
                " password=" . $config['clave'];

            $connection = pg_connect($conn_string);

            if (!$connection) {
                throw new Exception("Error de conexión a la base de datos $key: " . pg_last_error());
            }

            $this->connections[$key] = $connection;
        }

        return "Todas las conexiones se realizaron con éxito."; // Mensaje de éxito
    }
    // Método para ejecutar una consulta en todas las bases de datos
    public function executeQueryInAllDatabases($query) {
        try{
            $results = [];
            foreach ($this->connections as $key => $connection) {
                $result = pg_query($connection, $query);
                if (!$result) {
                    throw new Exception("Error en la base de datos $key: " . pg_last_error($connection));
                } else {
                    $results[$key] = "Éxito en la base de datos $key";
                }
            }
            return $results;
        }catch(\Throwable $e){
            throw new Exception("Error en la base de datos principal: {$e->getMessage()}\n");
        }
    }

    // Método para ejecutar consultas SELECT en la base de datos principal
    public function executeQuery($query, $connectionKey = null) {
        try{
            $principalConnection = empty($connectionKey) ? $this->connections[$this->principalDb] : $this->connections[$connectionKey];
            $result = pg_query($principalConnection, $query);

            if (!$result) {
                throw new Exception("No hay respuesta en la base de datos principal");
            }

            return pg_fetch_all($result);
        }catch(\Throwable $e){
            throw new Exception("Error en la base de datos principal: {$e->getMessage()}\n".pg_last_error($principalConnection) );
        }
    }

    public function transaction($action, $connectionKey = null){
        $connection = empty($connectionKey) ? $this->connections[$this->principalDb] : $this->connections[$connectionKey];
        try {
            pg_query($connection,"BEGIN");
            $result = $action(new Connection($connection));
            pg_query($connection,"COMMIT");
            return $result;
        } catch (\Throwable $e) {
            pg_query($connection,"ROLLBACK");
            throw $e;
        }
    }

    // Método para cerrar conexion a las base de datos
    public function closeAllConnections() {
        foreach ($this->connections as $connection) {
            pg_close($connection);
        }
        $this->connections = []; // Limpiar el array de conexiones
    }
}
