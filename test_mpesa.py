import requests
import time

BASE_URL = "http://localhost:5000/api"

def test_mpesa():
    print("Testing M-Pesa Integration...")
    
    # 1. Initiate STK Push
    print("Initiating STK Push...")
    # Using dummy data, this will fail if credentials aren't set, but we want to see it hit the endpoint
    try:
        response = requests.post(f"{BASE_URL}/mpesa/stkpush", json={
            "phone": "254700000000",
            "amount": 1,
            "group_id": 1,
            "member_id": 1
        })
        print(f"STK Push Response Code: {response.status_code}")
        print(f"STK Push Response Body: {response.json()}")
        
        # We expect a response, even if it's an error from Safaricom (due to invalid credentials)
        # The key is that our API handled it.
        if response.status_code == 200:
            print("STK Push Endpoint Reachable: PASSED")
        else:
            print("STK Push Endpoint Reachable: FAILED")

    except Exception as e:
        print(f"STK Push Test FAILED: {e}")

if __name__ == "__main__":
    test_mpesa()
