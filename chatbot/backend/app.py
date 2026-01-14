from flask import Flask, request, jsonify
from flask_cors import CORS
import requests
import re
import logging
import sqlite3
import os
from enum import Enum

# ==================== CONFIGURATION ====================
app = Flask(__name__)
CORS(app)
logging.basicConfig(level=logging.INFO, format='%(asctime)s - %(levelname)s - %(message)s')

LM_STUDIO_URL = "http://127.0.0.1:1234/v1/completions"
MODEL_NAME = "mistral-7b-instruct-v0.2.Q4_K_M.gguf"
DB_PATH = 'student_system.db'

# Auto-create test database if missing
if not os.path.exists(DB_PATH):
    logging.warning(f"Creating test database at {DB_PATH}")
    conn = sqlite3.connect(DB_PATH)
    cursor = conn.cursor()
    cursor.execute('CREATE TABLE IF NOT EXISTS absences (id INTEGER PRIMARY KEY, student_email TEXT, module_code TEXT, date TEXT, excused INTEGER)')
    cursor.execute('CREATE TABLE IF NOT EXISTS schedule (id INTEGER PRIMARY KEY, student_email TEXT, module_code TEXT, room TEXT, start_time TEXT)')
    cursor.execute('CREATE TABLE IF NOT EXISTS modules (id INTEGER PRIMARY KEY, student_email TEXT, module_code TEXT, module_name TEXT, total_hours INTEGER)')
    cursor.execute("INSERT INTO absences VALUES (1, 'demo@student.edu', 'CS301', '2026-01-08', 1)")
    cursor.execute("INSERT INTO schedule VALUES (1, 'demo@student.edu', 'CS301', '301', '2026-01-10 09:00:00')")
    cursor.execute("INSERT INTO modules VALUES (1, 'demo@student.edu', 'CS301', 'Data Structures', 45)")
    conn.commit()
    conn.close()

DB_CONFIG = {'sqlite': {'database': DB_PATH}}

# ==================== INTENT ENUM ====================
class Intent(Enum):
    ABSENCE_QUERY = "absence_query"
    SCHEDULE_QUERY = "schedule_query"
    MODULE_LIST = "module_list"
    FALLBACK = "fallback"
    
# ==================== SEMANTIC INTENT CLASSIFIER ====================
def classify_intent_with_llm(message: str) -> tuple[Intent, float]:
    """
    Uses your local LLM to UNDERSTAND meaning, not just match words
    Returns (intent, confidence)
    """
    if not message or len(message.strip()) < 2:
        return Intent.FALLBACK, 0.0
    
    # Few-shot classification prompt
    classifier_prompt = f"""Classify this student query into ONE category:
- ABSENCE_QUERY: Questions about missed classes, absences, skipping, being away, not attending
- SCHEDULE_QUERY: Questions about next class, schedule, timetable, when classes are
- MODULE_LIST: Questions about enrolled modules, courses, subjects
- FALLBACK: None of the above

Examples:
"How many classes did I skip?" → ABSENCE_QUERY
"I was away yesterday" → ABSENCE_QUERY
"When is my next lesson?" → SCHEDULE_QUERY
"What modules am I taking?" → MODULE_LIST
"What is quantum physics?" → FALLBACK

Query: "{message}"
Classification:"""

    try:
        response = requests.post(
            LM_STUDIO_URL,
            json={
                "model": MODEL_NAME,
                "prompt": classifier_prompt,
                "max_tokens": 20,
                "temperature": 0.1,  # Low temp for consistency
                "stop": ["\n", "Query:"]
            },
            timeout=10
        )
        response.raise_for_status()
        
        classification = response.json().get("choices", [{}])[0].get("text", "").strip().upper()
        logging.info(f"LLM classification: '{classification}' for query: '{message}'")
        
        # Map LLM output to Intent enum
        if "ABSENCE_QUERY" in classification:
            return Intent.ABSENCE_QUERY, 0.95
        elif "SCHEDULE_QUERY" in classification:
            return Intent.SCHEDULE_QUERY, 0.95
        elif "MODULE_LIST" in classification:
            return Intent.MODULE_LIST, 0.95
        else:
            return Intent.FALLBACK, 0.5
            
    except Exception as e:
        logging.error(f"LLM classification failed: {e}")
        return Intent.FALLBACK, 0.0

