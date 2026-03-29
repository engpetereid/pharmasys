import requests
import random
import string
import re

BASE_URL = "http://localhost:8000"
TIMEOUT = 30

# Assuming session-based auth, establish a session and login as admin before tests
session = requests.Session()
def login():
    # Get CSRF token from login page
    url = f"{BASE_URL}/login"
    resp = session.get(url, timeout=TIMEOUT)
    resp.raise_for_status()

    csrf_token = None
    # Parse CSRF token from cookies
    if 'XSRF-TOKEN' in session.cookies:
        csrf_token = session.cookies['XSRF-TOKEN']
    else:
        # parse token from HTML input named _token
        match = re.search(r'name="_token" value="([^"]+)"', resp.text)
        if match:
            csrf_token = match.group(1)

    if not csrf_token:
        raise AssertionError("CSRF token not found in cookies or HTML")

    login_url = f"{BASE_URL}/login"
    headers = {"Content-Type": "application/x-www-form-urlencoded"}
    data = {
        "email": "admin@example.com", 
        "password": "adminpassword", 
        "_token": csrf_token
    }
    resp = session.post(login_url, data=data, headers=headers, timeout=TIMEOUT)
    resp.raise_for_status()
    assert resp.status_code == 200

login()

def create_center():
    url = f"{BASE_URL}/admin/centers"
    # create center with a random unique name
    center_name = "Center-" + "".join(random.choices(string.ascii_letters + string.digits, k=6))
    payload = {"name": center_name, "address": "123 Test St", "province_id": 1}  # province_id=1 assumed valid
    resp = session.post(url, json=payload, timeout=TIMEOUT)
    resp.raise_for_status()
    assert resp.status_code == 201 or resp.status_code == 200
    center = resp.json()
    assert "id" in center
    return center["id"]

def delete_center(center_id):
    url = f"{BASE_URL}/admin/centers/{center_id}"
    resp = session.delete(url, timeout=TIMEOUT)
    # Deletion might return 200 or 204
    assert resp.status_code in [200, 204]

def create_pharmacist(center_id):
    url = f"{BASE_URL}/admin/pharmacists"
    pharmacist_name = "Pharma-" + "".join(random.choices(string.ascii_letters + string.digits, k=6))
    payload = {
        "name": pharmacist_name,
        "center_id": center_id,
        "email": f"{pharmacist_name.lower()}@example.com",
        "phone": "0123456789"
    }
    resp = session.post(url, json=payload, timeout=TIMEOUT)
    resp.raise_for_status()
    assert resp.status_code == 201 or resp.status_code == 200
    pharmacist = resp.json()
    assert "id" in pharmacist
    return pharmacist["id"], payload

def get_pharmacist(pharmacist_id):
    url = f"{BASE_URL}/admin/pharmacists/{pharmacist_id}"
    resp = session.get(url, timeout=TIMEOUT)
    resp.raise_for_status()
    assert resp.status_code == 200
    pharmacist = resp.json()
    assert "id" in pharmacist and pharmacist["id"] == pharmacist_id
    return pharmacist

def update_pharmacist(pharmacist_id, update_data):
    url = f"{BASE_URL}/admin/pharmacists/{pharmacist_id}"
    resp = session.put(url, json=update_data, timeout=TIMEOUT)
    resp.raise_for_status()
    assert resp.status_code == 200
    updated_pharmacist = resp.json()
    return updated_pharmacist

def delete_pharmacist(pharmacist_id):
    url = f"{BASE_URL}/admin/pharmacists/{pharmacist_id}"
    resp = session.delete(url, timeout=TIMEOUT)
    assert resp.status_code in [200, 204]

def test_pharmacists_management_endpoints():
    center_id = create_center()
    pharmacist_id = None
    try:
        # Create pharmacist linked to center
        pharmacist_id, pharmacist_payload = create_pharmacist(center_id)

        # Retrieve pharmacist and validate
        pharmacist = get_pharmacist(pharmacist_id)
        assert pharmacist["name"] == pharmacist_payload["name"]
        assert pharmacist["center_id"] == center_id
        assert pharmacist["email"] == pharmacist_payload["email"]
        assert pharmacist["phone"] == pharmacist_payload["phone"]

        # Update pharmacist
        update_data = {
            "name": pharmacist_payload["name"] + "-Updated",
            "email": "updated_" + pharmacist_payload["email"],
            "phone": "0987654321"
        }
        updated_pharmacist = update_pharmacist(pharmacist_id, update_data)
        assert updated_pharmacist["name"] == update_data["name"]
        assert updated_pharmacist["email"] == update_data["email"]
        assert updated_pharmacist["phone"] == update_data["phone"]
        assert updated_pharmacist["center_id"] == center_id  # center_id should remain the same

        # Delete pharmacist
        delete_pharmacist(pharmacist_id)

        # Confirm deletion by attempting to get pharmacist, expect 404
        url = f"{BASE_URL}/admin/pharmacists/{pharmacist_id}"
        resp = session.get(url, timeout=TIMEOUT)
        assert resp.status_code == 404

    finally:
        if pharmacist_id:
            # Clean up in case delete failed silently
            try:
                delete_pharmacist(pharmacist_id)
            except:
                pass
        delete_center(center_id)

test_pharmacists_management_endpoints()
