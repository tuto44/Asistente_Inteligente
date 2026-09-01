import os
import hashlib
from fastapi import APIRouter, Depends, BackgroundTasks, UploadFile, File, HTTPException
from app.models.schemas import ChatRequest, ChatResponse
from app.api.security import verificar_api_key
from app.services.rag_service import RagService
from app.services.indexer import Indexer
from app.database.qdrant_manager import QdrantManager

# 1. Definir primero las instancias antes de usar los decoradores
router = APIRouter()
rag_service = RagService()


@router.post("/chat", response_model=ChatResponse, dependencies=[Depends(verificar_api_key)])
def chat(request: ChatRequest):
    respuesta_texto = rag_service.responder(
        usuario=request.user_name,
        pregunta=request.question,
        historial=request.historial,
        image_base64=request.image_base64
    )
    
    return ChatResponse(
        status="success",
        answer=respuesta_texto
    )


@router.post("/upload-and-index", dependencies=[Depends(verificar_api_key)])
async def upload_and_index(
    background_tasks: BackgroundTasks,
    file: UploadFile = File(...)
):
    try:
        # 1. Guardar el archivo recibido
        docs_path = "./documentos"
        os.makedirs(docs_path, exist_ok=True)
        file_path = os.path.join(docs_path, file.filename)
        
        with open(file_path, "wb") as buffer:
            buffer.write(await file.read())
            
        # 2. Función envoltorio para ver los logs en vivo
        def tarea_indexacion(nombre_archivo):
            print(f"\n[Fondo] 🚀 Iniciando indexación por el archivo: {nombre_archivo}", flush=True)
            try:
                indexer = Indexer()
                indexer.indexar_documentos()
                print(f"[Fondo] ✅ Indexación completada con éxito.", flush=True)
            except Exception as e:
                print(f"[Fondo] ❌ ERROR EN LA INDEXACIÓN: {str(e)}", flush=True)

        # 3. Tarea en segundo plano
        background_tasks.add_task(tarea_indexacion, file.filename)
        
        return {
            "status": "success", 
            "message": f"Archivo '{file.filename}' guardado correctamente. Indexación iniciada en segundo plano."
        }
    except Exception as e:
        raise HTTPException(status_code=500, detail=str(e))
    

@router.get("/documents", dependencies=[Depends(verificar_api_key)])
def get_documents():
    docs_path = "./documentos"
    os.makedirs(docs_path, exist_ok=True)
    # Listar solo archivos
    archivos = [f for f in os.listdir(docs_path) if os.path.isfile(os.path.join(docs_path, f))]
    return {"status": "success", "documents": archivos}

@router.delete("/documents/{filename}", dependencies=[Depends(verificar_api_key)])
def delete_document(filename: str):
    docs_path = "./documentos"
    file_path = os.path.join(docs_path, filename)
    
    if os.path.exists(file_path):
        try:
            # 1. Generar el ID del documento (mismo método que usa tu Indexer)
            doc_id = hashlib.sha256(filename.encode('utf-8')).hexdigest()
            
            # 2. Conectar a Qdrant y eliminar los vectores
            qdrant = QdrantManager()
            
            # IMPORTANTE: Cambia "eliminar_documento" por el nombre exacto 
            # de la función que tienes en tu archivo qdrant_manager.py
            qdrant.eliminar_documento(doc_id) 
            
            # 3. Eliminar el archivo físico de la carpeta
            os.remove(file_path)
            
            print(f"[Fondo] 🗑️ Archivo '{filename}' (ID: {doc_id}) eliminado de Qdrant y del disco.", flush=True)
            return {"status": "success", "message": f"Archivo '{filename}' eliminado del servidor y base vectorial."}
            
        except Exception as e:
            print(f"[Fondo] ❌ Error eliminando '{filename}': {str(e)}", flush=True)
            raise HTTPException(status_code=500, detail=f"Error interno al eliminar: {str(e)}")
    else:
        raise HTTPException(status_code=404, detail="El archivo no existe.")