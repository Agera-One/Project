from google import generativeai
import os
import requests
from dotenv import load_dotenv
load_dotenv()

api_key = os.getenv("API_KEY")
generativeai.configure(api_key=api_key)

model = generativeai.GenerativeModel("gemini-2.0-flash")
chat = model.start_chat()

def chat_inference(prompt):
    response = chat.send_message(prompt)
    return response.text