import requests

BASE_URL = "http://localhost:8000"
TIMEOUT = 30
SESSION = requests.Session()

def login_admin():
    login_url = f"{BASE_URL}/login"
    # First get the login page to obtain CSRF token
    get_resp = SESSION.get(login_url, timeout=TIMEOUT)
    get_resp.raise_for_status()

    import re
    # Extract CSRF token from HTML page
    match = re.search(r'name="_token" value="([^"]+)"', get_resp.text)
    assert match, "CSRF token not found on login page"
    csrf_token = match.group(1)

    credentials = {
        "email": "admin@example.com",
        "password": "password",
        "_token": csrf_token
    }
    # Laravel expects form-encoded data, not JSON
    resp = SESSION.post(login_url, data=credentials, timeout=TIMEOUT)
    # Laravel may redirect on success
    assert resp.status_code in (200, 302), f"Login failed with status {resp.status_code}"

def test_representatives_management_endpoints():
    login_admin()

    headers = {"Accept": "application/json"}
    created_rep_id = None
    try:
        # Create a new representative
        rep_data = {
            "name": "Test Rep",
            "type": "sales",
            "phone": "1234567890",
            "email": "testrep@example.com"
        }
        response = SESSION.post(f"{BASE_URL}/admin/representatives", data=rep_data, headers=headers, timeout=TIMEOUT)
        assert response.status_code == 201
        created_rep = response.json()
        assert "id" in created_rep
        created_rep_id = created_rep["id"]
        assert created_rep["name"] == rep_data["name"]
        assert created_rep["type"] == rep_data["type"]
        assert created_rep["phone"] == rep_data["phone"]
        assert created_rep["email"] == rep_data["email"]

        # Retrieve the representative by ID
        get_resp = SESSION.get(f"{BASE_URL}/admin/representatives/{created_rep_id}", headers=headers, timeout=TIMEOUT)
        assert get_resp.status_code == 200
        rep_retrieved = get_resp.json()
        assert rep_retrieved["id"] == created_rep_id
        assert rep_retrieved["name"] == rep_data["name"]

        # Update the representative
        updated_data = {
            "name": "Test Rep Updated",
            "phone": "0987654321",
            "email": "testupdated@example.com",
            "type": "medical"
        }
        put_resp = SESSION.put(f"{BASE_URL}/admin/representatives/{created_rep_id}", data=updated_data, headers=headers, timeout=TIMEOUT)
        assert put_resp.status_code == 200
        rep_updated = put_resp.json()
        assert rep_updated["name"] == updated_data["name"]
        assert rep_updated["phone"] == updated_data["phone"]
        assert rep_updated["email"] == updated_data["email"]
        assert rep_updated["type"] == updated_data["type"]

        # List all representatives includes the updated one
        list_resp = SESSION.get(f"{BASE_URL}/admin/representatives", headers=headers, timeout=TIMEOUT)
        assert list_resp.status_code == 200
        reps_list = list_resp.json()
        assert any(r["id"] == created_rep_id for r in reps_list)

    finally:
        # Delete the created representative if exists
        if created_rep_id:
            del_resp = SESSION.delete(f"{BASE_URL}/admin/representatives/{created_rep_id}", headers=headers, timeout=TIMEOUT)
            assert del_resp.status_code in (200, 204)

test_representatives_management_endpoints()
