import requests

BASE_URL = "http://localhost:8000"
TIMEOUT = 30
LOGIN_URL = f"{BASE_URL}/login"
WAREHOUSES_URL = f"{BASE_URL}/admin/warehouses"


def login(session: requests.Session, username: str, password: str):
    # Perform login to establish session for authenticated requests
    resp = session.post(
        LOGIN_URL,
        data={"email": username, "password": password},
        timeout=TIMEOUT,
        allow_redirects=False,
    )
    # Expect 302 redirect on successful login, or 200 with error message
    assert resp.status_code in (200, 302), "Login request failed"
    if resp.status_code == 200:
        # Perhaps login failed: check for known error indication in content
        assert "Invalid" not in resp.text, "Login failed due to invalid credentials"


def test_warehouses_stock_management():
    session = requests.Session()

    # Use default admin credentials for testing
    admin_email = "admin@example.com"
    admin_password = "password"

    # Login to get authenticated session
    login(session, admin_email, admin_password)

    # ---- Create Warehouse ----
    warehouse_payload = {
        "name": "Test Warehouse TC009",
        "description": "Warehouse for test warehouses stock management",
        "type": "main"  # Assume type accepted, if required; else adapt accordingly
    }
    create_resp = session.post(
        WAREHOUSES_URL,
        json=warehouse_payload,
        timeout=TIMEOUT,
    )
    assert create_resp.status_code == 201, f"Warehouse creation failed: {create_resp.text}"
    warehouse = create_resp.json()
    warehouse_id = warehouse.get("id")
    assert warehouse_id is not None, "Created warehouse ID missing"

    try:
        # ---- Read Warehouse ----
        get_resp = session.get(f"{WAREHOUSES_URL}/{warehouse_id}", timeout=TIMEOUT)
        assert get_resp.status_code == 200, f"Failed getting warehouse: {get_resp.text}"
        warehouse_data = get_resp.json()
        assert warehouse_data["name"] == warehouse_payload["name"]

        # ---- Update Warehouse ----
        update_payload = {
            "name": "Test Warehouse TC009 Updated",
            "description": "Updated warehouse description"
        }
        update_resp = session.put(
            f"{WAREHOUSES_URL}/{warehouse_id}",
            json=update_payload,
            timeout=TIMEOUT,
        )
        assert update_resp.status_code == 200, f"Warehouse update failed: {update_resp.text}"
        updated_data = update_resp.json()
        assert updated_data["name"] == update_payload["name"]

        # ---- Add Stock to Warehouse ----
        # First GET the add stock page/data (might be metadata or validation info)
        stock_add_get_resp = session.get(
            f"{WAREHOUSES_URL}/{warehouse_id}/stock/add", timeout=TIMEOUT
        )
        assert stock_add_get_resp.status_code == 200, "Failed to get stock add info"

        # Add stock POST
        stock_add_payload = {
            "drug_id": 1,  # Assuming a drug with ID=1 exists for testing; adapt if needed
            "quantity": 10,
            "notes": "Adding 10 items to warehouse stock"
        }
        stock_add_post_resp = session.post(
            f"{WAREHOUSES_URL}/{warehouse_id}/stock/add",
            json=stock_add_payload,
            timeout=TIMEOUT,
        )
        assert stock_add_post_resp.status_code == 200, f"Stock addition failed: {stock_add_post_resp.text}"
        stock_add_result = stock_add_post_resp.json()
        # Expect some confirmation or stock record data
        assert "stock_id" in stock_add_result or "id" in stock_add_result

        # ---- Return Stock from Warehouse ----
        # GET stock return info
        stock_return_get_resp = session.get(
            f"{WAREHOUSES_URL}/{warehouse_id}/stock/return", timeout=TIMEOUT
        )
        assert stock_return_get_resp.status_code == 200, "Failed to get stock return info"

        # Return stock POST
        stock_return_payload = {
            "drug_id": 1,  # Same drug_id assumed for return
            "quantity": 5,
            "notes": "Returning 5 items from warehouse stock"
        }
        stock_return_post_resp = session.post(
            f"{WAREHOUSES_URL}/{warehouse_id}/stock/return",
            json=stock_return_payload,
            timeout=TIMEOUT,
        )
        assert stock_return_post_resp.status_code == 200, f"Stock return failed: {stock_return_post_resp.text}"
        stock_return_result = stock_return_post_resp.json()
        assert "return_id" in stock_return_result or "id" in stock_return_result

        # ---- Delete Warehouse ----
        # Delete warehouse to clean up
    finally:
        delete_resp = session.delete(
            f"{WAREHOUSES_URL}/{warehouse_id}",
            timeout=TIMEOUT,
        )
        # Deletion might return 200 or 204 on success
        assert delete_resp.status_code in (200, 204), f"Warehouse deletion failed: {delete_resp.text}"


test_warehouses_stock_management()