import os
from dotenv import load_dotenv

load_dotenv()

# Variables de entorno y configuración general
GOOGLE_API_KEY = os.getenv("GOOGLE_API_KEY")
CHAT_MODEL = "gemini-3.5-flash-lite"
EMBEDDING_MODEL = "gemini-embedding-001"

QDRANT_URL = os.getenv("QDRANT_URL")
COLLECTION_NAME = "documentacion_ti"

TOP_K_RESULTS = 5
MIN_SIMILARITY_SCORE = 0.60

DOCUMENTS_FOLDER = "./documentos"
CHUNK_SIZE = 500
CHUNK_OVERLAP = 100

INTERNAL_API_KEY: str = os.getenv("INTERNAL_API_KEY", "secreto_123")


# Clase y objeto para compatibilidad cuando se importa `from app.config import config`
class Config:
    GOOGLE_API_KEY = GOOGLE_API_KEY
    CHAT_MODEL = CHAT_MODEL
    EMBEDDING_MODEL = EMBEDDING_MODEL
    QDRANT_URL = QDRANT_URL
    COLLECTION_NAME = COLLECTION_NAME
    TOP_K_RESULTS = TOP_K_RESULTS
    MIN_SIMILARITY_SCORE = MIN_SIMILARITY_SCORE
    DOCUMENTS_FOLDER = DOCUMENTS_FOLDER
    CHUNK_SIZE = CHUNK_SIZE
    CHUNK_OVERLAP = CHUNK_OVERLAP
    INTERNAL_API_KEY = INTERNAL_API_KEY


config = Config()