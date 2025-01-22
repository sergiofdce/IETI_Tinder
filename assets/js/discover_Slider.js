// Cargar perfiles
let usedIndexes = new Set();
// Animación de arrastrar
let isDragging = false;
let startX = 0;
let startY = 0;
let currentX = 0;
let currentY = 0;
let profileContainer = null;
let swipeThreshold = 60; // Pixeles necesarios para un swipe
let isAnimating = false;

// Matches
let currentProfileEmail = "";

// ==================================================
// Carga de perfiles
// ==================================================

// Perfil inicial
document.addEventListener("DOMContentLoaded", function () {
  // BUGFIX DEL SERVIDOR
  hideMatchWindow();
  console.log(profiles);

  if (profiles.length === 0) {
    const container = document.querySelector(".container");
    container.innerHTML = "";
    const message = document.createElement("p");
    message.setAttribute("class", "no-profiles-message");
    message.textContent = "No hay perfiles disponibles";
    container.appendChild(message);

    // Crear el contenedor para el botón de filtro
    const filterButtonDiv = document.createElement("button");
    filterButtonDiv.className = "filtroButton";
    filterButtonDiv.id = "filtroButton";

    // Crear el SVG
    filterButtonDiv.innerHTML = `
      <svg xmlns="http://www.w3.org/2000/svg" width="20" viewBox="0 0 20 20" height="20" fill="none" class="svg-icon">
        <g stroke-width="1.5" stroke-linecap="round" stroke="#5d41de">
          <circle r="2.5" cy="10" cx="10"></circle>
          <path fill-rule="evenodd" d="m8.39079 2.80235c.53842-1.51424 2.67991-1.51424 3.21831-.00001.3392.95358 1.4284 1.40477 2.3425.97027 1.4514-.68995 2.9657.82427 2.2758 2.27575-.4345.91407.0166 2.00334.9702 2.34248 1.5143.53842 1.5143 2.67996 0 3.21836-.9536.3391-1.4047 1.4284-.9702 2.3425.6899 1.4514-.8244 2.9656-2.2758 2.2757-.9141-.4345-2.0033.0167-2.3425.9703-.5384 1.5142-2.67989 1.5142-3.21831 0-.33914-.9536-1.4284-1.4048-2.34247-.9703-1.45148.6899-2.96571-.8243-2.27575-2.2757.43449-.9141-.01669-2.0034-.97028-2.3425-1.51422-.5384-1.51422-2.67994.00001-3.21836.95358-.33914 1.40476-1.42841.97027-2.34248-.68996-1.45148.82427-2.9657 2.27575-2.27575.91407.4345 2.00333-.01669 2.34247-.97026z" clip-rule="evenodd"></path>
        </g>
      </svg>
      <span class="filtroButton-label">Filtros</span>
    `;

    container.appendChild(filterButtonDiv);

    // Configurar el evento del botón de filtrar
    filterButtonDiv.addEventListener("click", function () {
      showFilterPopup();
    });

    
    return;
  }

  //loadRandomProfile();
  loadNextProfile();
  setupEventListeners();
});

// Cargar un perfil aleatorio
async function loadRandomProfile() {
  if (usedIndexes.size >= profiles.length) {
    const container = document.querySelector(".container");
    container.innerHTML = "";
    const message = document.createElement("p");
    message.setAttribute("class", "no-profiles-message");
    message.textContent = "No hay perfiles disponibles";
    container.appendChild(message);

    
    return;
  }

  // Simular un retraso para cargar el perfil
  await new Promise((resolve) => setTimeout(resolve, 500));

  // Obtener un perfil aleatorio no usado
  let randomIndex;
  do {
    randomIndex = Math.floor(Math.random() * profiles.length);
  } while (usedIndexes.has(randomIndex));

  usedIndexes.add(randomIndex);
  const profile = profiles[randomIndex];

  // Guardar el email del perfil actual
  currentProfileEmail = profile.email;

  // Crear el nuevo contenedor de perfil
  const container = document.getElementById("discover-profiles");
  container.innerHTML = `
    <div class="profile-container">
      <div class="slider">
        <img class="discover-image" src="${
          profile.images.split(",")[0]
        }" alt="Profile Image">
        <img class="discover-image" src="${
          profile.images.split(",")[1]
        }" alt="Profile Image" style="display: none;">
      </div>
      <div id="profile-info">
        <div class="paginator">
          <span class="dot active"></span>
          <span class="dot"></span>
        </div>
        <p id="user-name">${profile.name} <span id="user-age">${calculateAge(
    profile.birth_date
  )}</span></p>
      </div>
    </div>
  `;

  // Evento animación de arrastrar
  // setupEventListeners();

  // Slider imagenes
  setupSlider();
}

