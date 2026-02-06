from flask import Flask, request, jsonify
from flask_cors import CORS
import mysql.connector
import requests
import json
import logging
from typing import Tuple, Dict, Any, Optional
from enum import Enum

# ==================== CONFIGURATION ====================
app = Flask(__name__)
CORS(app)
logging.basicConfig(
    level=logging.INFO,
    format='%(asctime)s - %(levelname)s - %(message)s'
)

# Groq Configuration
GROQ_MODEL = "llama-3.3-70b-versatile"
GROQ_URL = "https://api.groq.com/openai/v1/chat/completions"

# MySQL Configuration
DB_CONFIG = {
    "host": "127.0.0.1",
    "user": "root",
    "password": "",
    "database": "gestion_absences"
}

# ==================== ACTION ENUM ====================
class Action(Enum):
    """Possible actions the AI can route to"""
    # Absence queries
    ABSENCE_COUNT = "get_absence_count"
    ABSENCE_BY_MODULE = "get_absence_by_module"
    ABSENCE_THIS_WEEK = "get_absence_this_week"
    ABSENCE_THIS_MONTH = "get_absence_this_month"
    EXCUSED_VS_UNEXCUSED = "get_excused_vs_unexcused"
    ATTENDANCE_RATE = "get_attendance_rate"
    
    # Schedule queries
    NEXT_CLASS = "get_next_class"
    LAST_CLASS_OF_WEEK = "get_last_class_of_week"
    TODAY_SCHEDULE = "get_today_schedule"
    WEEK_SCHEDULE = "get_week_schedule"
    CLASSES_TODAY_COUNT = "get_classes_today_count"
    
    # Module queries
    MODULE_LIST = "get_modules_list"
    MODULE_DETAILS = "get_module_details"
    PROFESSORS_LIST = "get_professors_list"
    
    # General
    CONVERSATION = "conversation"
    UNCLEAR = "unclear"

# ==================== DATABASE LAYER ====================
def get_db_connection():
    """Create and return a MySQL database connection"""
    return mysql.connector.connect(**DB_CONFIG)

def get_student_id(email: str) -> Optional[int]:
    """Get student ID from email"""
    try:
        conn = get_db_connection()
        cursor = conn.cursor()
        cursor.execute(
            "SELECT id FROM users WHERE email=%s AND role='student'",
            (email,)
        )
        result = cursor.fetchone()
        conn.close()
        return result[0] if result else None
    except mysql.connector.Error as e:
        logging.error(f"❌ Database error getting student ID: {e}")
        return None

def get_absence_count(student_id: int) -> int:
    """Get total absence count for a student"""
    try:
        conn = get_db_connection()
        cursor = conn.cursor()
        cursor.execute(
            "SELECT COUNT(*) FROM attendance WHERE student_id=%s AND status='absent'",
            (student_id,)
        )
        result = cursor.fetchone()
        conn.close()
        return result[0] if result else 0
    except mysql.connector.Error as e:
        logging.error(f"❌ Database error: {e}")
        return 0

def get_absences_by_module(student_id: int) -> list:
    """Get absence breakdown by module"""
    try:
        conn = get_db_connection()
        cursor = conn.cursor()
        cursor.execute("""
            SELECT m.module_name, COUNT(*) as absence_count
            FROM attendance a
            JOIN modules m ON a.module_id = m.id
            WHERE a.student_id=%s AND a.status='absent'
            GROUP BY m.module_name
            ORDER BY absence_count DESC
        """, (student_id,))
        result = cursor.fetchall()
        conn.close()
        return result
    except mysql.connector.Error as e:
        logging.error(f"❌ Database error: {e}")
        return []

def get_next_class(student_id: int) -> Optional[tuple]:
    """Get next scheduled class based on current day and time"""
    try:
        conn = get_db_connection()
        cursor = conn.cursor()
        cursor.execute("""
            SELECT m.module_name, ms.weekday, ms.start_time
            FROM module_schedule ms
            JOIN modules m ON ms.module_id=m.id
            JOIN attendance a ON a.module_id=m.id
            WHERE a.student_id=%s
            AND (
                ms.weekday > DAYOFWEEK(CURDATE())
                OR (ms.weekday = DAYOFWEEK(CURDATE()) AND ms.start_time > CURTIME())
            )
            ORDER BY ms.weekday ASC, ms.start_time ASC
            LIMIT 1
        """, (student_id,))
        result = cursor.fetchone()
        conn.close()
        return result
    except mysql.connector.Error as e:
        logging.error(f"❌ Database error: {e}")
        return None