def classify_intent(message: str) -> Intent:
    """
    TWO-STAGE CLASSIFIER:
    1. Fast regex for common patterns (high confidence)
    2. LLM semantic analysis for everything else
    """
    msg_lower = message.lower()
    
    # Stage 1: Regex for high-confidence matches
    regex_patterns = {
        Intent.ABSENCE_QUERY: [r"\babsences?\b", r"\bskip(?:s|ped|ping)?\b", r"\bmiss(?:es|ed|ing)?\b"],
        Intent.SCHEDULE_QUERY: [r"\bnext\b.*?\bclass\b", r"\bwhen\b.*?\bclass\b", r"\bschedule\b"],
        Intent.MODULE_LIST: [r"\bmodules?\b", r"\bcourses?\b", r"\bsubjects?\b"]
    }
    
    for intent, patterns in regex_patterns.items():
        if any(re.search(p, msg_lower) for p in patterns):
            logging.info(f"Regex matched: {intent.value}")
            return intent
    
    # Stage 2: LLM for semantic understanding
    logging.info("Regex failed, using LLM classification...")
    return classify_intent_with_llm(message)[0]

# ==================== DATABASE LAYER ====================
def get_db_connection():
    conn = sqlite3.connect(DB_CONFIG['sqlite']['database'])
    conn.row_factory = sqlite3.Row
    return conn

def execute_sql_for_intent(intent: Intent, email: str, message: str) -> str:
    conn = get_db_connection()
    cursor = conn.cursor()
    
    try:
        if intent == Intent.ABSENCE_QUERY:
            cursor.execute("SELECT module_code, COUNT(*) as total FROM absences WHERE student_email = ? GROUP BY module_code", (email,))
            rows = cursor.fetchall()
            if not rows:
                return "✅ No absences recorded."
            result = f"📊 Absences for {email}:\n"
            for row in rows:
                result += f"• {row['module_code']}: {row['total']} classes\n"
            return result
        
        elif intent == Intent.SCHEDULE_QUERY:
            cursor.execute("SELECT module_code, room, start_time FROM schedule WHERE student_email = ? AND start_time > datetime('now') ORDER BY start_time ASC LIMIT 1", (email,))
            row = cursor.fetchone()
            if row:
                return f"🕒 Next: {row['module_code']} at {row['start_time']} in {row['room']}"
            return "No upcoming classes."
        
        elif intent == Intent.MODULE_LIST:
            cursor.execute("SELECT module_code, module_name, total_hours FROM modules WHERE student_email = ? ORDER BY module_code", (email,))
            rows = cursor.fetchall()
            if not rows:
                return "No modules found."
            result = f"📚 Modules:\n"
            for row in rows:
                result += f"• {row['module_code']}: {row['module_name']} ({row['total_hours']}h)\n"
            return result
            
    except sqlite3.Error as e:
        logging.error(f"Database error: {e}")
        return f"❌ Database error: {str(e)}"
    finally:
        conn.close()

# ==================== LLM FALLBACK ====================
def call_lm_studio(message: str) -> str:
    try:
        prompt = f"You are a student assistant. Answer briefly.\n\nUser: {message}\nAssistant:"
        
        response = requests.post(
            LM_STUDIO_URL,
            json={
                "model": MODEL_NAME,
                "prompt": prompt,
                "max_tokens": 200,
                "temperature": 0.7
            },
            timeout=15
        )
        response.raise_for_status()
        
        return response.json().get("choices", [{}])[0].get("text", "No response").strip()
        
    except Exception as e:
        logging.error(f"LLM error: {e}")
        return "AI service unavailable."

# ==================== MAIN CHAT ENDPOINT ====================
@app.route("/chat", methods=["POST"])
def chat():
    data = request.json
    message = data.get("message", "").strip()
    email = data.get("email", "").strip() or "demo@student.edu"
    
    if not message:
        return jsonify({"error": "No message"}), 400
    
    logging.info(f"Processing: '{message}' for {email}")
    
    # Use the LM to classify intent, then route accordingly
    intent = classify_intent(message)
    if intent != Intent.FALLBACK:
        answer = execute_sql_for_intent(intent, email, message)
        source = "database"
    else:
        answer = call_lm_studio(message)
        source = "llm"
    return jsonify({
        "answer": answer,
        "intent": intent.value,
        "source": source
    })

@app.route("/health", methods=["GET"])
def health_check():
    return jsonify({"status": "healthy", "model": MODEL_NAME, "app": "student-assistant"})

if __name__ == "__main__":
    app.run(debug=False, port=5000)