// Cargar un perfil secuencial
// TODO: no parece funcionar bien.
let currentIndex = 0;
async function loadNextProfile() {
  if (usedIndexes.size >= profiles.length) {
    const container = document.querySelector(".container");
    container.innerHTML = "";
    const message = document.createElement("p");
    message.setAttribute("class", "no-profiles-message");
    message.textContent = "No hay perfiles disponibles";
    container.appendChild(message);

    // Crear el contenedor para el botón de filtro
    const filterButtonDiv = document.createElement('button');
    filterButtonDiv.className = 'filtroButton';
    filterButtonDiv.id = 'filtroButton';

    // Crear el SVG
    filterButtonDiv.innerHTML = `
      <svg xmlns="http://www.w3.org/2000/svg" width="20" viewBox="0 0 20 20" height="20" fill="none" class="svg-icon">
        <g stroke-width="1.5" stroke-linecap="round" stroke="#5d41de">
          <circle r="2.5" cy="10" cx="10"></circle>
          <path fill-rule="evenodd" d="m8.39079 2.80235c.53842-1.51424 2.67991-1.51424 3.21831-.00001.3392.95358 1.4284 1.40477 2.3425.97027 1.4514-.68995 2.9657.82427 2.2758 2.27575-.4345.91407.0166 2.00334.9702 2.34248 1.5143.53842 1.5143 2.67996 0 3.21836-.9536.3391-1.4047 1.4284-.9702 2.3425.6899 1.4514-.8244 2.9656-2.2758 2.2757-.9141-.4345-2.0033.0167-2.3425.9703-.5384 1.5142-2.67989 1.5142-3.21831 0-.33914-.9536-1.4284-1.4048-2.34247-.9703-1.45148.6899-2.96571-.8243-2.27575-2.2757.43449-.9141-.01669-2.0034-.97028-2.3425-1.51422-.5384-1.51422-2.67994.00001-3.21836.95358-.33914 1.40476-1.42841.97027-2.34248-.68996-1.45148.82427-2.9657 2.27575-2.27575.91407.4345 2.00333-.01669 2.34247-.97026z" clip-rule="evenodd"></path>
        </g>
      </svg>
      <span class="filtroButton-label">Filtros</span>
    `;

    container.appendChild(filterButtonDiv);

    // Configurar el evento del botón de filtrar
    filterButtonDiv.addEventListener("click", function () {
      showFilterPopup();
    });

    return;
  }

  // Simular un retraso para cargar el perfil
  await new Promise((resolve) => setTimeout(resolve, 500));

  // Obtener un perfil aleatorio no usado
  while (usedIndexes.has(currentIndex)) {
    currentIndex++;
  }

  usedIndexes.add(currentIndex);
  const profile = profiles[currentIndex];
  console.log("Llamando a loadNextProfile()");

  // Verifica que el índice esté siendo actualizado correctamente
  console.log("Índice actual:", currentIndex);

  // Verifica que no estés utilizando un bucle que esté causando que se muestre el mismo elemento dos veces
  console.log("Bucle actual:", profiles.length);

  // Verificar que el contenedor de perfiles exista
  let container = document.getElementById("discover-profiles");
  if (!container) {
    console.log("El contenedor de perfiles no existe. Creando uno nuevo.");
    const mainContainer = document.querySelector(".container");
    mainContainer.innerHTML = `
      <div id="discover-profiles">
        <div class="profile-container">
          <div id="profile-info">
            <p id="user-name">Username <span id="user-age">Age</span></p>
          </div>
        </div>
      </div>
      <div id="actions">
        <button id="nope" class="discover-actionButton nope-button">
          <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <path d="M18 6 6 18"></path>
            <path d="m6 6 12 12"></path>
          </svg>
        </button>
        <button id="like" class="discover-actionButton like-button">
          <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <path d="M19 14c1.49-1.46 3-3.21 3-5.5A5.5 5.5 0 0 0 16.5 3c-1.76 0-3 .5-4.5 2-1.5-1.5-2.74-2-4.5-2A5.5 5.5 0 0 0 2 8.5c0 2.3 1.5 4.05 3 5.5l7 7Z"></path>
          </svg>
        </button>
      </div>
      <div id="filtro">
      <button class="filtroButton" id="filtroButton">
            <svg xmlns="http://www.w3.org/2000/svg" width="20" viewBox="0 0 20 20" height="20" fill="none" class="svg-icon">
                  <g stroke-width="1.5" stroke-linecap="round" stroke="#5d41de">
                        <circle r="2.5" cy="10" cx="10"></circle>
                        <path fill-rule="evenodd" d="m8.39079 2.80235c.53842-1.51424 2.67991-1.51424 3.21831-.00001.3392.95358 1.4284 1.40477 2.3425.97027 1.4514-.68995 2.9657.82427 2.2758 2.27575-.4345.91407.0166 2.00334.9702 2.34248 1.5143.53842 1.5143 2.67996 0 3.21836-.9536.3391-1.4047 1.4284-.9702 2.3425.6899 1.4514-.8244 2.9656-2.2758 2.2757-.9141-.4345-2.0033.0167-2.3425.9703-.5384 1.5142-2.67989 1.5142-3.21831 0-.33914-.9536-1.4284-1.4048-2.34247-.9703-1.45148.6899-2.96571-.8243-2.27575-2.2757.43449-.9141-.01669-2.0034-.97028-2.3425-1.51422-.5384-1.51422-2.67994.00001-3.21836.95358-.33914 1.40476-1.42841.97027-2.34248-.68996-1.45148.82427-2.9657 2.27575-2.27575.91407.4345 2.00333-.01669 2.34247-.97026z" clip-rule="evenodd"></path>
                  </g>
            </svg>
            <span class="filtroButton-label">Filtros</span>
      </button>
      </div>

    `;

    container = document.getElementById("discover-profiles");

    // Configurar el evento del botón de filtrar
    document.getElementById("filtroButton").addEventListener("click", function () {
      showFilterPopup();
    });

    // Configurar los eventos de los botones de acción
    document.getElementById("nope").addEventListener("click", function () {
      handleSwipe("left");
    });
    document.getElementById("like").addEventListener("click", function () {
      handleSwipe("right");
    });
  }

  // Guardar el email del perfil actual
  currentProfileEmail = profile.email;

  // Crear el nuevo contenedor de perfil
  container.innerHTML = `
    <div class="profile-container">
      <div class="slider">
        <img class="discover-image" src="${
          profile.images.split(",")[0]
        }" alt="Profile Image">
        <img class="discover-image" src="${
          profile.images.split(",")[1]
        }" alt="Profile Image" style="display: none;">
      </div>
      <div id="profile-info">
        <div class="paginator">
          <span class="dot active"></span>
          <span class="dot"></span>
        </div>
        <p id="user-name">${profile.name} <span id="user-age">${calculateAge(
    profile.birth_date
  )}</span></p>
      </div>
    </div>
  `;

  // Evento animación de arrastrar
  // setupEventListeners();

  // Slider imagenes
  setupSlider();
}

