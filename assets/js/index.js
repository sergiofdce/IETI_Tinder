$(() => { //jquery equivalente a DOMContentLoaded
    $('#submit').on('click', function (e) { // evento click para el formulario
        e.preventDefault();
        sendForm();
    });
    
    // función para enviar el formulario
    function sendForm() {
        $.ajax({
            url: 'login.php', // url del archivo php que va a recibir el formulario
            type: 'POST', // tipo de petición
            data: {
                email: $('#email').val(), // valor del input email
                password: $('#password').val() // valor del input password
            },
            success: function (response) { // función que se va a ejecutar si la petición es exitosa
                // switch para evaluar el resultado de la petición
                switch (response) {
                    case 'success':
                        window.location.href = 'discober.php'; // redirige a discober.php si el usuario se loguea correctamente
                        break;
                    case 'incorrect password':
                        $('#message').html('Contraseña incorrecta'); // mensaje de error si la contraseña es incorrecta
                        $('#message').addClass('alert alert-danger'); // agrega clase de alerta roja
                        break;
                    case 'incorrect user':
                        $('#message').html('Usuario incorrecto'); // mensaje de error si el usuario es incorrecto
                        $('#message').addClass('alert alert-danger'); // agrega clase de alerta roja
                        break;
                    case 'empty post':
                        $('#message').html('Rellene todos los campos'); // mensaje de error si los campos estan vacíos
                        $('#message').addClass('alert alert-danger'); // agrega clase de alerta roja
                        break;
                }
            }
        });
    }

});
