<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Administración - SmartSupport</title>
    <link rel="stylesheet" href="/css/style.css?v=<?= time() ?>">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
</head>
<body class="admin-page">
    <div class="admin-wrapper">
        <header class="admin-header">
            <h1>Panel de Gestión de Conocimiento</h1>
        </header>

        <main class="admin-grid">
            <!-- Columna Izquierda: Formulario de Subida -->
            <section class="admin-card">
                <h2>Subir Documento</h2>
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
            </section>

            <!-- Columna Derecha: Lista de Documentos -->
            <section class="admin-card">
                <h2>Documentos en Servidor</h2>
                <ul id="document-list" class="doc-list">
                    <!-- Se carga dinámicamente con JS -->
                </ul>
            </section>
        </main>

        <footer class="admin-footer">
            <a href="/" class="btn-back">← Volver al Chat</a>
        </footer>
    </div>

    <script src="/js/admin.js?v=<?= time() ?>"></script>
</body>
</html>