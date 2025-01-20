function renderPhotos(photoArray) {
    const ulElement = document.getElementById('photoList');
    ulElement.innerHTML = ''; // Limpia cualquier contenido existente

    const defaultPhoto = 'assets/img/web/pictureDefault.webp'; // Ruta de la foto por defecto
    const maxPhotos = 6;

    const photosToRender = [...photoArray, ...Array(Math.max(0, maxPhotos - photoArray.length)).fill(defaultPhoto)];

    photosToRender.forEach((photo, index) => {
        const li = document.createElement('li');

        if (photo === defaultPhoto) {
            li.innerHTML = `
                <label>
                    <img src="${photo}" alt="Foto por defecto">
                    <input type="file" class="file-input" data-index="${index}" style="display: none;">
                </label>
            `;
        } else {
            li.innerHTML = `
                <label>
                    <img src="assets/img/web/eliminar.png" alt="Eliminar" class="deletePhotos" data-path="${photo}">
                    <img src="${photo}" alt="Foto existente" class="normal-photo">
                </label>
            `;
        }

        ulElement.appendChild(li);
    });

    // Agregar eventos a los íconos de eliminación
    document.querySelectorAll('.deletePhotos').forEach((deleteBtn) => {
        deleteBtn.addEventListener('click', handleDeletePhoto);
    });

    // Agregar eventos a los inputs de archivo
    document.querySelectorAll('.file-input').forEach((input) => {
        input.addEventListener('change', handleFileUpload);
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
            alert('¡Foto subida con éxito!');

            // Obtener la lista actualizada de fotos usando AJAX
            const photosResponse = await fetch('getUserPhotos.php'); // Endpoint para obtener las fotos del usuario
            const photosResult = await photosResponse.json();

            if (photosResult.success) {
                renderPhotos(photosResult.photos); // Renderizar las nuevas fotos
            } else {
                alert('Error al obtener las fotos actualizadas.');
            }
        } else {
            alert('Error al subir la foto: ' + result.message);
        }
    } catch (error) {
        console.error('Error al subir la foto:', error);
        alert('Hubo un problema al intentar subir la foto.');
    }
}

async function handleDeletePhoto(event) {
    const photoPath = event.target.dataset.path; // Obtener la ruta de la foto desde el atributo `data-path`

    if (!photoPath) return;

    // Verificar la cantidad de fotos restantes
    const remainingPhotos = document.querySelectorAll('.normal-photo').length;
    if (remainingPhotos <= 1) {
        alert('No puedes eliminar tu única foto. Debes tener al menos una foto en tu perfil.');
        return;
    }

    const confirmDelete = confirm('¿Estás seguro de que deseas eliminar esta foto?');
    if (!confirmDelete) return;

    try {
        const response = await fetch('deletePhoto.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ path: photoPath }),
        });

        const result = await response.json();

        if (result.success) {
            alert('Foto eliminada con éxito.');

            // Actualizar la lista de fotos
            const photosResponse = await fetch('getUserPhotos.php');
            const photosResult = await photosResponse.json();

            if (photosResult.success) {
                renderPhotos(photosResult.photos); // Renderizar las fotos actualizadas
            } else {
                alert('Error al obtener las fotos actualizadas.');
            }
        } else {
            alert('Error al eliminar la foto: ' + result.message);
        }
    } catch (error) {
        console.error('Error al eliminar la foto:', error);
        alert('Hubo un problema al intentar eliminar la foto.');
    }
}
