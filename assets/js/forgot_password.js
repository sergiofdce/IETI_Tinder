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
if (document.getElementById('forgotEmailForm')) {


    document.getElementById('forgotEmailForm').addEventListener('submit', function (event) {
        event.preventDefault();
        const email = document.getElementById('email').value;

        // Comprobar que el formulario no este vacio
        const fields = [
            { id: 'email', label: 'email-label', value: email }
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

        if (!email) {
            showMessage('error', 'Todos los campos son obligatorios');
            return;
        }

        const formData = new FormData(this);

        fetch('includes/passwordSendEmail.php', {
            method: 'POST',
            body: formData
        })
            .then(response => response.json())
            .then(data => {
                console.log(data);
                if (data.status === 'success') {
                    showMessage('success', data.message);
                    document.querySelector('.container-cabecera h1').innerText = "Correo enviado: ";
                    document.getElementById('forgotEmailForm').remove();
                    //añadir mensaje de registro correcto
                    document.querySelector('.container-cabecera').insertAdjacentHTML('afterend', '<p class="confirmation-message">Por favor, comprueba tu correo: ' + email + ' para recuperar tu contraseña.</p>');
                } else {
                    showMessage('error', data.message);
                }
            })
            .catch(error => {
                showMessage('error', '¡Error! Algo salió mal ' + error.message);

            });
    });
}

// Manejo del segundo formulario con Ajax
if (document.getElementById('forgotPasswordForm')) {
    document.getElementById('forgotPasswordForm').addEventListener('submit', function (event) {
        event.preventDefault();
        const password = document.getElementById('password').value;
        const password2 = document.getElementById('password2').value;

        if (password && password !== password2) {
            showMessage('error', 'Las contraseñas no coinciden');
            document.getElementById('password').classList.add('form__field--error');
            document.getElementById('password2').classList.add('form__field--error');
            document.getElementById('password-label').classList.add('form__label--error');
            document.getElementById('password2-label').classList.add('form__label--error');
            return;
        }

        // Comprobar que el formulario no este vacio
        const fields = [
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

        if (!password || !password2) {
            showMessage('error', 'Todos los campos son obligatorios');
            return;
        }
        // Comprobar que la contrasena tenga al menos 8 caracteres, una mayuscula, una minuscula y un numero
        if (password.length < 8 || !/[A-Z]/.test(password) || !/[a-z]/.test(password) || !/[0-9]/.test(password)) {
            showMessage('error', 'La contraseña debe tener al menos 8 caracteres, una mayúscula, una minúscula y un número');
            return;
        }

        const formData = new FormData(this);

        fetch('includes/passwordUpdateUser.php', {
            method: 'POST',
            body: formData
        })
            .then(response => response.json())
            .then(data => {
                console.log(data);
                if (data.status === 'success') {
                    showMessage('success', data.message);
                    document.querySelector('.container-cabecera h1').innerText = "Contraseña actualizada";
                    document.getElementById('forgotPasswordForm').remove();
                    //añadir mensaje de registro correcto
                    document.querySelector('.container-cabecera').insertAdjacentHTML('afterend', '<p class="confirmation-message">Contraseña cambiada con éxito. Vuelva a <a href="login.php">iniciar sesión</a>.</p>');
                } else {
                    showMessage('error', data.message);
                }
            })
            .catch(error => {
                showMessage('error', '¡Error! Algo salió mal ' + error.message);

            });
    });
}