// Configurar el slider
function setupSlider() {
  const images = document.querySelectorAll(".discover-image");
  const dots = document.querySelectorAll(".dot");
  let currentIndex = 0;

  images.forEach((image, index) => {
    image.addEventListener("click", () => {
      images[currentIndex].style.display = "none";
      dots[currentIndex].classList.remove("active");
      currentIndex = (currentIndex + 1) % images.length;
      images[currentIndex].style.display = "block";
      dots[currentIndex].classList.add("active");
    });
  });
}

// Calcular edad
function calculateAge(birthdate) {
  const today = new Date();
  const birthDate = new Date(birthdate); // Fecha en formato "YYYY-MM-DD"
  let age = today.getFullYear() - birthDate.getFullYear();
  const monthDiff = today.getMonth() - birthDate.getMonth();

  if (
    monthDiff < 0 ||
    (monthDiff === 0 && today.getDate() < birthDate.getDate())
  ) {
    age--;
  }

  return age;
}

// ==================================================
// NOPE o LIKE
// ==================================================

// Nope
document.getElementById("nope").addEventListener("click", function () {
  handleSwipe("left"); // Llamamos a handleSwipe, que ya se encarga de enviar la interacción
});

// Like
document.getElementById("like").addEventListener("click", function () {
  handleSwipe("right"); // Llamamos a handleSwipe, que ya se encarga de enviar la interacción
});

