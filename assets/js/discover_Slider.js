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
    message.textContent = "No hi ha perfils disponibles";
    container.appendChild(message);
    return;
  }

  preloadImages(profiles);
  loadRandomProfile();
  setupEventListeners();
});


// Preload profile images
function preloadImages(images) {
  images.forEach(profile => {
    profile.images.split(',').forEach(imageUrl => {
      const img = new Image();
      img.src = imageUrl;
    });
  });
}

 
// Cargar un perfil aleatorio
function loadRandomProfile() {
  if (usedIndexes.size >= profiles.length) {
    const container = document.querySelector(".container");
    container.innerHTML = "";
    const message = document.createElement("p");
    message.setAttribute("class", "no-profiles-message");
    message.textContent = "No hi ha perfils disponibles";
    container.appendChild(message);
    return;
  }

  // Get random unused profile
  let randomIndex;
  do {
    randomIndex = Math.floor(Math.random() * profiles.length);
  } while (usedIndexes.has(randomIndex));

  usedIndexes.add(randomIndex);
  const profile = profiles[randomIndex];

  // Guardar el email del perfil actual
  currentProfileEmail = profile.email;

  // Create new profile container
  const container = document.getElementById("discover-profiles");
  container.innerHTML = `
    <div class="profile-container">
      <img id="profile-image" src="${profile.images.split(',')[0]}" alt="Profile Image">
      <div id="profile-info">
        <p id="user-name">${profile.name} <span id="user-age">${calculateAge(profile.birth_date)}</span></p>
      </div>
    </div>
  `;

  // Reset variables and setup new listeners
  setupEventListeners();
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
document.getElementById('nope').addEventListener('click', function () {
    sendInteraction('nope');
});

// Like
document.getElementById('like').addEventListener('click', function () {
    sendInteraction('like');
});

// Función para enviar la interacción al servidor
function sendInteraction(action) {

  const data = {
    senderID: userId,
    receiverID: profiles.find(profile => profile.email === currentProfileEmail).id,
    action: action,
  };

  // console.log("Datos enviados al servidor:", data);


  // Enviar la interacción al servidor con fetch
  fetch("includes/interactions.php", {
    method: "POST",
    headers: { "Content-Type": "application/json" },
    body: JSON.stringify(data),
  })
    .then((response) => response.text()) // Cambiar a .text() para inspeccionar la respuesta cruda
    .then((responseText) => {
      // console.log("Respuesta cruda del servidor:", responseText);
      return JSON.parse(responseText); // Intenta convertirlo a JSON
    })
    .then((responseData) => {
      console.log("Respuesta del servidor:", responseData);
      if (responseData.message === "Match!") {
        showMatchWindow();
      }
    })
    .catch((error) => {
      console.error("Error al enviar la interacción:", error);
    });

}

// Match

// Botones
const showMatch = document.getElementById("showMatch");
const container = document.querySelector(".container");

function showMatchWindow() {
  showMatch.style.display = "block";
  container.classList.add("blur");
}

function hideMatchWindow() {
  showMatch.style.display = "none";
  container.classList.remove("blur");
}

document
  .getElementById("closeMatch1")
  .addEventListener("click", hideMatchWindow);
document
  .getElementById("closeMatch2")
  .addEventListener("click", hideMatchWindow);


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

function handleSwipe(direction) {
  if (isAnimating) return;
  isAnimating = true;

  const isRight = direction === "right";
  profileContainer.classList.add(isRight ? "swiped-right" : "swiped-left");
  showStamp(isRight ? "LIKE" : "NOPE");

  // Wait for animation to complete before loading next profile
  setTimeout(() => {
    isAnimating = false;
    loadRandomProfile();
  }, 100);
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