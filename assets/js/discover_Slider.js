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
  console.log(profiles);

  if (profiles.length === 0) {
    const container = document.querySelector(".container");
    container.innerHTML = "";
    const message = document.createElement("p");
    message.setAttribute("class", "no-profiles-message");
    message.textContent = "No hay perfiles disponibles";
    container.appendChild(message);
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
  console.log('Llamando a loadNextProfile()');

  // Verifica que el índice esté siendo actualizado correctamente
  console.log('Índice actual:', currentIndex);

  // Verifica que no estés utilizando un bucle que esté causando que se muestre el mismo elemento dos veces
  console.log('Bucle actual:', profiles.length);
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