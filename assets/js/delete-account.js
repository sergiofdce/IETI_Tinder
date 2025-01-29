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
//si existe el formulario
if (document.getElementById('deleteAccountForm')) {
    document.getElementById('deleteAccountForm').addEventListener('submit', function (event) {
        event.preventDefault();
        const keyword = document.getElementById('delete').value;

        //Comprobar que el formulario no este vacio
        if (!keyword) {
            document.getElementById('delete').classList.add('form__field--error');
            showMessage('error', 'Todos los campos son obligatorios');
            return;
        }
        //Comprobar que la palabra clave sea 'Eliminar'
        if (keyword !== 'Eliminar') {
            document.getElementById('delete').classList.add('form__field--error');
            showMessage('error', 'La palabra clave es incorrecta');
            return;
        }

        const formData = new FormData(this);

        fetch('includes/deleteUser.php', {
            method: 'POST',
            body: formData
        })
            .then(response => response.json())
            .then(data => {
                console.log(data);
                if (data.status === 'success') {
                    showMessage('success', data.message);
                    document.querySelector('.container-cabecera h1').innerText = "Cuenta eliminada";
                    document.getElementById('deleteAccountForm').remove();
                    //añadir mensaje de registro correcto
                    document.querySelector('.container-cabecera').insertAdjacentHTML('afterend', '<p class="confirmation-message">Sentimos que te vayas.</p>');
                } else {
                    showMessage('error', data.message);
                }
            })
            .catch(error => {
                showMessage('error', '¡Error! Algo salió mal ' + error.message);

            });
    });
}