// Función para enviar la interacción al servidor
async function sendInteraction(action) {
  const data = {
    senderID: userId,
    receiverID: profiles.find(
      (profile) => profile.email === currentProfileEmail
    ).id,
    action: action,
  };

  try {
    const response = await fetch("includes/interactions.php", {
      method: "POST",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify(data),
    });

    const responseText = await response.text(); // Obtener la respuesta como texto
    const responseData = JSON.parse(responseText); // Convertir la respuesta a JSON

    console.log("Respuesta del servidor:", responseData);

    // Llamar a logEvent en el servidor
    await fetch("includes/log_event.php", {
      method: "POST",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify({
        eventType: action === "like" ? "like_sent" : "nope_sent",
        description: `Envió un ${action} al usuario ${currentProfileEmail}`,
        userEmail: userEmail,
      }),
    });

    return responseData; // Retornar la respuesta del servidor
  } catch (error) {
    console.error("Error al enviar la interacción:", error);
    return null; // Retornar null en caso de error
  }
}

// Match

// Botones
const showMatch = document.getElementById("showMatch");
const container = document.querySelector(".container");

function showMatchWindow() {
  return new Promise((resolve) => {
    // Mostrar la ventana de match
    showMatch.style.display = "block";

    // Desenfocar el fondo
    container.classList.add("blur");

    // Seleccionar los botones usando sus ID's
    const closeButton1 = document.getElementById("closeMatch1");
    const closeButton2 = document.getElementById("closeMatch2");

    // Agregar el event listener a ambos botones de cierre
    closeButton1.addEventListener("click", () => {
      showMatch.style.display = "none";
      resolve();
    });

    closeButton2.addEventListener("click", () => {
      showMatch.style.display = "none";
      resolve();
    });
  });
}

function hideMatchWindow() {
  showMatch.style.display = "none";
  container.classList.remove("blur");
}

// Match -> Message
document.getElementById("closeMatch1").addEventListener("click", function () {
  hideMatchWindow();
});
// Match -> Discover
document.getElementById("closeMatch2").addEventListener("click", function () {
  hideMatchWindow();
  //loadRandomProfile();
  loadNextProfile();
});

// ==================================================
// Animación Slide
// ==================================================

function setupEventListeners() {
  profileContainer = document.querySelector(".profile-container");

  // Arrastrar imagenes
  // Activar .profile-container{ cursor: grab; } en CSS

  // // Touch events
  // profileContainer.addEventListener("touchstart", handleDragStart);
  // profileContainer.addEventListener("touchmove", handleDragMove);
  // profileContainer.addEventListener("touchend", handleDragEnd);
  // // Mouse events
  // profileContainer.addEventListener("mousedown", handleDragStart);
  // document.addEventListener("mousemove", handleDragMove);
  // document.addEventListener("mouseup", handleDragEnd);

  // Button events
  document
    .getElementById("nope")
    .addEventListener("click", () => handleSwipe("left"));
  document
    .getElementById("like")
    .addEventListener("click", () => handleSwipe("right"));
}

function handleDragStart(e) {
  if (isAnimating) return;

  isDragging = true;
  profileContainer.classList.add("dragging");

  // Get starting position
  if (e.type === "mousedown") {
    startX = e.clientX;
    startY = e.clientY;
  } else {
    startX = e.touches[0].clientX;
    startY = e.touches[0].clientY;
  }

  currentX = startX;
  currentY = startY;
}