def get_last_class_of_week(student_id: int) -> Optional[tuple]:
    """Get the last class of the current week"""
    try:
        conn = get_db_connection()
        cursor = conn.cursor()
        cursor.execute("""
            SELECT m.module_name, ms.weekday, ms.start_time
            FROM module_schedule ms
            JOIN modules m ON ms.module_id=m.id
            JOIN attendance a ON a.module_id=m.id
            WHERE a.student_id=%s
            ORDER BY ms.weekday DESC, ms.start_time DESC
            LIMIT 1
        """, (student_id,))
        result = cursor.fetchone()
        conn.close()
        return result
    except mysql.connector.Error as e:
        logging.error(f"❌ Database error: {e}")
        return None

def get_attendance_rate(student_id: int) -> Optional[Dict[str, Any]]:
    """Calculate overall attendance rate"""
    try:
        conn = get_db_connection()
        cursor = conn.cursor()
        cursor.execute("""
            SELECT 
                COUNT(*) as total_sessions,
                SUM(CASE WHEN status = 'present' THEN 1 ELSE 0 END) as present_count,
                SUM(CASE WHEN status = 'absent' THEN 1 ELSE 0 END) as absent_count,
                ROUND((SUM(CASE WHEN status = 'present' THEN 1 ELSE 0 END) / COUNT(*)) * 100, 2) as attendance_rate
            FROM attendance
            WHERE student_id = %s
        """, (student_id,))
        result = cursor.fetchone()
        conn.close()
        
        if result and result[0] > 0:
            return {
                'total_sessions': result[0],
                'present': result[1],
                'absent': result[2],
                'rate': result[3]
            }
        return None
    except mysql.connector.Error as e:
        logging.error(f"❌ Database error: {e}")
        return None

def get_absences_this_week(student_id: int) -> int:
    """Get number of absences in current week"""
    try:
        conn = get_db_connection()
        cursor = conn.cursor()
        cursor.execute("""
            SELECT COUNT(*)
            FROM attendance
            WHERE student_id = %s 
            AND status = 'absent'
            AND YEARWEEK(date, 1) = YEARWEEK(CURDATE(), 1)
        """, (student_id,))
        result = cursor.fetchone()
        conn.close()
        return result[0] if result else 0
    except mysql.connector.Error as e:
        logging.error(f"❌ Database error: {e}")
        return 0

def get_absences_this_month(student_id: int) -> int:
    """Get number of absences in current month"""
    try:
        conn = get_db_connection()
        cursor = conn.cursor()
        cursor.execute("""
            SELECT COUNT(*)
            FROM attendance
            WHERE student_id = %s 
            AND status = 'absent'
            AND MONTH(date) = MONTH(CURDATE())
            AND YEAR(date) = YEAR(CURDATE())
        """, (student_id,))
        result = cursor.fetchone()
        conn.close()
        return result[0] if result else 0
    except mysql.connector.Error as e:
        logging.error(f"❌ Database error: {e}")
        return 0

def get_today_schedule(student_id: int) -> list:
    """Get all classes scheduled for today"""
    try:
        conn = get_db_connection()
        cursor = conn.cursor()
        cursor.execute("""
            SELECT m.module_name, ms.start_time, ms.end_time, ms.room
            FROM module_schedule ms
            JOIN modules m ON ms.module_id = m.id
            JOIN attendance a ON a.module_id = m.id
            WHERE a.student_id = %s
            AND ms.weekday = DAYOFWEEK(CURDATE())
            ORDER BY ms.start_time ASC
        """, (student_id,))
        result = cursor.fetchall()
        conn.close()
        return result
    except mysql.connector.Error as e:
        logging.error(f"❌ Database error: {e}")
        return []

