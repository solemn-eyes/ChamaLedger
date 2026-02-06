import os
from dotenv import load_dotenv

# Load environment variables from .env file
load_dotenv()

class MpesaClient:
    def __init__(self):
        self.consumer_key = os.getenv("MPESA_CONSUMER_KEY")
        self.consumer_secret = os.getenv("MPESA_CONSUMER_SECRET")
        self.shortcode = os.getenv("MPESA_SHORTCODE", "174379")
        self.passkey = os.getenv("MPESA_PASSKEY", "bfb279f9aa9bdbcf158e97dd71a467cd2e0c893059b10f78e6b72ada1ed2c919")
        self.env = os.getenv("MPESA_ENV", "sandbox")
        self.base_url = "https://sandbox.safaricom.co.ke" if self.env == "sandbox" else "https://api.safaricom.co.ke"

    def get_access_token(self):
        if not self.consumer_key or not self.consumer_secret:
             print("Error: Missing MPESA_CONSUMER_KEY or MPESA_CONSUMER_SECRET in .env")
             return None

        url = f"{self.base_url}/oauth/v1/generate?grant_type=client_credentials"
        auth = base64.b64encode(f"{self.consumer_key}:{self.consumer_secret}".encode()).decode()
        headers = {
            "Authorization": f"Basic {auth}"
        }
        try:
            response = requests.get(url, headers=headers)
            response.raise_for_status()
            return response.json()['access_token']
        except Exception as e:
            print(f"Error getting access token: {e}")
            if hasattr(e, 'response') and e.response is not None:
                print(f"Response Body: {e.response.text}")
            return None

    def stk_push(self, phone_number, amount, account_reference, transaction_desc):
        token = self.get_access_token()
        if not token:
            return {"error": "Failed to get access token. Check server logs for details."}

        timestamp = datetime.now().strftime('%Y%m%d%H%M%S')
        password = base64.b64encode(f"{self.shortcode}{self.passkey}{timestamp}".encode()).decode()

        # Format phone number to 254...
        if phone_number.startswith("0"):
            phone_number = "254" + phone_number[1:]
        
        payload = {
            "BusinessShortCode": self.shortcode,
            "Password": password,
            "Timestamp": timestamp,
            "TransactionType": "CustomerPayBillOnline",
            "Amount": int(amount), 
            "PartyA": phone_number,
            "PartyB": self.shortcode,
            "PhoneNumber": phone_number,
            "CallBackURL": "https://mydomain.com/api/mpesa/callback", # Needs a live URL or ngrok
            "AccountReference": account_reference,
            "TransactionDesc": transaction_desc
        }
        
        headers = {
            "Authorization": f"Bearer {token}",
            "Content-Type": "application/json"
        }
        
        url = f"{self.base_url}/mpesa/stkpush/v1/processrequest"
        
        try:
            response = requests.post(url, json=payload, headers=headers)
            try:
                return response.json()
            except json.JSONDecodeError:
                return {"error": f"Invalid JSON response: {response.text}"}
        except Exception as e:
            return {"error": str(e)}
