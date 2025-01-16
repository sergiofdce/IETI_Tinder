<?php
session_start();
//persistencia de sesiones, si el usuario se ha logueado, se redirige a discober
if (isset($_SESSION["user"])) {
      header("Location: discover.php");
} else {
      header("Location: login.php");
}
