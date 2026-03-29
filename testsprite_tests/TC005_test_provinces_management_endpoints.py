import requests
from bs4 import BeautifulSoup

BASE_URL = "http://localhost:8000"
TIMEOUT = 30

# Assuming an admin user exists with these credentials for authentication
ADMIN_CREDENTIALS = {
    "email": "admin@example.com",
    "password": "adminpassword"
}

session = requests.Session()


def login():
    login_url = f"{BASE_URL}/login"
    # First get login page to get CSRF token from the page content
    get_resp = session.get(login_url, timeout=TIMEOUT)
    assert get_resp.status_code == 200, f"GET login page failed with status {get_resp.status_code}"

    # Parse CSRF token from hidden input _token in form
    soup = BeautifulSoup(get_resp.text, 'html.parser')
    token_input = soup.find('input', {'name': '_token'})
    assert token_input is not None, "CSRF token input (_token) not found in login page"
    csrf_token = token_input.get('value')
    assert csrf_token, "CSRF token (_token) value is empty"

    headers = {
        'Referer': login_url,
        'X-CSRF-TOKEN': csrf_token,
        'Content-Type': 'application/x-www-form-urlencoded'
    }

    payload = {
        'email': ADMIN_CREDENTIALS['email'],
        'password': ADMIN_CREDENTIALS['password'],
        '_token': csrf_token
    }

    resp = session.post(login_url, data=payload, headers=headers, timeout=TIMEOUT, allow_redirects=False)
    # Laravel returns 302 redirect on successful login
    assert resp.status_code == 302, f"Login failed with status {resp.status_code}"


def logout():
    logout_url = f"{BASE_URL}/logout"
    # Get CSRF token from cookie
    csrf_token = session.cookies.get('XSRF-TOKEN')
    assert csrf_token is not None, "CSRF token cookie missing for logout"

    headers = {
        'Referer': BASE_URL,
        'X-XSRF-TOKEN': csrf_token
    }

    resp = session.post(logout_url, headers=headers, timeout=TIMEOUT, allow_redirects=False)
    # Logout also returns 302 redirect
    assert resp.status_code == 302, f"Logout failed with status {resp.status_code}"


def test_provinces_management_endpoints():
    login()
    provinces_url = f"{BASE_URL}/admin/provinces"
    headers = {"Accept": "application/json"}

    # Create Province (POST /admin/provinces)
    province_data = {"name": "Test Province"}

    # Get CSRF token before POST
    csrf_token = session.cookies.get('XSRF-TOKEN')
    assert csrf_token is not None, "CSRF token cookie missing before province creation"

    headers.update({
        'X-XSRF-TOKEN': csrf_token,
        'Content-Type': 'application/json'
    })

    create_resp = session.post(provinces_url, json=province_data, headers=headers, timeout=TIMEOUT)
    assert create_resp.status_code == 201, f"Province creation failed: {create_resp.text}"
    created_province = create_resp.json()
    province_id = created_province.get("id")
    assert province_id is not None, "Created province has no ID"

    try:
        # Retrieve Province (GET /admin/provinces/{id})
        get_url = f"{provinces_url}/{province_id}"
        get_resp = session.get(get_url, headers=headers, timeout=TIMEOUT)
        assert get_resp.status_code == 200, f"Get province failed: {get_resp.text}"
        province = get_resp.json()
        assert province.get("name") == province_data["name"], "Province name mismatch on retrieval"

        # Update Province (PUT /admin/provinces/{id})
        update_data = {"name": "Updated Test Province"}

        # Update CSRF token header
        csrf_token = session.cookies.get('XSRF-TOKEN')
        assert csrf_token is not None, "CSRF token cookie missing before province update"

        headers.update({
            'X-XSRF-TOKEN': csrf_token,
            'Content-Type': 'application/json'
        })

        update_resp = session.put(get_url, json=update_data, headers=headers, timeout=TIMEOUT)
        assert update_resp.status_code == 200, f"Update province failed: {update_resp.text}"
        updated_province = update_resp.json()
        assert updated_province.get("name") == update_data["name"], "Province name mismatch after update"

        # List Provinces (GET /admin/provinces)
        list_resp = session.get(provinces_url, headers={"Accept": "application/json"}, timeout=TIMEOUT)
        assert list_resp.status_code == 200, f"List provinces failed: {list_resp.text}"
        provinces_list = list_resp.json()
        assert any(p.get("id") == province_id for p in provinces_list), "Created province not in list"

    finally:
        # Delete Province (DELETE /admin/provinces/{id})
        csrf_token = session.cookies.get('XSRF-TOKEN')
        assert csrf_token is not None, "CSRF token cookie missing before province deletion"

        headers.update({
            'X-XSRF-TOKEN': csrf_token
        })

        delete_resp = session.delete(f"{provinces_url}/{province_id}", headers=headers, timeout=TIMEOUT)
        assert delete_resp.status_code == 200 or delete_resp.status_code == 204, f"Delete province failed: {delete_resp.text}"

    logout()


test_provinces_management_endpoints()