def get_week_schedule(student_id: int) -> list:
    """Get full week schedule"""
    try:
        conn = get_db_connection()
        cursor = conn.cursor()
        cursor.execute("""
            SELECT m.module_name, ms.weekday, ms.start_time, ms.end_time, ms.room
            FROM module_schedule ms
            JOIN modules m ON ms.module_id = m.id
            JOIN attendance a ON a.module_id = m.id
            WHERE a.student_id = %s
            ORDER BY ms.weekday ASC, ms.start_time ASC
        """, (student_id,))
        result = cursor.fetchall()
        conn.close()
        return result
    except mysql.connector.Error as e:
        logging.error(f"❌ Database error: {e}")
        return []

def get_module_details(student_id: int, module_name: str) -> Optional[Dict[str, Any]]:
    """Get detailed information about a specific module"""
    try:
        conn = get_db_connection()
        cursor = conn.cursor()
        cursor.execute("""
            SELECT 
                m.module_name,
                CONCAT(u.prenom, ' ', u.nom) as professor,
                COUNT(CASE WHEN a.status = 'absent' THEN 1 END) as absences,
                COUNT(*) as total_sessions
            FROM modules m
            JOIN users u ON m.professor_id = u.id
            JOIN attendance a ON a.module_id = m.id
            WHERE a.student_id = %s
            AND m.module_name LIKE %s
            GROUP BY m.id, m.module_name, u.prenom, u.nom
        """, (student_id, f"%{module_name}%"))
        result = cursor.fetchone()
        conn.close()
        
        if result:
            return {
                'module_name': result[0],
                'professor': result[1],
                'absences': result[2],
                'total_sessions': result[3]
            }
        return None
    except mysql.connector.Error as e:
        logging.error(f"❌ Database error: {e}")
        return None

def get_professors_list(student_id: int) -> list:
    """Get list of all professors teaching the student"""
    try:
        conn = get_db_connection()
        cursor = conn.cursor()
        cursor.execute("""
            SELECT DISTINCT 
                CONCAT(u.prenom, ' ', u.nom) as professor_name,
                u.email,
                GROUP_CONCAT(m.module_name SEPARATOR ', ') as modules
            FROM modules m
            JOIN users u ON m.professor_id = u.id
            JOIN attendance a ON a.module_id = m.id
            WHERE a.student_id = %s
            GROUP BY u.id, u.prenom, u.nom, u.email
            ORDER BY u.nom ASC
        """, (student_id,))
        result = cursor.fetchall()
        conn.close()
        return result
    except mysql.connector.Error as e:
        logging.error(f"❌ Database error: {e}")
        return []

def get_classes_today_count(student_id: int) -> int:
    """Get number of classes scheduled for today"""
    try:
        conn = get_db_connection()
        cursor = conn.cursor()
        cursor.execute("""
            SELECT COUNT(*)
            FROM module_schedule ms
            JOIN attendance a ON a.module_id = ms.module_id
            WHERE a.student_id = %s
            AND ms.weekday = DAYOFWEEK(CURDATE())
        """, (student_id,))
        result = cursor.fetchone()
        conn.close()
        return result[0] if result else 0
    except mysql.connector.Error as e:
        logging.error(f"❌ Database error: {e}")
        return 0

def get_excused_vs_unexcused_absences(student_id: int) -> Optional[Dict[str, int]]:
    """Get breakdown of excused vs unexcused absences"""
    try:
        conn = get_db_connection()
        cursor = conn.cursor()
        cursor.execute("""
            SELECT 
                COUNT(CASE WHEN excused = 1 THEN 1 END) as excused,
                COUNT(CASE WHEN excused = 0 THEN 1 END) as unexcused,
                COUNT(*) as total
            FROM attendance
            WHERE student_id = %s
            AND status = 'absent'
        """, (student_id,))
        result = cursor.fetchone()
        conn.close()
        
        if result:
            return {
                'excused': result[0] or 0,
                'unexcused': result[1] or 0,
                'total': result[2] or 0
            }
        return None
    except mysql.connector.Error as e:
        logging.error(f"❌ Database error: {e}")
        return None

