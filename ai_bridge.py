import g4f
import sys
import json
import re
import os

os.environ['G4F_VERSION_CHECK'] = 'false' 

def process_question():
    if len(sys.argv) < 2: return
    raw_input = sys.argv[1]

    # Enhanced prompt to handle your specific 4-column database schema
    prompt = f"""
    Act as a Study Verse Exam Parser. 
    Task: Convert the following raw question into a clean JSON object for a 4-option database.
    
    CRITICAL RULES:
    1. Clean the question text.
    2. Identify the correct answer from the "Answer:" line.
    3. If the input has more than 4 options (e.g., A-E), you MUST pick the correct answer and only THREE other distractors to make exactly 4 options total.
    4. Randomize the position of the correct answer among the 4 options (Index 0-3).
    5. Return ONLY a valid JSON object. No extra text.
    
    JSON FORMAT: {{"question": "...", "options": ["...", "...", "...", "..."], "correct_index": 0-3}}

    RAW TEXT:
    {raw_input}
    """

    try:
        from g4f.client import Client
        client = Client()
        response = client.chat.completions.create(
            model="gpt-4o",
            messages=[{"role": "user", "content": prompt}]
        )
        content = response.choices[0].message.content
        
        # Robust JSON extraction
        match = re.search(r'\{.*\}', content, re.DOTALL)
        if match:
            print(match.group())
        else:
            print(json.dumps({"error": "AI failed to return JSON format"}))
            
    except Exception as e:
        print(json.dumps({"error": str(e)}))

if __name__ == "__main__":
    process_question()