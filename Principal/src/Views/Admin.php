<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Administración - SmartSupport</title>
    <link rel="stylesheet" href="/css/style.css?v=<?= time() ?>">
</head>
<body class="admin-page">
    <div class="admin-wrapper">
        <header class="admin-header">
            <h2>Panel de Gestión de Conocimiento</h2>
        </header>

        <div class="admin-grid">
            <!-- Columna Izquierda: Formulario de Subida -->
            <div class="admin-card">
                <h3>Subir Documento</h3>
                <form id="upload-form">
                    <div class="file-dropzone">
                        <label for="file-input" class="file-label">
                                <span>Seleccionar archivo (.md, .txt)</span>
                        </label>
                        <input type="file" id="file-input" accept=".md,.txt" required>
                        <small id="file-selected-name" class="file-name">Ningún archivo seleccionado</small>
                    </div>
                    
                    <button type="submit" class="btn-primary">
                        Subir e Indexar Archivo
                    </button>
                </form>

                <div id="status-message" class="status-box"></div>
            </div>

            <!-- Columna Derecha: Lista de Documentos -->
            <div class="admin-card">
                <h3>Documentos en Servidor</h3>
                <ul id="document-list" class="doc-list">
                    <!-- Se carga dinámicamente con JS -->
                </ul>
            </div>
        </div>

        <div class="admin-footer">
            <a href="/" class="btn-back">← Volver al Chat</a>
        </div>
    </div>

    <script src="/js/admin.js?v=<?= time() ?>"></script>
</body>
</html>