def get_modules(student_id: int) -> list:
    """Get list of enrolled modules with professors"""
    try:
        conn = get_db_connection()
        cursor = conn.cursor()
        cursor.execute("""
            SELECT DISTINCT m.module_name, u.nom, u.prenom
            FROM modules m
            JOIN users u ON m.professor_id=u.id
            JOIN attendance a ON a.module_id=m.id
            WHERE a.student_id=%s
        """, (student_id,))
        result = cursor.fetchall()
        conn.close()
        return result
    except mysql.connector.Error as e:
        logging.error(f"❌ Database error: {e}")
        return []

# ==================== AI ROUTING WITH CONFIDENCE ====================
def ai_route_with_confidence(message: str, student_id: int) -> Tuple[Action, float]:
    """
    HYBRID AI ROUTING:
    - Uses AI to decide action (flexible like alternative)
    - Returns confidence score (robust like my code)
    - JSON-based for flexibility
    - Validated for robustness
    
    Returns (action, confidence_score)
    """
    headers = {
        "Authorization": f"Bearer {GROQ_API_KEY}",
        "Content-Type": "application/json"
    }

    prompt = f"""You are an intelligent API router for a student information system.

Available actions (choose ONE):

ABSENCE QUERIES:
- get_absence_count: Total absences across all time
- get_absence_by_module: Absences broken down by each module
- get_absence_this_week: Absences in current week only
- get_absence_this_month: Absences in current month only
- get_excused_vs_unexcused: Breakdown of excused vs unexcused absences
- get_attendance_rate: Overall attendance percentage

SCHEDULE QUERIES:
- get_next_class: Next upcoming class
- get_last_class_of_week: Last class in the week
- get_today_schedule: All classes today
- get_week_schedule: Full weekly schedule
- get_classes_today_count: How many classes today

MODULE QUERIES:
- get_modules_list: All enrolled modules with professors
- get_module_details: Detailed info about a specific module
- get_professors_list: All professors teaching this student

GENERAL:
- conversation: Math, greetings, general questions, chitchat
- unclear: Cannot determine intent

Student ID: {student_id}
User: "{message}"

IMPORTANT RULES:
- For math questions like "1+1", "what's 5*3", use "conversation"
- For greetings like "hi", "hello", use "conversation"
- For general questions not about student data, use "conversation"

Respond ONLY with valid JSON:
{{"action": "<action_name>", "confidence": <0.0-1.0>}}
"""

    try:
        response = requests.post(
            GROQ_URL,
            json={
                "model": GROQ_MODEL,
                "messages": [{"role": "user", "content": prompt}],
                "temperature": 0.0,
                "max_tokens": 50
            },
            headers=headers,
            timeout=10
        )
        response.raise_for_status()
        
        content = response.json()["choices"][0]["message"]["content"].strip()
        logging.info(f"🤖 AI raw response: {content}")
        
        # Parse JSON response
        action_data = json.loads(content)
        action_str = action_data.get("action", "unclear")
        confidence = float(action_data.get("confidence", 0.5))
        
        # Map to Action enum
        action_map = {
            # Absence actions
            "get_absence_count": Action.ABSENCE_COUNT,
            "get_absence_by_module": Action.ABSENCE_BY_MODULE,
            "get_absence_this_week": Action.ABSENCE_THIS_WEEK,
            "get_absence_this_month": Action.ABSENCE_THIS_MONTH,
            "get_excused_vs_unexcused": Action.EXCUSED_VS_UNEXCUSED,
            "get_attendance_rate": Action.ATTENDANCE_RATE,
            
            # Schedule actions
            "get_next_class": Action.NEXT_CLASS,
            "get_last_class_of_week": Action.LAST_CLASS_OF_WEEK,
            "get_today_schedule": Action.TODAY_SCHEDULE,
            "get_week_schedule": Action.WEEK_SCHEDULE,
            "get_classes_today_count": Action.CLASSES_TODAY_COUNT,
            
            # Module actions
            "get_modules_list": Action.MODULE_LIST,
            "get_module_details": Action.MODULE_DETAILS,
            "get_professors_list": Action.PROFESSORS_LIST,
            
            # General
            "conversation": Action.CONVERSATION,
            "unclear": Action.UNCLEAR
        }
        
        action = action_map.get(action_str, Action.UNCLEAR)
        
        # Validate confidence range
        confidence = max(0.0, min(1.0, confidence))
        
        logging.info(f"✅ Routing → Action: {action.value} | Confidence: {confidence:.2f}")
        return action, confidence
        
    except json.JSONDecodeError as e:
        logging.error(f"❌ AI returned invalid JSON: {e}")
        return Action.UNCLEAR, 0.0
    except requests.exceptions.Timeout:
        logging.error(f"⏱️ AI request timeout")
        return Action.UNCLEAR, 0.0
    except Exception as e:
        logging.error(f"❌ AI routing failed: {e}")
        return Action.UNCLEAR, 0.0

