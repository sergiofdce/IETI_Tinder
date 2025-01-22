function renderPhotos(photoArray) {
    const ulElement = document.getElementById('photoList');
    ulElement.innerHTML = ''; // Limpia cualquier contenido existente

    const defaultPhoto = 'assets/img/web/pictureDefault.webp'; // Ruta de la foto por defecto

    // Combinar las fotos del array con fotos por defecto si hay menos que el máximo visible
    const photosToRender = [...photoArray, ...Array(Math.max(0, maxVisiblePhotos - photoArray.length)).fill(defaultPhoto)];

    // Generar y agregar las fotos dentro de <li>
    photosToRender.slice(0, maxVisiblePhotos).forEach((photo, index) => {
        const li = document.createElement('li');

        // Si es el primer <li>, asignar un id y añadir un div con el texto "Foto principal"
        if (index === 0) {
            li.id = 'firstPhoto';
            const mainPhotoDiv = document.createElement('div');
            mainPhotoDiv.id = 'mainPhotoLabel';
            mainPhotoDiv.innerHTML = `<p>Foto principal</p>`;
            li.appendChild(mainPhotoDiv);
        }

        // Verificar si la foto es la foto por defecto
        if (photo === defaultPhoto) {
            // Foto por defecto: se puede subir una nueva
            li.innerHTML += `
                <label>
                    <img src="${photo}" alt="Foto por defecto" class="default-photo">
                    <input type="file" class="file-input" data-index="${index}" style="display: none;">
                </label>
            `;
        } else {
            li.innerHTML += `
                <label>
                    <img src="assets/img/web/eliminar.png" alt="cruz" class="deletePhotos" data-path="${photo}">
                    <img src="${photo}" alt="Foto existente" class="normal-photo">
                </label>
            `;
        }

        ulElement.appendChild(li);
    });

    // Agregar eventos solo a los inputs de archivo de las fotos por defecto
    document.querySelectorAll('.file-input').forEach(input => {
        input.addEventListener('change', handleFileUpload);
    });

    // Agregar eventos para eliminar fotos
    document.querySelectorAll('.deletePhotos').forEach(deleteIcon => {
        deleteIcon.addEventListener('click', handleDeletePhoto);
    });
}

// Manejo de subida de archivos
async function handleFileUpload(event) {
    const file = event.target.files[0];
    if (!file) return;

    const formData = new FormData();
    formData.append('photo', file);

    try {
        const response = await fetch('uploadPhoto.php', {
            method: 'POST',
            body: formData,
        });

        const result = await response.json();

        if (result.success) {
            typeMessenger('green','Imagen subida');

            // Obtener la lista actualizada de fotos usando AJAX
            const photosResponse = await fetch('getUserPhotos.php'); // Endpoint para obtener las fotos del usuario
            const photosResult = await photosResponse.json();

            if (photosResult.success) {
                renderPhotos(photosResult.photos); // Renderizar las nuevas fotos
            } else {
                typeMessenger('red','Error al obtener las fotos actualizadas.');
            }
        } else {
            typeMessenger('red','Error al subir la foto: ' + result.message);
        }
    } catch (error) {
        console.error('Error al subir la foto:', error);
        typeMessenger('red','Hubo un problema al intentar subir la foto.');
    }
}

async function handleDeletePhoto(event) {
    const photoPath = event.target.dataset.path; // Obtener la ruta de la foto desde el atributo `data-path`

    if (!photoPath) return;

    // Verificar la cantidad de fotos restantes
    const remainingPhotos = document.querySelectorAll('.normal-photo').length;
    if (remainingPhotos <= 1) {
        typeMessenger('info','No puedes eliminar tu única foto. Debes tener al menos una foto en tu perfil.');
        return;
    }

    try {
        const response = await fetch('deletePhoto.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ path: photoPath }),
        });

        const result = await response.json();

        if (result.success) {
            typeMessenger('green','Imagen eliminada');

            // Actualizar la lista de fotos
            const photosResponse = await fetch('getUserPhotos.php');
            const photosResult = await photosResponse.json();

            if (photosResult.success) {
                renderPhotos(photosResult.photos); // Renderizar las fotos actualizadas
            } else {
                typeMessenger('red','Error al obtener las fotos actualizadas.');
            }
        } else {
            typeMessenger('red','Error al eliminar la foto: ' + result.message);
        }
    } catch (error) {
        console.error('Error al eliminar la foto:', error);
        typeMessenger('red','Hubo un problema al intentar eliminar la foto.');
    }
}

// Función para mostrar mensajes
function typeMessenger(type,message) {
    const container = document.getElementById("notificationContainer");

    // Crear un nuevo elemento de mensaje
    const notification = document.createElement("div");
    notification.classList.add("messenger");

    // Determinar el estilo y texto del mensaje(añadir los necesarios y poner stilos en el css)
    switch (type) {
        case 'red':
            notification.classList.add("divNotiError");
            notification.innerText = message ;
            break;
        case 'green':
            notification.classList.add("divNotiLike");
            notification.innerText = message;
            break;
        case 'info':
            notification.classList.add("divNotiOther");
            notification.innerText = message;
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