function handleDragMove(e) {
  if (!isDragging || isAnimating) return;
  e.preventDefault();

  // Get current position
  const clientX = e.type === "mousemove" ? e.clientX : e.touches[0].clientX;
  const clientY = e.type === "mousemove" ? e.clientY : e.touches[0].clientY;

  // Calculate distance moved
  const deltaX = clientX - startX;
  const deltaY = clientY - startY;

  currentX = clientX;
  currentY = clientY;

  // Update card position and rotation
  const rotation = deltaX * 0.1; // Adjust rotation based on drag distance
  profileContainer.style.transform = `translate(${deltaX}px, ${deltaY}px) rotate(${rotation}deg)`;

  // Show/hide stamps based on drag direction
  updateStamp(deltaX);
}

function handleDragEnd() {
  if (!isDragging || isAnimating) return;
  isDragging = false;
  profileContainer.classList.remove("dragging");

  const deltaX = currentX - startX;

  if (Math.abs(deltaX) >= swipeThreshold) {
    // Swipe was strong enough
    handleSwipe(deltaX > 0 ? "right" : "left");
  } else {
    // Reset card position
    profileContainer.style.transform = "";
    hideStamps();
  }
}

async function handleSwipe(direction) {
  if (isAnimating) return;
  isAnimating = true;

  const isRight = direction === "right";
  profileContainer.classList.add(isRight ? "swiped-right" : "swiped-left");
  // showStamp(isRight ? "LIKE" : "NOPE");

  try {
    // Enviar interacción y esperar la respuesta del servidor
    const matchResponse = await sendInteraction(
      direction === "right" ? "like" : "nope"
    );

    // Si hay un match
    if (matchResponse && matchResponse.message === "Match!") {
      await showMatchWindow(); // Espera a que se muestre el popup

      // Limpiamos la animación actual
      profileContainer.classList.remove("swiped-right", "swiped-left");
      isAnimating = false;

      // Cargamos el siguiente perfil solo después de que el usuario
      // cierre la ventana de match
      //await loadRandomProfile();
      await loadNextProfile();
    } else {
      // Si no hay match, esperamos que termine la animación
      // await new Promise((resolve) => setTimeout(resolve, 100));
      profileContainer.classList.remove("swiped-right", "swiped-left");
      isAnimating = false;
      //await loadRandomProfile();
      await loadNextProfile();
    }
  } catch (error) {
    console.error("Error durante el swipe:", error);
    isAnimating = false;
    profileContainer.classList.remove("swiped-right", "swiped-left");
  }
}

function updateStamp(deltaX) {
  if (Math.abs(deltaX) < 60) {
    hideStamps();
    return;
  }

  showStamp(deltaX > 0 ? "LIKE" : "NOPE");
}

function showStamp(type) {
  hideStamps();
  const stamp = document.createElement("div");
  stamp.className = `stamp ${type.toLowerCase()} visible`;
  stamp.textContent = type;
  profileContainer.appendChild(stamp);
}

function hideStamps() {
  const stamps = document.querySelectorAll(".stamp");
  stamps.forEach((stamp) => stamp.remove());
}

// Selecciona todas las imágenes de la página
document.addEventListener("dragstart", function (event) {
  if (event.target.tagName === "IMG") {
    event.preventDefault(); // Evita el comportamiento de arrastrar
  }
});

// ==================================================
// Filtro de perfiles
// ==================================================
// Configuración del filtro
// Define default values for age range and radius
let savedMinAge = 18;
let savedMaxAge = 100;
let savedRadius = 50;

document.getElementById("filtroButton").addEventListener("click", function () {
  showFilterPopup();
});