# ==================== ACTION EXECUTION ====================
def execute_action(action: Action, student_id: int, confidence: float, message: str = "") -> str:
    """
    Execute the routed action and return formatted response.
    Enhanced with rich formatting and all new query types.
    """
    # Helper function to convert weekday number to name
    def get_day_name(weekday: int) -> str:
        days = {1: "Sunday", 2: "Monday", 3: "Tuesday", 4: "Wednesday", 
                5: "Thursday", 6: "Friday", 7: "Saturday"}
        return days.get(weekday, f"Day {weekday}")
    
    # If confidence is too low, don't execute
    if confidence < 0.5:
        return "🤔 I'm not quite sure what you're asking. Could you rephrase that?"
    
    # === ABSENCE QUERIES ===
    if action == Action.ABSENCE_COUNT:
        count = get_absence_count(student_id)
        if count == 0:
            return "🎉 **Great news!** You have no absences recorded."
        return f"📊 **Total Absences**: {count}"
    
    elif action == Action.ABSENCE_BY_MODULE:
        data = get_absences_by_module(student_id)
        if not data:
            return "✅ No absences found in any module."
        
        result = "📊 **Absences by Module**\n\n"
        total = 0
        for module_name, count in data:
            result += f"• **{module_name}**: {count} absence(s)\n"
            total += count
        result += f"\n📌 Total: {total} absence(s) across all modules"
        return result
    
    elif action == Action.ABSENCE_THIS_WEEK:
        count = get_absences_this_week(student_id)
        if count == 0:
            return "✅ **No absences this week!** Keep it up! 🌟"
        return f"📅 **This Week**: {count} absence(s)"
    
    elif action == Action.ABSENCE_THIS_MONTH:
        count = get_absences_this_month(student_id)
        if count == 0:
            return "✅ **Perfect attendance this month!** 🎯"
        return f"📅 **This Month**: {count} absence(s)"
    
    elif action == Action.EXCUSED_VS_UNEXCUSED:
        data = get_excused_vs_unexcused_absences(student_id)
        if not data or data['total'] == 0:
            return "✅ No absences on record!"
        
        return f"""📊 **Absence Breakdown**

✓ Excused: {data['excused']}
✗ Unexcused: {data['unexcused']}
📌 Total: {data['total']}"""
    
    elif action == Action.ATTENDANCE_RATE:
        data = get_attendance_rate(student_id)
        if not data:
            return "📊 No attendance data available yet."
        
        emoji = "🌟" if data['rate'] >= 90 else "👍" if data['rate'] >= 75 else "⚠️"
        return f"""{emoji} **Attendance Rate: {data['rate']}%**

✓ Present: {data['present']} sessions
✗ Absent: {data['absent']} sessions
📊 Total: {data['total_sessions']} sessions"""
    
    # === SCHEDULE QUERIES ===
    elif action == Action.NEXT_CLASS:
        class_info = get_next_class(student_id)
        if not class_info:
            return "📅 No more upcoming classes this week."
        
        module, weekday, time = class_info
        day_name = get_day_name(weekday)
        return f"🕒 **Next Class**: {module}\n📆 {day_name}\n⏰ {time}"
    
    elif action == Action.LAST_CLASS_OF_WEEK:
        class_info = get_last_class_of_week(student_id)
        if not class_info:
            return "📅 No classes scheduled for this week."
        
        module, weekday, time = class_info
        day_name = get_day_name(weekday)
        return f"🏁 **Last Class of the Week**: {module}\n📆 {day_name}\n⏰ {time}"
    
    elif action == Action.TODAY_SCHEDULE:
        schedule = get_today_schedule(student_id)
        if not schedule:
            return "📅 No classes scheduled for today. Enjoy your free day! 🎉"
        
        result = f"📅 **Today's Schedule** ({len(schedule)} class(es))\n\n"
        for module, start, end, room in schedule:
            result += f"• **{module}**\n  ⏰ {start} - {end}\n  📍 Room {room}\n\n"
        return result.strip()
    
    elif action == Action.WEEK_SCHEDULE:
        schedule = get_week_schedule(student_id)
        if not schedule:
            return "📅 No classes scheduled this week."
        
        result = "📅 **Weekly Schedule**\n\n"
        current_day = None
        for module, weekday, start, end, room in schedule:
            day_name = get_day_name(weekday)
            if day_name != current_day:
                result += f"\n**{day_name}**\n"
                current_day = day_name
            result += f"• {start}-{end}: {module} (Room {room})\n"
        return result
    
    elif action == Action.CLASSES_TODAY_COUNT:
        count = get_classes_today_count(student_id)
        if count == 0:
            return "📅 No classes today! 🎉"
        elif count == 1:
            return "📚 You have **1 class** today."
        else:
            return f"📚 You have **{count} classes** today."
    
    # === MODULE QUERIES ===
    elif action == Action.MODULE_LIST:
        modules = get_modules(student_id)
        if not modules:
            return "📚 No modules found."
        
        result = f"📚 **Your Modules** ({len(modules)} total)\n\n"
        for module, nom, prenom in modules:
            result += f"• **{module}**\n  👨‍🏫 Prof. {prenom} {nom}\n"
        return result
    
    elif action == Action.MODULE_DETAILS:
        # Extract module name from message (basic extraction)
        # In production, you'd want more sophisticated NLP here
        module_name = message.lower().replace("details", "").replace("about", "").strip()
        data = get_module_details(student_id, module_name)
        
        if not data:
            return "📚 Module not found. Try asking: 'Show my modules' to see your enrolled courses."
        
        return f"""📚 **{data['module_name']}**

👨‍🏫 Professor: {data['professor']}
📊 Sessions: {data['total_sessions']} total
✗ Absences: {data['absences']}
✓ Attendance Rate: {round((data['total_sessions'] - data['absences']) / data['total_sessions'] * 100, 1)}%"""
    
    elif action == Action.PROFESSORS_LIST:
        professors = get_professors_list(student_id)
        if not professors:
            return "👨‍🏫 No professors found."
        
        result = f"👨‍🏫 **Your Professors** ({len(professors)} total)\n\n"
        for prof_name, email, modules in professors:
            result += f"• **{prof_name}**\n  📧 {email}\n  📚 Teaching: {modules}\n\n"
        return result.strip()
    
    # === GENERAL ===
    elif action == Action.CONVERSATION:
        return chat_with_ai_enhanced(message, student_id)
    
    else:  # UNCLEAR
        return "❓ I couldn't understand your request. Try asking about:\n• Absences\n• Schedule\n• Modules\n• Or just chat with me!"

