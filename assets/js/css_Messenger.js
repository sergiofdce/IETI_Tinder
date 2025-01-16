
//funcion para mensajes

function typeMessenger(type) {
    const container = document.getElementById("notificationContainer");

    // Crear un nuevo elemento de mensaje
    const notification = document.createElement("div");
    notification.classList.add("messenger");

    // Determinar el estilo y texto del mensaje(añadir los necesarios y poner stilos en el css)
    switch (type) {
        case 'error':
            notification.classList.add("divNotiError");
            notification.innerText = "¡Error! Algo salió mal";
            break;
        case 'like':
            notification.classList.add("divNotiLike");
            notification.innerText = "¡Te han dado un like!";
            break;
        case 'nope':
            notification.classList.add("divNotiNope");
            notification.innerText = "Lo siento, no es una coincidencia.";
            break;
        default:
            notification.classList.add("divNotiOther");
            notification.innerText = "Notificación sin tipo específico.";
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







