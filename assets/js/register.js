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
    case 'wrongEmail':
      notification.classList.add("divNotiWarning");
      notification.innerText = message || "El enlace de verificación no es válido.";
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
document.getElementById('registerForm').addEventListener('submit', function (event) {
  event.preventDefault();
  const name = document.getElementById('name').value;
  const surname = document.getElementById('surname').value;
  const username = document.getElementById('username').value;
  const email = document.getElementById('email').value;
  const genre = document.getElementById('genre').value;
  const sexual_preference = document.getElementById('sexual_preference').value;
  const birthdate = document.getElementById('birthdate').value;
  const location = document.getElementById('location').value;
  const media = document.getElementById('media').value;
  const media2 = document.getElementById('media2').value;

  const password = document.getElementById('password').value;
  const password2 = document.getElementById('password2').value;

  document.getElementById('location').disabled = false;

  if (password && password !== password2) {
    showMessage('error', 'Las contraseñas no coinciden');
    return;
  }

  // Comprobar que el formulario no este vacio
  const fields = [
    { id: 'name', label: 'name-label', value: name },
    { id: 'surname', label: 'surname-label', value: surname },
    { id: 'username', label: 'username-label', value: username },
    { id: 'email', label: 'email-label', value: email },
    { id: 'genre', label: 'genre-label', value: genre },
    { id: 'sexual_preference', label: 'sexual_preference-label', value: sexual_preference },
    { id: 'birthdate', label: 'birthdate-label', value: birthdate },
    { id: 'location', label: 'location-label', value: location },
    { id: 'media', label: 'media-label', value: media },
    { id: 'media2', label: 'media2-label', value: media2 },
    { id: 'password', label: 'password-label', value: password },
    { id: 'password2', label: 'password2-label', value: password2 }
  ];

  fields.forEach((field) => {
    if (!field.value) {
      document.getElementById(field.id).classList.add('form__field--error');
      document.getElementById(field.label).classList.add('form__label--error');
    }
    else {
      document.getElementById(field.id).classList.remove('form__field--error');
      document.getElementById(field.label).classList.remove('form__label--error');
    }
  });

  if (!name || !surname || !username || !email || !genre || !sexual_preference || !birthdate || !location || !media || !media2 || !password || !password2) {
    showMessage('error', 'Todos los campos son obligatorios');
    return;
  }

  const formData = new FormData(this);
 
  fetch('register.php', {
    method: 'POST',
    body: formData
  })
    .then(response => response.json())
    .then(data => {
      console.log(data);
      if (data.status === 'success') {
        showMessage('success', data.message);
        document.querySelector('.container-cabecera h1').innerText = data.name;
        document.getElementById('registerForm').remove();
        //añadir mensaje de registro correcto
        document.querySelector('.container-cabecera').insertAdjacentHTML('afterend', '<p class="confirmation-message">¡Registro realizado con éxito! Por favor, verifica tu correo para activar tu cuenta.</p>');
      } else {
        showMessage('error', data.message);
      }
    })
    .catch(error => {
      
      showMessage('error', '¡Error! Algo salió mal ' + error.message);

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

