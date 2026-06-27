document.getElementById('form-sensor').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);

    fetch('procesar.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        const modalBox = document.getElementById('modal-content-box');
        
        // Limpiamos estilos de estado previos
        modalBox.classList.remove('status-normal', 'status-alerta');

        // Aplicamos el color del modal según la respuesta del servidor
        if (data.status === 'critico') {
            modalBox.classList.add('status-alerta');
        } else {
            modalBox.classList.add('status-normal');
        }

        mostrarModal(data.mensaje);
        
        // Reseteamos el campo numérico
        document.getElementById('temperatura').value = '';
    })
    .catch(error => {
        console.error('Error:', error);
        const modalBox = document.getElementById('modal-content-box');
        modalBox.classList.add('status-alerta');
        mostrarModal('<strong>Error:</strong> Fallo crítico al intentar comunicar con el servidor.');
    });
});

const modalContainer = document.getElementById('modal-container');
const modalMensaje = document.getElementById('modal-mensaje');

function mostrarModal(mensaje) {
    modalMensaje.innerHTML = mensaje;
    modalContainer.classList.remove('hidden');
}

// Cerrar clickeando fuera de la alerta
modalContainer.addEventListener('click', function(e) {
    if (e.target === modalContainer) {
        modalContainer.classList.add('hidden');
    }
});