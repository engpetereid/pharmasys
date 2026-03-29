import requests

BASE_URL = "http://localhost:8000"
TIMEOUT = 30
# Test admin credentials (assumed to exist)
ADMIN_EMAIL = "admin@example.com"
ADMIN_PASSWORD = "adminpassword"

def login_session():
    session = requests.Session()
    login_url = f"{BASE_URL}/login"
    login_data = {
        "email": ADMIN_EMAIL,
        "password": ADMIN_PASSWORD
    }
    resp = session.post(login_url, data=login_data, timeout=TIMEOUT)
    assert resp.status_code == 200, "Login failed"
    # Confirm login success by checking response or cookies
    return session

def test_centers_management_endpoints():
    session = login_session()

    # Step 1: Create a province first (needed for center creation)
    province_data = {
        "name": "TestProvince_TC006"
    }
    province = None
    center = None
    updated_center = None
    try:
        province_resp = session.post(f"{BASE_URL}/admin/provinces", json=province_data, timeout=TIMEOUT)
        assert province_resp.status_code == 201, f"Failed to create province: {province_resp.text}"
        province = province_resp.json()
        province_id = province.get("id")
        assert province_id is not None, "Province ID not returned"

        # Step 2: Create a center within this province
        center_data = {
            "name": "TestCenter_TC006",
            "province_id": province_id
        }
        center_resp = session.post(f"{BASE_URL}/admin/centers", json=center_data, timeout=TIMEOUT)
        assert center_resp.status_code == 201, f"Failed to create center: {center_resp.text}"
        center = center_resp.json()
        center_id = center.get("id")
        assert center_id is not None, "Center ID not returned"

        # Step 3: Retrieve the center details
        get_resp = session.get(f"{BASE_URL}/admin/centers/{center_id}", timeout=TIMEOUT)
        assert get_resp.status_code == 200, f"Failed to get center details: {get_resp.text}"
        get_data = get_resp.json()
        assert get_data.get("id") == center_id, "Retrieved center ID mismatch"
        assert get_data.get("name") == center_data["name"], "Retrieved center name mismatch"
        assert get_data.get("province_id") == province_id, "Retrieved province_id mismatch"

        # Step 4: Update the center details
        update_data = {
            "name": "UpdatedTestCenter_TC006",
            "province_id": province_id
        }
        update_resp = session.put(f"{BASE_URL}/admin/centers/{center_id}", json=update_data, timeout=TIMEOUT)
        assert update_resp.status_code == 200, f"Failed to update center: {update_resp.text}"
        updated_center = update_resp.json()
        assert updated_center.get("name") == update_data["name"], "Center name was not updated correctly"
        assert updated_center.get("province_id") == province_id, "Center province_id changed unexpectedly"

        # Step 5: Delete the center
        del_resp = session.delete(f"{BASE_URL}/admin/centers/{center_id}", timeout=TIMEOUT)
        assert del_resp.status_code == 204, f"Failed to delete center: {del_resp.text}"

        # Step 6: Confirm deletion by attempting to get the center
        get_after_del_resp = session.get(f"{BASE_URL}/admin/centers/{center_id}", timeout=TIMEOUT)
        assert get_after_del_resp.status_code == 404, "Center still exists after deletion"

        # Cleanup province - delete it after center deletion
        del_province_resp = session.delete(f"{BASE_URL}/admin/provinces/{province_id}", timeout=TIMEOUT)
        assert del_province_resp.status_code == 204, f"Failed to delete province: {del_province_resp.text}"

    except Exception as e:
        # If exception occurs, try cleaning up created resources
        if center and center.get("id"):
            session.delete(f"{BASE_URL}/admin/centers/{center.get('id')}", timeout=TIMEOUT)
        if province and province.get("id"):
            session.delete(f"{BASE_URL}/admin/provinces/{province.get('id')}", timeout=TIMEOUT)
        raise e


test_centers_management_endpoints()
