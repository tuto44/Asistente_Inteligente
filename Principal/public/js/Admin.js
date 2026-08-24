document.addEventListener('DOMContentLoaded', () => {
    const uploadForm = document.getElementById('upload-form');
    const fileInput = document.getElementById('file-input');
    const fileNameDisplay = document.getElementById('file-selected-name');
    const statusDiv = document.getElementById('status-message');
    const documentList = document.getElementById('document-list');

    // Mostrar el nombre del archivo al seleccionarlo
    if (fileInput) {
        fileInput.addEventListener('change', () => {
            if (fileInput.files.length > 0) {
                fileNameDisplay.textContent = fileInput.files[0].name;
                fileNameDisplay.style.color = "#0f172a";
            } else {
                fileNameDisplay.textContent = "Ningún archivo seleccionado";
                fileNameDisplay.style.color = "#64748b";
            }
        });
    }

    // Cargar documentos al iniciar
    cargarDocumentos();

    // Enviar Formulario
    if (uploadForm) {
        uploadForm.addEventListener('submit', async (e) => {
            e.preventDefault();
            if (!fileInput.files.length) return;

            const formData = new FormData();
            formData.append('documento', fileInput.files[0]);

            statusDiv.innerHTML = "Subiendo e indexando archivo en segundo plano...";
            statusDiv.style.color = "#d97706";

            try {
                const response = await fetch('/admin/upload', {
                    method: 'POST',
                    body: formData
                });
                const data = await response.json();

                if (response.ok && data.status === 'success') {
                    statusDiv.innerHTML = data.message;
                    statusDiv.style.color = "#16a34a";
                    fileInput.value = '';
                    fileNameDisplay.textContent = "Ningún archivo seleccionado";
                    cargarDocumentos();
                } else {
                    statusDiv.innerHTML = (data.message || "Error al subir.");
                    statusDiv.style.color = "#dc2626";
                }
            } catch (error) {
                statusDiv.innerHTML = "Error de conexión con el servidor.";
                statusDiv.style.color = "#dc2626";
            }
        });
    }

    // Cargar Lista de Documentos
    async function cargarDocumentos() {
        documentList.innerHTML = '<li style="font-size: 0.85rem; color: #64748b;">Cargando lista...</li>';
        try {
            const res = await fetch('/admin/documents');
            const data = await res.json();
            documentList.innerHTML = '';

            if (data.status === 'success' && data.documents.length > 0) {
                data.documents.forEach(doc => {
                    const li = document.createElement('li');
                    li.className = 'doc-item';
                    
                    const spanName = document.createElement('span');
                    spanName.className = 'doc-name';
                    spanName.textContent = doc;

                    const btnDelete = document.createElement('button');
                    btnDelete.className = 'btn-delete';
                    btnDelete.textContent = "Eliminar";
                    btnDelete.onclick = () => eliminarDocumento(doc);

                    li.appendChild(spanName);
                    li.appendChild(btnDelete);
                    documentList.appendChild(li);
                });
            } else {
                documentList.innerHTML = '<li style="font-size: 0.85rem; color: #64748b;">No hay documentos indexados.</li>';
            }
        } catch (error) {
            documentList.innerHTML = '<li style="font-size: 0.85rem; color: #dc2626;">Error al obtener documentos.</li>';
        }
    }

    // Eliminar Documento
    async function eliminarDocumento(filename) {
        if (!confirm(`¿Eliminar "${filename}" de la base de datos y servidor?`)) return;

        try {
            const res = await fetch('/admin/delete', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ filename: filename })
            });
            const data = await res.json();

            if (data.status === 'success') {
                cargarDocumentos();
            } else {
                alert("Error: " + data.message);
            }
        } catch (error) {
            alert("Error de red.");
        }
    }
});