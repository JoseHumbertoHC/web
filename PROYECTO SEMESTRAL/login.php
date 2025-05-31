<?php
session_start();

$dbhost = "localhost";
$dbuser = "root";
$dbpass = "";
$dbname = "test";

$conn = mysqli_connect($dbhost, $dbuser, $dbpass, $dbname);
if (!$conn) {
    die("Error de conexión: " . mysqli_connect_error());
}

// Limpieza básica de inputs
$nombre = mysqli_real_escape_string($conn, $_POST["txtusuario"]);
$pass = mysqli_real_escape_string($conn, $_POST["txtpassword"]);

// Consulta preparada (seguridad mejorada)
$stmt = $conn->prepare("SELECT usuario FROM login WHERE usuario = ? AND password = ?");
$stmt->bind_param("ss", $nombre, $pass);
$stmt->execute();
$stmt->store_result();

if($stmt->num_rows == 1) {
    // Autenticación exitosa
    $_SESSION['autenticado'] = true;
    $_SESSION['usuario'] = $nombre;
    $_SESSION['expira'] = time() + 3600; // 1 hora de sesión
    
    // También almacenamos en localStorage para JS
    $authData = json_encode([
        'isAuthenticated' => true,
        'expires' => (time() + 3600) * 1000 // Convertir a milisegundos
    ]);
    
    // Redirigir a menu.html con los datos de autenticación
    echo "<script>
            localStorage.setItem('auth', '".addslashes($authData)."');
            window.location.href = 'menu.html';
          </script>";
    exit();
} else {
    // Mostrar error y redirigir después de 3 segundos
    echo "<script>
            alert('Usuario o contraseña incorrectos');
            setTimeout(function() {
                window.location.href = 'LOGIN.html';
            }, 3000);
          </script>";
}

$stmt->close();
$conn->close();
?>