# ==================== ENHANCED AI CHAT ====================
def chat_with_ai_enhanced(message: str, student_id: int) -> str:
    """Enhanced conversational AI that can answer general questions, do math, etc."""
    headers = {
        "Authorization": f"Bearer {GROQ_API_KEY}",
        "Content-Type": "application/json"
    }
    
    system_message = """You are a helpful AI assistant.

Your role:
- Answer general questions (math, facts, explanations)
- Have friendly conversations
- Help with non-student-data queries

Guidelines:
- For math: Calculate and give the answer directly
- For facts: Provide accurate information
- For greetings: Be warm but brief
- Keep responses 1-3 sentences unless more detail needed
- Don't mention student IDs or act like you don't know things

Examples:
User: "1+1" → "2"
User: "what's 5 times 3?" → "15"
User: "hello" → "Hi! How can I help you today?"
User: "what is photosynthesis?" → "Photosynthesis is the process..."
"""
    
    payload = {
        "model": GROQ_MODEL,
        "messages": [
            {"role": "system", "content": system_message},
            {"role": "user", "content": message}
        ],
        "temperature": 0.7,
        "max_tokens": 150
    }
    
    try:
        response = requests.post(GROQ_URL, json=payload, headers=headers, timeout=10)
        response.raise_for_status()
        return response.json()["choices"][0]["message"]["content"].strip()
    except Exception as e:
        logging.error(f"❌ Chat AI failed: {e}")
        return "I'm here to help! Try asking me about your absences, schedule, or modules. 😊"

