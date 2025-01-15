<?php
session_start();
include 'includes/functions.php';
//log de logout
logEvent("logout", "El usuario " . $_SESSION["email"] . " ha cerrado sesion", $_SESSION["email"]);
//cerrar sesion
unset($_SESSION["user"]);
unset($_SESSION["email"]);
session_destroy();
//redireccionar a index
header("Location: index.php");
?>