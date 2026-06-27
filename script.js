// Manejar Registro de Ingreso
document.getElementById('form-ingreso').addEventListener('submit', function(e) {
    e.preventDefault();
    enviarDatos(new FormData(this), 'ingreso');
});

// Manejar Registro de Egreso
document.getElementById('form-egreso').addEventListener('submit', function(e) {
    e.preventDefault();
    enviarDatos(new FormData(this), 'egreso');
});

function enviarDatos(formData, accion) {
    formData.append('accion', accion); // Añade al envío qué acción estamos ejecutando

    fetch('procesar.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        mostrarModal(data.mensaje);
        if (data.status === 'success') {
            document.getElementById('form-' + accion).reset();
        }
    })
    .catch(error => {
        console.error('Error:', error);
        mostrarModal('<strong>Error:</strong> No se pudo procesar la solicitud en el servidor.');
    });
}

const modalContainer = document.getElementById('modal-container');
const modalMensaje = document.getElementById('modal-mensaje');

function mostrarModal(mensaje) {
    modalMensaje.innerHTML = mensaje;
    modalContainer.classList.remove('hidden');
}

// Cerrar el aviso flotante clickeando afuera
modalContainer.addEventListener('click', function(e) {
    if (e.target === modalContainer) {
        modalContainer.classList.add('hidden');
    }
});