# ==================== API ENDPOINTS ====================
@app.route("/chat", methods=["POST"])
def chat():
    """Main chat endpoint with hybrid AI routing"""
    data = request.json
    message = data.get("message", "").strip()
    email = data.get("email", "").strip()
    
    # Validation
    if not message:
        return jsonify({"error": "Message is required"}), 400
    
    # Get student ID (with fallback for testing)
    if email:
        student_id = get_student_id(email)
        if not student_id:
            return jsonify({"error": "Student not found"}), 404
    else:
        # Fallback for testing (remove in production)
        student_id = 1
        logging.warning("⚠️ Using hardcoded student_id for testing")
    
    logging.info(f"📨 Query from student {student_id}: '{message}'")
    
    try:
        # AI routing with confidence
        action, confidence = ai_route_with_confidence(message, student_id)
        
        # Execute action (pass message for context-aware execution)
        answer = execute_action(action, student_id, confidence, message)
        
        logging.info(f"✅ Response generated successfully")
        
        return jsonify({
            "answer": answer,
            "action": action.value,
            "confidence": round(confidence, 2),
            "model": GROQ_MODEL,
            "source": "hybrid_ai"
        })
        
    except Exception as e:
        logging.error(f"❌ Request failed: {e}")
        return jsonify({
            "error": "Processing failed",
            "details": str(e)
        }), 500

@app.route("/health", methods=["GET"])
def health_check():
    """Health check with database and API connectivity"""
    try:
        # Test database connection
        conn = get_db_connection()
        conn.close()
        db_status = "connected"
    except:
        db_status = "error"
    
    try:
        # Test Groq API
        response = requests.post(
            GROQ_URL,
            headers={
                "Authorization": f"Bearer {GROQ_API_KEY}",
                "Content-Type": "application/json"
            },
            json={
                "model": GROQ_MODEL,
                "messages": [{"role": "user", "content": "test"}],
                "max_tokens": 5
            },
            timeout=5
        )
        api_status = "connected" if response.status_code == 200 else "error"
    except:
        api_status = "unreachable"
    
    return jsonify({
        "status": "healthy",
        "model": GROQ_MODEL,
        "database": db_status,
        "groq_api": api_status,
        "architecture": "hybrid_ai_router"
    })

@app.route("/test-routing", methods=["POST"])
def test_routing():
    """Test endpoint to see routing decisions without executing"""
    data = request.json
    message = data.get("message", "").strip()
    student_id = data.get("student_id", 1)
    
    if not message:
        return jsonify({"error": "Message required"}), 400
    
    action, confidence = ai_route_with_confidence(message, student_id)
    
    return jsonify({
        "message": message,
        "action": action.value,
        "confidence": round(confidence, 2),
        "will_execute": confidence >= 0.5
    })

# ==================== RUN ====================
if __name__ == "__main__":
    print("=" * 60)
    print("🚀 HYBRID AI CHATBOT - BEST OF BOTH WORLDS")
    print("=" * 60)
    print(f"🤖 Model: {GROQ_MODEL}")
    print(f"💾 Database: MySQL ({DB_CONFIG['database']})")
    print(f"🎯 Architecture: Hybrid AI Router")
    print(f"✨ Features:")
    print("   • JSON-based AI routing (flexible)")
    print("   • Confidence scoring (robust)")
    print("   • Rich response formatting")
    print("   • Health checks & test endpoints")
    print("   • Production-ready error handling")
    print("=" * 60)
    app.run(debug=True, host='0.0.0.0', port=5000)