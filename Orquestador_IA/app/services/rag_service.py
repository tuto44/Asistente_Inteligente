from langchain_google_genai import (GoogleGenerativeAIEmbeddings, ChatGoogleGenerativeAI)
from app.database.qdrant_manager import QdrantManager
from app.prompts.system_prompt import construir_prompt 
from app.config import CHAT_MODEL, EMBEDDING_MODEL, MIN_SIMILARITY_SCORE, TOP_K_RESULTS


class RagService:

    def __init__(self):
        self.qdrant = QdrantManager()
        self.embedding_model = GoogleGenerativeAIEmbeddings(
            model=EMBEDDING_MODEL
        )
        self.llm = ChatGoogleGenerativeAI(
            model=CHAT_MODEL,
            temperature=0.2
        )
        
    def generar_embedding(self, pregunta: str) -> list[float]:
        print("Generando embedding de la pregunta...")
        embedding = self.embedding_model.embed_query(pregunta)
        print(f"Embedding generado ({len(embedding)} dimensiones).")
        return embedding

    def construir_consulta_rag(self, pregunta: str, historial: list) -> str:
        if not historial:
            return pregunta
        
        # Para respuestas muy cortas ("sí", "no", "listo"), se combina con la pregunta previa
        # para evitar que el vector pierda el contexto del tema
        palabras = pregunta.strip().split()
        if len(palabras) <= 4:
            for mensaje in reversed(historial):
                role = mensaje.role if hasattr(mensaje, 'role') else mensaje.get('role', '')
                content = mensaje.content if hasattr(mensaje, 'content') else mensaje.get('content', '')
                if role == 'user' and len(content.strip().split()) > 3:
                    return f"{content} {pregunta}"
        
        return pregunta

    def recuperar_contexto(self, pregunta: str, historial: list, limite: int = TOP_K_RESULTS) -> str:
        consulta_rag = self.construir_consulta_rag(
            pregunta=pregunta,
            historial=historial
        )
        print(f"Consulta utilizada para RAG: {consulta_rag}")
        embedding = self.generar_embedding(consulta_rag)
        
        resultados = self.qdrant.buscar_similares(
            embedding=embedding,
            limite=limite
        )
        if not resultados:
            return None
            
        print(f"Se encontraron {len(resultados)} resultados.")
        contexto = []
        for resultado in resultados:
            if resultado.score < MIN_SIMILARITY_SCORE:
                continue
            print(f"Score: {resultado.score:.4f}")
            contexto.append(resultado.payload["page_content"])
            
        if not contexto:
            print("Ningún resultado superó el umbral de similitud.")
            return None
            
        return "\n\n".join(contexto)  

    def responder(self, usuario: str, pregunta: str, historial: list = None) -> str:
        print(f"Pregunta recibida de {usuario}: {pregunta}")
        if historial is None:
            historial = []

        # 1. Recuperar contexto vectorial utilizando el historial enviado por PHP
        contexto = self.recuperar_contexto(
            pregunta=pregunta,
            historial=historial
        )
        
        # 2. Evaluación si la documentación cubre el caso
        if contexto is None:
            return (
                "No encontré información relacionada con tu consulta en la "
                "documentación disponible. Te recomiendo hablar directamente con el encargado de TI de HWI."
            )
        
        # 3. Construir prompt completo
        prompt = construir_prompt(
            usuario=usuario,
            pregunta=pregunta,
            contexto=contexto,
            historial=historial
        )
        
        # 4. Invocación al LLM
        respuesta = self.llm.invoke(prompt)
        print("Respuesta generada correctamente.")
        
        # 5. Extracción limpia de texto en objetos AIMessage
        content = respuesta.content if hasattr(respuesta, 'content') else str(respuesta)
        if isinstance(content, list):
            partes = [
                b if isinstance(b, str) else b.get("text", str(b))
                for b in content
            ]
            return "".join(partes)
        
        return str(content)