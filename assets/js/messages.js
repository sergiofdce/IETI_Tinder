function loadUserMessages(senderId, receiverId) {
  fetch("messages.php", {
    method: "POST",
    headers: {
      "Content-Type": "application/x-www-form-urlencoded",
    },
    body: `action=getUserInfo&userId=${receiverId}`,
  })
    .then((response) => response.json())
    .then((user) => {
      // Calculate age from birth date
      const birthDate = new Date(user.fecha_nacimiento);
      const today = new Date();
      const age = today.getFullYear() - birthDate.getFullYear();
      
      document.getElementById("main-content").innerHTML = `
        <div id="usermessages-header">
          <button id='usermessages-header-goback' onclick="loadDefaultMain()">
            <img src='assets/img/web/left-arrow.png' alt='Go back'>
          </button>
          <img src="${user.foto}" alt="Foto del usuario">
          <span>${user.nombre}</span>
        </div>
        <div id="usermessages-container">
          <div class="tabs">
            <button onclick="showTab('chat')">Chat</button>
            <button onclick="showTab('perfil')">Perfil</button>
          </div>
          <div id="chat" class="tab-content">
            <!-- Aquí se cargarán los mensajes -->
            <div id="chat-container"></div>
            <input type="text" id="message-input" placeholder="Escribe un mensaje...">
            <button id="message-button" onclick="sendMessage(${senderId}, ${receiverId})">Enviar</button>
          </div>
          <div id="perfil" class="tab-content" style="display:none;">
            <div class="profile-container">
              <div class="slider">
                <img class="message-profileImage" src="${user.foto}" alt="Profile Image">
              </div>
              <div id="message-profile-info">   
                <p id="user-name">${user.nombre} <span id="user-age">${age}</span></p>
              </div>
            </div>
          </div>
        </div>
      `;
      loadMessages(senderId, receiverId);

      // Actualizar los mensajes cada 5 segundos
      setInterval(() => loadMessages(senderId, receiverId), 5000);
    })
    .catch((error) => console.error("Error:", error));
}

function loadDefaultMain() {
  // Recargar la página para volver al estado inicial
  location.reload();
}

function showTab(tabName) {
  document.querySelectorAll(".tab-content").forEach((tab) => {
    tab.style.display = "none";
  });
  document.getElementById(tabName).style.display = "block";
}

function loadMessages(senderId, receiverId) {
  fetch("messages.php", {
    method: "POST",
    headers: {
      "Content-Type": "application/x-www-form-urlencoded",
    },
    body: `action=load&senderId=${senderId}&receiverId=${receiverId}`,
  })
    .then((response) => response.json())
    .then((data) => {
      if (data.error) {
        console.error(data.message);
      } else {
        const chatContainer = document.getElementById("chat-container");
        chatContainer.innerHTML = "";
        let lastMessageTime = null;

        data.forEach((message, index) => {
          const messageTime = new Date(message.sent_at);
          if (
            lastMessageTime &&
            messageTime - lastMessageTime >= 5 * 60 * 1000
          ) {
            const divider = document.createElement("div");
            divider.classList.add("message-divider");
            divider.textContent = messageTime.toLocaleString("es-ES", {
              weekday: "long",
              day: "2-digit",
              month: "2-digit",
              year: "numeric",
              hour: "2-digit",
              minute: "2-digit",
            });
            chatContainer.appendChild(divider);
          } else if (index === 0) {
            const divider = document.createElement("div");
            divider.classList.add("message-divider");
            divider.textContent = messageTime.toLocaleString("es-ES", {
              weekday: "long",
              day: "2-digit",
              month: "2-digit",
              year: "numeric",
              hour: "2-digit",
              minute: "2-digit",
            });
            chatContainer.appendChild(divider);
          }
          lastMessageTime = messageTime;

          const messageElement = document.createElement("div");
          messageElement.classList.add("message-item");

          const imgElement = document.createElement("img");
          imgElement.src = `${message.foto}`;
          imgElement.alt = "Foto del usuario";
          imgElement.classList.add("message-photo");

          const textElement = document.createElement("div");
          textElement.textContent = message.message;
          textElement.classList.add("message-text");

          if (message.role === "sender") {
            messageElement.classList.add("message-sender");
            messageElement.appendChild(textElement); // Mensaje a la izquierda
          } else {
            messageElement.classList.add("message-receiver");
            messageElement.appendChild(imgElement); // Foto a la izquierda
            messageElement.appendChild(textElement); // Mensaje a la derecha
          }

          chatContainer.appendChild(messageElement);
        });
          chatContainer.scrollTop = chatContainer.scrollHeight;

      }
    })
    .catch((error) => console.error("Error:", error));
}

function sendMessage(senderId, receiverId) {
  const message = document.getElementById("message-input").value;
  if (message.trim() === "" || /^\s+$/.test(message)) {
    document.getElementById("message-input").value = "";
    return; // No enviar mensajes vacíos o solo espacios
  }

  fetch("messages.php", {
    method: "POST",
    headers: {
      "Content-Type": "application/x-www-form-urlencoded",
    },
    body: `action=send&senderId=${senderId}&receiverId=${receiverId}&message=${encodeURIComponent(
      message
    )}`,
  })
    .then((response) => response.json())
    .then((data) => {
      document.getElementById("message-input").value = ""; // Limpiar el campo de entrada
      if (data.error) {
        console.error(data.message);
      } else {
        loadMessages(senderId, receiverId); // Recargar los mensajes
      }
    })
    .catch((error) => {
      document.getElementById("message-input").value = ""; // Limpiar el campo de entrada en caso de error
      console.error("Error:", error);
    });
}
