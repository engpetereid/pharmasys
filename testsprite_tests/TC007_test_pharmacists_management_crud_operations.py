import requests
from bs4 import BeautifulSoup

BASE_URL = "http://localhost:8000"
TIMEOUT = 30

# Assuming admin login credentials are known for session-based auth
ADMIN_CREDENTIALS = {
    "email": "admin@example.com",
    "password": "adminpassword"
}

session = requests.Session()

def get_csrf_token(url):
    resp = session.get(url, timeout=TIMEOUT)
    assert resp.status_code == 200, f"Failed to get page to extract CSRF token: {resp.status_code}"
    soup = BeautifulSoup(resp.text, "html.parser")
    token_tag = soup.find('input', {'name': '_token'})
    assert token_tag and token_tag.get('value'), "CSRF token not found on page"
    return token_tag['value']

def login():
    login_url = BASE_URL + "/login"
    csrf_token = get_csrf_token(login_url)
    login_data = {
        "email": ADMIN_CREDENTIALS["email"],
        "password": ADMIN_CREDENTIALS["password"],
        "_token": csrf_token
    }
    resp = session.post(login_url, data=login_data, timeout=TIMEOUT)
    assert resp.status_code == 200, f"Login failed: {resp.status_code} {resp.text}"

def logout():
    logout_url = BASE_URL + "/logout"
    csrf_token = get_csrf_token(logout_url)  # Generally /logout is POST and requires CSRF token
    logout_data = {
        "_token": csrf_token
    }
    resp = session.post(logout_url, data=logout_data, timeout=TIMEOUT)
    assert resp.status_code == 200, f"Logout failed: {resp.status_code} {resp.text}"

def create_center():
    url = BASE_URL + "/admin/centers"
    # Minimal center data required - guessing name and province_id
    # Need a valid province_id for linking center, so create or get one
    province_id = None
    # Try to get existing province or create one if none available
    provinces_resp = session.get(BASE_URL + "/admin/provinces", timeout=TIMEOUT)
    assert provinces_resp.status_code == 200, "Failed to get provinces"
    provinces = provinces_resp.json()
    if provinces and isinstance(provinces, list) and len(provinces) > 0:
        province_id = provinces[0]["id"]
    else:
        # Create province
        province_data = {"name": "TestProvince"}
        # Need CSRF token for POST
        csrf_token = get_csrf_token(BASE_URL + "/admin/provinces/create") if BASE_URL + "/admin/provinces/create" else get_csrf_token(BASE_URL + "/admin/provinces")
        province_data["_token"] = csrf_token
        create_prov_resp = session.post(BASE_URL + "/admin/provinces", data=province_data, timeout=TIMEOUT)
        assert create_prov_resp.status_code == 201, "Failed to create province"
        province_id = create_prov_resp.json()["id"]

    center_data = {
        "name": "Test Center",
        "address": "123 Test St",
        "province_id": province_id
    }
    # Get CSRF token for creating center
    csrf_token_center = get_csrf_token(BASE_URL + "/admin/centers/create") if BASE_URL + "/admin/centers/create" else get_csrf_token(BASE_URL + "/admin/centers")
    center_data["_token"] = csrf_token_center

    resp = session.post(url, data=center_data, timeout=TIMEOUT)
    assert resp.status_code == 201, f"Failed to create center: {resp.text}"
    return resp.json()

def delete_center(center_id):
    url = BASE_URL + f"/admin/centers/{center_id}"
    csrf_token = get_csrf_token(BASE_URL + f"/admin/centers/{center_id}")
    resp = session.delete(url, data={"_token": csrf_token}, timeout=TIMEOUT)
    assert resp.status_code in (200, 204), f"Failed to delete center: {resp.status_code} {resp.text}"

def create_pharmacist(center_id):
    url = BASE_URL + "/admin/pharmacists"
    pharmacist_data = {
        "name": "John Doe",
        "phone": "1234567890",
        "email": "johndoe@example.com",
        "center_id": center_id
    }
    csrf_token = get_csrf_token(BASE_URL + "/admin/pharmacists/create") if BASE_URL + "/admin/pharmacists/create" else get_csrf_token(BASE_URL + "/admin/pharmacists")
    pharmacist_data["_token"] = csrf_token

    resp = session.post(url, data=pharmacist_data, timeout=TIMEOUT)
    assert resp.status_code == 201, f"Failed to create pharmacist: {resp.text}"
    return resp.json()

def get_pharmacist(pharmacist_id):
    url = BASE_URL + f"/admin/pharmacists/{pharmacist_id}"
    resp = session.get(url, timeout=TIMEOUT)
    assert resp.status_code == 200, f"Failed to get pharmacist: {resp.text}"
    return resp.json()

def update_pharmacist(pharmacist_id):
    url = BASE_URL + f"/admin/pharmacists/{pharmacist_id}"
    update_data = {
        "name": "John Smith",
        "phone": "0987654321",
        "email": "johnsmith@example.com"
    }
    csrf_token = get_csrf_token(url + "/edit") if url + "/edit" else get_csrf_token(url)
    update_data["_token"] = csrf_token

    resp = session.put(url, data=update_data, timeout=TIMEOUT)
    assert resp.status_code == 200, f"Failed to update pharmacist: {resp.text}"
    updated_pharmacist = resp.json()
    assert updated_pharmacist["name"] == update_data["name"], "Pharmacist name not updated"
    assert updated_pharmacist["phone"] == update_data["phone"], "Pharmacist phone not updated"
    assert updated_pharmacist["email"] == update_data["email"], "Pharmacist email not updated"
    return updated_pharmacist

def delete_pharmacist(pharmacist_id):
    url = BASE_URL + f"/admin/pharmacists/{pharmacist_id}"
    csrf_token = get_csrf_token(url)
    resp = session.delete(url, data={"_token": csrf_token}, timeout=TIMEOUT)
    assert resp.status_code in (200, 204), f"Failed to delete pharmacist: {resp.status_code} {resp.text}"

def test_pharmacists_management_crud_operations():
    login()
    center = None
    pharmacist = None
    try:
        center = create_center()
        center_id = center["id"]

        # Create pharmacist linked to center
        pharmacist = create_pharmacist(center_id)
        pharmacist_id = pharmacist["id"]

        # Read pharmacist
        fetched = get_pharmacist(pharmacist_id)
        assert fetched["id"] == pharmacist_id, "Fetched pharmacist id mismatch"
        assert fetched["center_id"] == center_id, "Pharmacist center_id mismatch"
        assert fetched["name"] == pharmacist["name"], "Pharmacist name mismatch"

        # Update pharmacist
        updated = update_pharmacist(pharmacist_id)

        # Try invalid data update (validation test: invalid email)
        invalid_data = {
            "name": "Invalid User",
            "phone": "invalidphone",
            "email": "notanemail"
        }
        csrf_token_invalid = get_csrf_token(BASE_URL + f"/admin/pharmacists/{pharmacist_id}/edit")
        invalid_data["_token"] = csrf_token_invalid
        url = BASE_URL + f"/admin/pharmacists/{pharmacist_id}"
        resp = session.put(url, data=invalid_data, timeout=TIMEOUT)
        # Assuming API returns 422 Unprocessable Entity or 400 Bad Request for validation errors
        assert resp.status_code in (400, 422), "Validation should prevent invalid pharmacist update"

    finally:
        # Cleanup pharmacist and center
        if pharmacist:
            try:
                delete_pharmacist(pharmacist["id"])
            except AssertionError:
                pass
        if center:
            try:
                delete_center(center["id"])
            except AssertionError:
                pass
        logout()

test_pharmacists_management_crud_operations()
