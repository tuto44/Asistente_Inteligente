from langchain_google_genai import GoogleGenerativeAIEmbeddings, ChatGoogleGenerativeAI
from app.database.qdrant_manager import QdrantManager
from app.prompts.system_prompt import construir_prompt 
from app.config import CHAT_MODEL, EMBEDDING_MODEL, MIN_SIMILARITY_SCORE, TOP_K_RESULTS

class RagService:

    def __init__(self):
        self.qdrant = QdrantManager()
        # ❌ ELIMINADO: self.conversation = ConversationService() (PHP manejará el historial)
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
            
        ultimos_mensajes = historial[-4:]
        historial_texto = []
        
        for mensaje in ultimos_mensajes:
            # Compatibilidad: detecta si viene como objeto Pydantic o diccionario de PHP
            role = mensaje.role if hasattr(mensaje, 'role') else mensaje.get('role', '')
            content = mensaje.content if hasattr(mensaje, 'content') else mensaje.get('content', '')
            
            rol = "Usuario" if role == "user" else "Asistente"
            historial_texto.append(f"{rol}: {content}")
            
        return (
            "Contexto de la conversación:\n"
            + "\n".join(historial_texto)
            + f"\n\nPregunta actual:\n{pregunta}"
        )

    def recuperar_contexto(self, pregunta: str, historial: list, limite: int = TOP_K_RESULTS) -> str:
        consulta_rag = self.construir_consulta_rag(
            pregunta=pregunta,
            historial=historial
        )
        print(f"Consulta utilizada para RAG:\n{consulta_rag}")
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

    def responder(self, pregunta: str, usuario: str = "Usuario", historial: list = None) -> str:
        print(f"Pregunta recibida de {usuario}: {pregunta}")
        
        if historial is None:
            historial = []

        # 1. Recuperar contexto usando el historial enviado por PHP
        contexto = self.recuperar_contexto(
            pregunta=pregunta,
            historial=historial
        )
        
        # 2. Si no hay contexto que supere el umbral
        if contexto is None:
            return (
                "No encontré información relacionada con tu consulta en la "
                "documentación disponible. Te recomiendo contactar al área de TI."
            )
        
        # 3. Construir prompt con la función de system_prompt.py
        prompt = construir_prompt(
            usuario=usuario,
            pregunta=pregunta,
            contexto=contexto,
            historial=historial
        )
        
        respuesta = self.llm.invoke(prompt)
        print("Respuesta generada correctamente.")
        
        # 4. Extraer el texto limpiamente (soporta strings o listas de bloques)
        content = respuesta.content if hasattr(respuesta, 'content') else str(respuesta)
        
        if isinstance(content, list):
            partes_texto = []
            for bloque in content:
                if isinstance(bloque, str):
                    partes_texto.append(bloque)
                elif isinstance(bloque, dict) and "text" in bloque:
                    partes_texto.append(bloque["text"])
                elif hasattr(bloque, "text"):
                    partes_texto.append(str(bloque.text))
            respuesta_texto = "".join(partes_texto)
        else:
            respuesta_texto = str(content)

        return respuesta_texto