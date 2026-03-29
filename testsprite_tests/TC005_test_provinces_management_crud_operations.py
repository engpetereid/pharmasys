import requests

BASE_URL = "http://localhost:8000"
TIMEOUT = 30

# Replace these with valid admin credentials for authentication
ADMIN_USERNAME = "admin"
ADMIN_PASSWORD = "admin_password"

session = requests.Session()

def login():
    login_url = f"{BASE_URL}/login"
    payload = {"email": ADMIN_USERNAME, "password": ADMIN_PASSWORD}
    # It appears the API uses session-based auth, so we POST credentials and maintain session
    response = session.post(login_url, data=payload, timeout=TIMEOUT)
    assert response.status_code == 200, f"Login failed: {response.text}"

def test_provinces_management_crud_operations():
    login()

    provinces_url = f"{BASE_URL}/admin/provinces"
    headers = {"Accept": "application/json"}

    # 1. Create province
    create_data = {"name": "TestProvince"}
    create_resp = session.post(provinces_url, json=create_data, headers=headers, timeout=TIMEOUT)
    assert create_resp.status_code == 201, f"Failed to create province: {create_resp.text}"
    province = create_resp.json()
    province_id = province.get("id")
    assert province_id is not None, "Created province has no ID"

    try:
        # 2. Read province - list all provinces and get specific one
        list_resp = session.get(provinces_url, headers=headers, timeout=TIMEOUT)
        assert list_resp.status_code == 200, f"Failed to list provinces: {list_resp.text}"
        provinces_list = list_resp.json()
        assert any(p.get("id") == province_id for p in provinces_list), "Created province not in list"

        get_resp = session.get(f"{provinces_url}/{province_id}", headers=headers, timeout=TIMEOUT)
        assert get_resp.status_code == 200, f"Failed to retrieve province by ID: {get_resp.text}"
        province_detail = get_resp.json()
        assert province_detail.get("id") == province_id, "Retrieved province ID mismatch"
        assert province_detail.get("name") == create_data["name"], "Province name mismatch"

        # 3. Update province
        update_data = {"name": "UpdatedTestProvince"}
        update_resp = session.put(f"{provinces_url}/{province_id}", json=update_data, headers=headers, timeout=TIMEOUT)
        assert update_resp.status_code == 200, f"Failed to update province: {update_resp.text}"
        updated_province = update_resp.json()
        assert updated_province.get("name") == update_data["name"], "Province update did not change name"

        # Verify update persisted
        verify_resp = session.get(f"{provinces_url}/{province_id}", headers=headers, timeout=TIMEOUT)
        assert verify_resp.status_code == 200, f"Failed to retrieve province after update: {verify_resp.text}"
        verify_data = verify_resp.json()
        assert verify_data.get("name") == update_data["name"], "Updated province name not persisted"

        # 4. Delete province
        delete_resp = session.delete(f"{provinces_url}/{province_id}", headers=headers, timeout=TIMEOUT)
        assert delete_resp.status_code in (200,204), f"Failed to delete province: {delete_resp.text}"

        # Verify deletion
        check_deleted_resp = session.get(f"{provinces_url}/{province_id}", headers=headers, timeout=TIMEOUT)
        assert check_deleted_resp.status_code == 404, "Deleted province still accessible"

    finally:
        # Cleanup: try to delete if still exists (idempotent)
        del_resp = session.delete(f"{provinces_url}/{province_id}", headers=headers, timeout=TIMEOUT)
        # Accept 200,204,404 or 403 if cascade delete disallowed
        assert del_resp.status_code in (200, 204, 404), f"Cleanup failed to delete province: {del_resp.text}"

test_provinces_management_crud_operations()
