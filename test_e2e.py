import requests
import time

BASE_URL = "http://localhost:5000/api"

def test_api():
    print("Waiting for server to start...")
    time.sleep(5) 

    # 1. Create Group
    print("Creating Group...")
    response = requests.post(f"{BASE_URL}/groups", json={"name": "Test Group", "description": "Test Description"})
    assert response.status_code == 201
    group_id = response.json()['id']
    print(f"Group Created: {group_id}")

    # 2. Add Member
    print("Adding Member...")
    response = requests.post(f"{BASE_URL}/members", json={"name": "Test User", "phone": "1234567890", "group_id": group_id})
    assert response.status_code == 201
    member_id = response.json()['id']
    print(f"Member Added: {member_id}")

    # 3. Add Contribution
    print("Adding Contribution...")
    response = requests.post(f"{BASE_URL}/contributions", json={"amount": 1000, "description": "Initial Deposit", "group_id": group_id, "member_id": member_id})
    assert response.status_code == 201
    print("Contribution Added")

    # 4. Check Balance
    print("Checking Balance...")
    response = requests.get(f"{BASE_URL}/groups/{group_id}/balance")
    assert response.status_code == 200
    balance = response.json()['balance']
    assert balance == 1000
    print(f"Balance Verified: {balance}")

    print("ALL TESTS PASSED!")

if __name__ == "__main__":
    try:
        test_api()
    except Exception as e:
        print(f"TEST FAILED: {e}")
