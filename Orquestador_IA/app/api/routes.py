from fastapi import APIRouter, Depends
from app.models.schemas import ChatRequest, ChatResponse
from app.api.security import verificar_api_key
from app.services.rag_service import RagService

router = APIRouter()
rag_service = RagService()

@router.post("/chat", response_model=ChatResponse, dependencies=[Depends(verificar_api_key)])
def chat(request: ChatRequest):
    respuesta_texto = rag_service.responder(
        usuario=request.user_name,  # <-- Agregamos esta línea que faltaba
        pregunta=request.question,
        historial=request.historial
    )
    
    return ChatResponse(
        status="success",
        answer=respuesta_texto
    )