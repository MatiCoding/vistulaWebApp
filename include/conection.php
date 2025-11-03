<?php
// Conection to mySQL using mysqli
$conexion = new mysqli("localhost", "root", "", "vistula_db");


if ($conexion->connect_error) {
    die("Error de conexión: " . $conexion->connect_error);
}

echo "✅ Conexión exitosa a la base de datos.";
?>
