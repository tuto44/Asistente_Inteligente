import logging
from typing import Any
from langchain_google_genai import (GoogleGenerativeAIEmbeddings, ChatGoogleGenerativeAI)
from langchain_core.messages import HumanMessage
from app.database.qdrant_manager import QdrantManager
from app.prompts.system_prompt import construir_prompt
from app.config import (CHAT_MODEL, EMBEDDING_MODEL, MIN_SIMILARITY_SCORE, TOP_K_RESULTS)

logger = logging.getLogger(__name__)

class RagService:
    RESPUESTAS_CORTAS = {
        "si", "sí", "no", "ok", "vale", "listo", "hecho",
        "correcto", "exacto", "ya", "continuar", "continúa",
        "continue", "siguiente", "funcionó", "funciono",
        "no funcionó", "no funciono"
    }

    def __init__(self):
        logger.info("Inicializando RagService...")
        self.qdrant = QdrantManager()
        self.embedding_model = GoogleGenerativeAIEmbeddings(model=EMBEDDING_MODEL)
        self.llm = ChatGoogleGenerativeAI(
            model=CHAT_MODEL,
            temperature=0.0,
            max_output_tokens=350
        )

    @staticmethod
    def obtener_campo(mensaje: Any, campo: str, default: str = "") -> str:
        if hasattr(mensaje, campo):
            valor = getattr(mensaje, campo)
        elif isinstance(mensaje, dict):
            valor = mensaje.get(campo, default)
        else:
            valor = default
        return str(valor or "").strip()

    @staticmethod
    def normalizar_texto(texto: str) -> str:
        return " ".join(texto.lower().strip().split())

    def es_respuesta_corta(self, pregunta: str) -> bool:
        texto = self.normalizar_texto(pregunta)
        if texto in self.RESPUESTAS_CORTAS:
            return True
        return len(texto.split()) <= 3

    def generar_embedding(self, texto: str) -> list[float]:
        try:
            return self.embedding_model.embed_query(texto)
        except Exception:
            logger.exception("Error generando embedding.")
            raise

    def construir_consulta_rag(self, pregunta: str, historial: list) -> str:
        pregunta = pregunta.strip()
        if not historial or not self.es_respuesta_corta(pregunta):
            return pregunta

        mensajes_relevantes = historial[-4:]
        contexto_conversacional = []

        for mensaje in mensajes_relevantes:
            role = self.obtener_campo(mensaje, "role")
            content = self.obtener_campo(mensaje, "content")
            if content:
                rol = "Usuario" if role == "user" else "Asistente"
                contexto_conversacional.append(f"{rol}: {content}")

        if not contexto_conversacional:
            return pregunta

        return (
            "Contexto de la conversación:\n"
            + "\n".join(contexto_conversacional)
            + f"\n\nRespuesta actual del usuario: {pregunta}"
        )

    def recuperar_contexto(
        self,
        pregunta: str,
        historial: list,
        limite: int = TOP_K_RESULTS,
        min_score: float = MIN_SIMILARITY_SCORE
    ) -> str | None:

        consulta_rag = self.construir_consulta_rag(pregunta=pregunta, historial=historial)

        try:
            embedding = self.generar_embedding(consulta_rag)
            resultados = self.qdrant.buscar_similares(embedding=embedding, limite=limite)
        except Exception:
            logger.exception("Error buscando en Qdrant.")
            return None

        if not resultados:
            return None

        contexto = []
        contenidos_vistos = set()

        for resultado in resultados:
            score = getattr(resultado, "score", 0.0)
            payload = getattr(resultado, "payload", {}) or {}

            if score < min_score:
                continue

            contenido = payload.get("page_content")
            if not contenido:
                continue

            contenido_normalizado = contenido.strip()
            if contenido_normalizado in contenidos_vistos:
                continue

            contenidos_vistos.add(contenido_normalizado)
            document_name = payload.get("document_name", "Documento")
            page = payload.get("page", "N/A")
            chunk_index = payload.get("chunk_index", "N/A")

            contexto.append(
                "\n".join([
                    "----------------------------------------",
                    f"DOCUMENTO: {document_name}",
                    f"PÁGINA: {page}",
                    f"CHUNK: {chunk_index}",
                    f"SIMILITUD: {score:.4f}",
                    "CONTENIDO:",
                    contenido_normalizado
                ])
            )

        return "\n\n".join(contexto) if contexto else None

    @staticmethod
    def extraer_contenido(respuesta: Any) -> str:
        content = respuesta.content if hasattr(respuesta, "content") else str(respuesta)
        if isinstance(content, str):
            return content.strip()
        if isinstance(content, list):
            partes = [b if isinstance(b, str) else b.get("text", "") for b in content if b]
            return "".join(partes).strip()
        return str(content).strip()

    def responder(
        self,
        usuario: str,
        pregunta: str,
        historial: list = None,
        image_base64: str = None
    ) -> str:

        pregunta = pregunta.strip()
        if historial is None:
            historial = []

        if not pregunta and not image_base64:
            return "Por favor, escribe tu consulta o adjunta una imagen."

        # Búsqueda inicial en RAG
        pregunta_rag = pregunta if pregunta else "pantalla error soporte acceso sistema"
        contexto = self.recuperar_contexto(pregunta=pregunta_rag, historial=historial, limite=TOP_K_RESULTS)

        # Si envió imagen y no hubo coincidencia estricta (score >= 0.60), reintentar con umbral flexible (0.35)
        if contexto is None and image_base64:
            logger.info("Imagen detectada sin RAG directo: Reintentando búsqueda con umbral flexible.")
            contexto = self.recuperar_contexto(pregunta=pregunta_rag, historial=historial, limite=TOP_K_RESULTS, min_score=0.35)

        # Si aún no hay coincidencia, proporcionar catálogo general para que Gemini identifique la pantalla
        if contexto is None and image_base64:
            contexto = (
                "DOCUMENTACIÓN GENERAL DE SISTEMAS HWI Y WHIRLPOOL:\n"
                "- WPASS / SAP / GlobalProtect / Citrix / Windchill (Whirlpool)\n"
                "- VPN FortiClient / Red Interna HWI (HWI)"
            )

        if contexto is None:
            return (
                "No encontré información relacionada con tu consulta en la documentación disponible. "
                "Te recomiendo hablar directamente con el encargado de TI de HWI."
            )

        try:
            prompt = construir_prompt(
                usuario=usuario,
                pregunta=pregunta if pregunta else "[El usuario ha enviado una captura de pantalla]",
                contexto=contexto,
                historial=historial
            )

            if image_base64:
                contenido_multimodal = [
                    {"type": "text", "text": prompt},
                    {
                        "type": "image_url",
                        "image_url": {"url": f"data:image/jpeg;base64,{image_base64}"}
                    }
                ]
                respuesta = self.llm.invoke([HumanMessage(content=contenido_multimodal)])
            else:
                respuesta = self.llm.invoke(prompt)

            return self.extraer_contenido(respuesta)

        except Exception:
            logger.exception("Error al procesar la solicitud.")
            return "No fue posible generar una respuesta en este momento. Te recomiendo intentarlo nuevamente."