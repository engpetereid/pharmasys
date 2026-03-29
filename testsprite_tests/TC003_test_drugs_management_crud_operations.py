import requests

BASE_URL = "http://localhost:8000"
TIMEOUT = 30
SESSION = requests.Session()

def test_drugs_management_crud_operations():
    # Assuming session-based auth and a login is required, implement login first
    login_url = f"{BASE_URL}/login"
    login_payload = {
        # Provide valid admin credentials here
        "email": "admin@example.com",
        "password": "adminpassword"
    }
    login_headers = {
        "Accept": "application/json",
    }
    login_resp = SESSION.post(login_url, json=login_payload, headers=login_headers, timeout=TIMEOUT)
    assert login_resp.status_code in [200, 302], f"Login failed with status code {login_resp.status_code}"
    
    headers = {
        # CSRF token could be required; assuming no token needed or handled by session
        "Accept": "application/json",
    }
    
    drug_created_id = None

    try:
        # Create a new drug
        create_url = f"{BASE_URL}/admin/drugs"
        drug_data = {
            "name": "TestDrugAlpha",
            "price": 123.45,
            "line": 1  # valid values 1 or 2 according to PRD
        }
        create_resp = SESSION.post(create_url, json=drug_data, headers=headers, timeout=TIMEOUT)
        assert create_resp.status_code == 201 or create_resp.status_code == 200, f"Drug creation failed: {create_resp.text}"
        created_drug = create_resp.json()
        drug_created_id = created_drug.get("id")
        assert drug_created_id is not None, "Created drug ID not returned"
        assert created_drug.get("name") == drug_data["name"]
        assert float(created_drug.get("price")) == drug_data["price"]
        assert int(created_drug.get("line")) == drug_data["line"]

        # Read the created drug by ID
        read_url = f"{BASE_URL}/admin/drugs/{drug_created_id}"
        read_resp = SESSION.get(read_url, headers=headers, timeout=TIMEOUT)
        assert read_resp.status_code == 200, f"Failed to read created drug ID {drug_created_id}"
        read_drug = read_resp.json()
        assert read_drug.get("id") == drug_created_id
        assert read_drug.get("name") == drug_data["name"]
        assert float(read_drug.get("price")) == drug_data["price"]
        assert int(read_drug.get("line")) == drug_data["line"]

        # Update the drug with new values
        update_url = f"{BASE_URL}/admin/drugs/{drug_created_id}"
        updated_data = {
            "name": "TestDrugBeta",
            "price": 543.21,
            "line": 2
        }
        update_resp = SESSION.put(update_url, json=updated_data, headers=headers, timeout=TIMEOUT)
        assert update_resp.status_code == 200, f"Drug update failed: {update_resp.text}"
        updated_drug = update_resp.json()
        assert updated_drug.get("name") == updated_data["name"]
        assert float(updated_drug.get("price")) == updated_data["price"]
        assert int(updated_drug.get("line")) == updated_data["line"]

        # Read again and verify update
        read_updated_resp = SESSION.get(read_url, headers=headers, timeout=TIMEOUT)
        assert read_updated_resp.status_code == 200, f"Failed to read updated drug ID {drug_created_id}"
        read_updated_drug = read_updated_resp.json()
        assert read_updated_drug.get("name") == updated_data["name"]
        assert float(read_updated_drug.get("price")) == updated_data["price"]
        assert int(read_updated_drug.get("line")) == updated_data["line"]

        # Delete the drug
        delete_url = f"{BASE_URL}/admin/drugs/{drug_created_id}"
        delete_resp = SESSION.delete(delete_url, headers=headers, timeout=TIMEOUT)
        assert delete_resp.status_code == 200 or delete_resp.status_code == 204, f"Drug deletion failed: {delete_resp.text}"

        # Verify drug is deleted by attempting to GET it
        verify_delete_resp = SESSION.get(read_url, headers=headers, timeout=TIMEOUT)
        assert verify_delete_resp.status_code == 404 or verify_delete_resp.status_code == 410, f"Deleted drug still accessible, status: {verify_delete_resp.status_code}"

    finally:
        # Clean up in case test fails before deletion
        if drug_created_id is not None:
            cleanup_url = f"{BASE_URL}/admin/drugs/{drug_created_id}"
            try:
                cleanup_resp = SESSION.delete(cleanup_url, headers=headers, timeout=TIMEOUT)
                # No assertion here because we just want to clean up if exists
            except Exception:
                pass


test_drugs_management_crud_operations()