# 📱 EasyDates TinderClone

Una aplicación web para dispositivos móviles inspirada en Tinder, creada con HTML, CSS, JavaScript, AJAX, MySQL y PHP.

## 🌟Características

### Autenticación (Inicio de sesión/Registro)
- Registro de usuario con verificación de correo electrónico
- Sistema de inicio de sesión seguro

### Página de descubrimiento
- Perfiles de usuario estilo tarjeta
- Algoritmo de coincidencia inteligente
- Sugerencias basadas en la ubicación

### Mensajes

- Historial de chat


### Perfil
- Personalización del perfil


## 🛠️ Stack técnico

### Interfaz de usuario
- HTML5
- CSS3 (diseño responsivo para dispositivos móviles)
- JavaScript (ES6+)
- jQuery 3.6+
- AJAX para solicitudes asincrónicas
- Swipe.js para interacciones táctiles

### Backend
- PHP 8.0+
- MySQL 8.0+
- Arquitectura API RESTful
- Autenticación JWT

## 📱 Diseño Mobile-First

La aplicación está diseñada específicamente para dispositivos móviles con:
- Interfaces táctiles optimizadas
- Diseños responsivos
- Carga de imágenes optimizada
- Animaciones fluidas

## 🔧 Instalación

1. Clona el repositorio:
```bash
https://github.com/sergiofdce/IETI_Tinder.git
```

2. Configura tu servidor web (Apache/Nginx) para que apunte al directorio del proyecto

3. Importa la base de datos:

- Puedes encontrar el script en la ruta /config/tinder.sql
```bash
mysql -u username -p database_name < tinder.sql
```

4. Configura la conexión a la base de datos en `config.php`:
```php
define('DB_HOST', 'localhost');
define('DB_USER', 'su_nombre_de_usuario');
define('DB_PASS', 'su_contraseña');
define('DB_NAME', 'tinder');
```

## 📂 Estructura del proyecto

```
├── assets/
│ ├── css/
│ ├── data/
│ ├── img/
│ ├── js/
│
├── modules/
├── config/
├── docs/
├── includes/
├── logs/
│
├── login.php
├── discover.php
├── index.php
├── login.php
├── logout.php
├── messages.php
```

## 🔐 Funciones de seguridad

- Hashing de contraseñas
- Prevención de inyección SQL
- Validación de entrada

## 🙏 Contribuidores

- [sergiofdce](https://github.com/sergiofdce)
- [jpachonguerra](https://github.com/jpachonguerra)
- [estillicon](https://github.com/estillicon)
