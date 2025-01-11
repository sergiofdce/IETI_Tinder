$(() => { //jquery equivalente a DOMContentLoaded
$('#submit').on('click', function(e) {
    e.preventDefault();
    sendForm();
});
    function sendForm() {
        $.ajax({
            url: 'login.php',
            type: 'POST',
            data: {
                email: $('#email').val(),
                password: $('#password').val()
            },
            success: function (response) {
                switch (response) {
                    case 'success':
                        window.location.href = 'discover.php';
                        break;
                    case 'incorrect password':
                        $('#message').html('Contraseña incorrecta');
                        $('#message').addClass('alert alert-danger');
                        break;
                    case 'incorrect user':
                        $('#message').html('Usuario incorrecto');
                        $('#message').addClass('alert alert-danger');
                        break;
                    case 'empty post':
                        $('#message').html('Rellene todos los campos');
                        $('#message').addClass('alert alert-danger');
                        break;
                }
            }

        })
    }

});
