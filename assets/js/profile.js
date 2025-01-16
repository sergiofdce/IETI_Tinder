// ==================================================
// Formulario de perfil
// ==================================================

// Función para mostrar mensajes
function showMessage(type, message) {
    const container = document.getElementById("notificationContainer");

    // Crear un nuevo elemento de mensaje
    const notification = document.createElement("div");
    notification.classList.add("messenger");

    // Determinar el estilo y texto del mensaje
    switch (type) {
        case 'error':
            notification.classList.add("divNotiError");
            notification.innerText = message || "¡Error! Algo salió mal";
            break;
        case 'like':
            notification.classList.add("divNotiLike");
            notification.innerText = message || "¡Te han dado un like!";
            break;
        case 'nope':
            notification.classList.add("divNotiNope");
            notification.innerText = message || "Lo siento, no es una coincidencia.";
            break;
        case 'success':
            notification.classList.add("divNotiSuccess");
            notification.innerText = message || "¡Cambio realizado con éxito!";
            break;
        case 'warning':
            notification.classList.add("divNotiWarning");
            notification.innerText = message || "¡Advertencia! Algo podría no estar bien.";
            break;
        default:
            notification.classList.add("divNotiOther");
            notification.innerText = message || "Notificación sin tipo específico.";
            break;
    }

    // Añadir el mensaje al contenedor
    container.appendChild(notification);

    // Eliminar el mensaje después de 6 segundos
    setTimeout(() => {
        notification.remove();
    }, 6000);

    // Si hay más de 3 mensajes, eliminar el más antiguo
    if (container.children.length > 3) {
        container.firstChild.remove();
    }
}

// Manejo del formulario con Ajax
document.getElementById('profileForm').addEventListener('submit', function(event) {
    event.preventDefault();

    const password = document.getElementById('password').value;
    const password2 = document.getElementById('password2').value;

    if (password && password !== password2) {
        showMessage('error', 'Las contraseñas no coinciden');
        return;
    }

    const formData = new FormData(this);

    fetch('profile.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.status === 'success') {
            showMessage('success', data.message);
            document.querySelector('.container-cabecera h1').innerText = data.name;
        } else {
            showMessage('error', data.message);
        }
    })
    .catch(error => {
        showMessage('error', '¡Error! Algo salió mal');
    });
});

// ==================================================
// MAP API
// ==================================================

// Mostrar/ocultar el mapa al hacer clic en el icono
document.getElementById("location-icon").addEventListener("click", function () {
  var mapContainer = document.getElementById("map-container");
  if (
    mapContainer.style.display === "none" ||
    mapContainer.style.display === ""
  ) {
    mapContainer.style.display = "block";
    map.invalidateSize(); // Asegurarse de que el mapa se renderiza correctamente
  } else {
    mapContainer.style.display = "none";
  }
});

// Cerrar el mapa si se hace clic fuera de él
document.addEventListener("click", function (event) {
  var mapContainer = document.getElementById("map-container");
  var locationIcon = document.getElementById("location-icon");
  if (
    mapContainer.style.display === "block" &&
    !mapContainer.contains(event.target) &&
    event.target !== locationIcon
  ) {
    mapContainer.style.display = "none";
  }
});

// Coordenadas API
// Inicializa el mapa en una ubicación predeterminada (España)
var map = L.map("map").setView([40.416775, -3.70379], 5); // Coordenadas de Madrid, España

// Añadir capa de mapa (OpenStreetMap)
L.tileLayer("https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png", {
  attribution:
    '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors',
}).addTo(map);

// Variable para almacenar el marcador
var marker = null;

// Añadir marcador (chincheta) solo una vez
map.on("click", function (e) {
  var lat = e.latlng.lat.toFixed(6);
  var lon = e.latlng.lng.toFixed(6);

  // Si ya existe un marcador, lo eliminamos
  if (marker) {
    map.removeLayer(marker);
  }

  // Crear y añadir el nuevo marcador
  marker = L.marker([lat, lon])
    .addTo(map)
    .bindPopup("Coordenadas: " + lat + ", " + lon)
    .openPopup();

  // Guardar las coordenadas en el input de ubicación
  document.getElementById("location").value = lat + ", " + lon;

  // Ocultar el mapa después de seleccionar la coordenada
  document.getElementById("map-container").style.display = "none";
});