function showFilterPopup() {
  const filterDiv = document.createElement("div");
  filterDiv.className = "filter-popup";

  const overlay = document.createElement("div");
  overlay.className = "filter-overlay";

  filterDiv.innerHTML = `
        <div class="filter-content">
            <h3>Filtrar por edad y distancia</h3>
            <div class="age-range-container">
                <div class="range-slider">
                    <div class="slider-track"></div>
                    <input type="range" 
                           class="range-min" 
                           min="18" 
                           max="100" 
                           value="${savedMinAge}"
                           step="1">
                    <input type="range" 
                           class="range-max" 
                           min="18" 
                           max="100" 
                           value="${savedMaxAge}"
                           step="1">
                </div>
                <div class="range-values">
                    <span id="minValue">${savedMinAge}</span> - <span id="maxValue">${savedMaxAge}</span> años
                </div>
            </div>
            <div class="radius-container">
                <label for="radiusRange">Radio de búsqueda: <span id="radiusValue">${savedRadius}</span> km</label>
                <input type="range" 
                       id="radiusRange" 
                       min="2" 
                       max="160" 
                       value="${savedRadius}"
                       step="1">
            </div>
            <div class="filter-buttons">
                <button id="applyFilter">Aplicar filtro</button>
            </div>
        </div>
    `;

  const styles = document.createElement("style");

  document.head.appendChild(styles);
  document.body.appendChild(overlay);
  document.body.appendChild(filterDiv);

  // Get slider elements
  const minSlider = filterDiv.querySelector(".range-min");
  const maxSlider = filterDiv.querySelector(".range-max");
  const radiusSlider = filterDiv.querySelector("#radiusRange");
  const minValue = filterDiv.querySelector("#minValue");
  const maxValue = filterDiv.querySelector("#maxValue");
  const radiusValue = filterDiv.querySelector("#radiusValue");
  const track = filterDiv.querySelector(".slider-track");

  // Update track color
  function updateTrack() {
    const percent1 =
      ((minSlider.value - minSlider.min) / (minSlider.max - minSlider.min)) *
      100;
    const percent2 =
      ((maxSlider.value - minSlider.min) / (maxSlider.max - minSlider.min)) *
      100;
    track.style.background = `linear-gradient(to right, #ddd ${percent1}%, #ff4458 ${percent1}%, #ff4458 ${percent2}%, #ddd ${percent2}%)`;
  }

  // Update range values and enforce min difference
  minSlider.addEventListener("input", function () {
    const minVal = parseInt(minSlider.value);
    const maxVal = parseInt(maxSlider.value);

    if (maxVal - minVal < 4) {
      minSlider.value = maxVal - 4;
    }

    minValue.textContent = minSlider.value;
    updateTrack();
  });

  maxSlider.addEventListener("input", function () {
    const minVal = parseInt(minSlider.value);
    const maxVal = parseInt(maxSlider.value);

    if (maxVal - minVal < 4) {
      maxSlider.value = parseInt(minSlider.value) + 4;
    }

    maxValue.textContent = maxSlider.value;
    updateTrack();
  });

  radiusSlider.addEventListener("input", function () {
    radiusValue.textContent = radiusSlider.value;
  });

  // Initial track update
  updateTrack();

  // Apply filter button handler
  document.getElementById("applyFilter").addEventListener("click", () => {
    savedMinAge = parseInt(minSlider.value);
    savedMaxAge = parseInt(maxSlider.value);
    savedRadius = parseInt(radiusSlider.value);

    const [userLat, userLon] = userLocation
      .split(",")
      .map((coord) => parseFloat(coord.trim()));

    // Convert radius from km to degrees (approximate)
    const latRadius = savedRadius / 111.32; // 1 degree = 111.32 km
    const lonRadius =
      savedRadius / (111.32 * Math.cos(userLat * (Math.PI / 180)));

    // Variables SQL
    const filters = {
      minAge: savedMinAge,
      maxAge: savedMaxAge,
      minLat: userLat - latRadius,
      maxLat: userLat + latRadius,
      minLon: userLon - lonRadius,
      maxLon: userLon + lonRadius,
    };

    // Enviar datos
    fetch("includes/filtrer.php", {
      method: "POST",
      headers: {
        "Content-Type": "application/json",
      },
      body: JSON.stringify(filters),
    })
      .then((response) => {
        if (!response.ok) {
          throw new Error('Network response was not ok');
        }
        return response.json();
      })
      .then((data) => {
        if (data.success) {
          console.log("Usuarios filtrados:", data.users);
          profiles = data.users; // Actualizar el JSON de perfiles
          usedIndexes.clear(); // Reiniciar los índices usados
          currentIndex = 0; // Reiniciar el índice actual
          loadNextProfile(); // Cargar el siguiente perfil
          filterDiv.remove(); // Cerrar la ventana de filter-popup
          overlay.remove(); // Eliminar el overlay
        } else {
          console.error("Error:", data.message);
        }
      })
      .catch((error) => {
        console.error("Error al procesar la solicitud:", error);
      });
  });

  // Close on overlay click
  overlay.addEventListener("click", (e) => {
    if (e.target === overlay) {
      filterDiv.remove();
      overlay.remove();
    }
  });
}
