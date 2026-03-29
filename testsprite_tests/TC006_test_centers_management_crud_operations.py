import requests
import re

BASE_URL = "http://localhost:8000"
TIMEOUT = 30

# Admin user credentials for login - replace with valid credentials if needed
ADMIN_CREDENTIALS = {
    "email": "admin@example.com",
    "password": "adminpassword"
}

session = requests.Session()

def get_csrf_token(url):
    resp = session.get(url, timeout=TIMEOUT)
    resp.raise_for_status()
    match = re.search(r'name="_token" value="([^"]+)"', resp.text)
    csrf_token = match.group(1) if match else None
    assert csrf_token is not None, f"CSRF token not found on page {url}"
    return csrf_token

def login():
    login_url = f"{BASE_URL}/login"
    # First, get the login page to retrieve CSRF token
    resp = session.get(login_url, timeout=TIMEOUT)
    resp.raise_for_status()
    # Extract CSRF token from the login page HTML
    match = re.search(r'name="_token" value="([^"]+)"', resp.text)
    csrf_token = match.group(1) if match else None
    assert csrf_token is not None, "CSRF token not found on login page"

    # Prepare payload including CSRF token
    payload = {
        "_token": csrf_token,
        **ADMIN_CREDENTIALS
    }

    # Try to login with provided credentials
    resp = session.post(login_url, data=payload, timeout=TIMEOUT)
    resp.raise_for_status()
    # Expect redirect or success. Check if login succeeded by accessing a protected page
    dashboard_url = f"{BASE_URL}/admin"
    dashboard_resp = session.get(dashboard_url, timeout=TIMEOUT)
    dashboard_resp.raise_for_status()
    assert dashboard_resp.url == dashboard_url or dashboard_resp.status_code == 200


def test_centers_management_crud_operations():
    login()

    province_id = None
    center_id = None

    # Create a province - needed to link center to a valid province
    province_create_url = f"{BASE_URL}/admin/provinces/create"
    csrf_token = get_csrf_token(province_create_url)

    province_payload = {
        "name": "TestProvinceForCenter",
        "_token": csrf_token
    }
    try:
        response = session.post(f"{BASE_URL}/admin/provinces", data=province_payload, timeout=TIMEOUT)
        assert response.status_code == 201, f"Failed to create province: {response.text}"
        province_data = response.json()
        province_id = province_data.get("id")
        assert province_id is not None, "Province ID not returned"

        # Create a center linked to the above province
        center_create_url = f"{BASE_URL}/admin/centers/create"
        csrf_token = get_csrf_token(center_create_url)
        center_payload = {
            "name": "TestCenter1",
            "province_id": province_id,
            "_token": csrf_token
        }
        response = session.post(f"{BASE_URL}/admin/centers", data=center_payload, timeout=TIMEOUT)
        assert response.status_code == 201, f"Failed to create center: {response.text}"
        center_data = response.json()
        center_id = center_data.get("id")
        assert center_id is not None, "Center ID not returned"
        assert center_data.get("name") == center_payload["name"]
        assert center_data.get("province_id") == province_id

        # Retrieve the center by ID
        response = session.get(f"{BASE_URL}/admin/centers/{center_id}", timeout=TIMEOUT)
        assert response.status_code == 200, f"Failed to get center: {response.text}"
        get_data = response.json()
        assert get_data.get("id") == center_id
        assert get_data.get("name") == center_payload["name"]
        assert get_data.get("province_id") == province_id

        # Update the center: change name and verify province linkage remains
        updated_name = "TestCenter1Updated"
        center_edit_url = f"{BASE_URL}/admin/centers/{center_id}/edit"
        csrf_token = get_csrf_token(center_edit_url)
        update_payload = {
            "name": updated_name,
            "province_id": province_id,  # Link stays the same for this test
            "_token": csrf_token
        }
        response = session.put(f"{BASE_URL}/admin/centers/{center_id}", data=update_payload, timeout=TIMEOUT)
        assert response.status_code == 200, f"Failed to update center: {response.text}"
        update_data = response.json()
        assert update_data.get("name") == updated_name
        assert update_data.get("province_id") == province_id

        # List all centers and verify presence of our updated center
        response = session.get(f"{BASE_URL}/admin/centers", timeout=TIMEOUT)
        assert response.status_code == 200, f"Failed to list centers: {response.text}"
        centers_list = response.json()
        assert any(c.get("id") == center_id and c.get("name") == updated_name for c in centers_list)

        # Test data integrity - try to create center with invalid province_id (e.g. 0 or non-existing)
        csrf_token = get_csrf_token(center_create_url)
        invalid_center_payload = {
            "name": "InvalidCenter",
            "province_id": 0,
            "_token": csrf_token
        }
        response = session.post(f"{BASE_URL}/admin/centers", data=invalid_center_payload, timeout=TIMEOUT)
        # Expect failure due to foreign key constraint or validation
        assert response.status_code in (400, 422), "Server allowed center with invalid province_id"

    finally:
        # Cleanup: Delete the center if created
        if center_id is not None:
            del_resp = session.delete(f"{BASE_URL}/admin/centers/{center_id}", timeout=TIMEOUT)
            assert del_resp.status_code in (200, 204, 202), f"Failed to delete center: {del_resp.text}"
        # Cleanup: Delete the province if created
        if province_id is not None:
            del_resp = session.delete(f"{BASE_URL}/admin/provinces/{province_id}", timeout=TIMEOUT)
            assert del_resp.status_code in (200, 204, 202), f"Failed to delete province: {del_resp.text}"


test_centers_management_crud_operations()
