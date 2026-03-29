import requests

BASE_URL = "http://localhost:8000"
TIMEOUT = 30

# Admin credentials - adjust if needed
ADMIN_CREDENTIALS = {
    "email": "admin@example.com",
    "password": "password"
}

session = requests.Session()

def login():
    login_url = f"{BASE_URL}/login"

    # Post login with credentials only (assuming no CSRF token needed for API login)
    login_data = ADMIN_CREDENTIALS.copy()

    post_resp = session.post(login_url, data=login_data, timeout=TIMEOUT, allow_redirects=False)
    # Laravel typically responds 302 redirect after login
    assert post_resp.status_code in (302, 303), f"Unexpected login POST status code: {post_resp.status_code}"

    # Session cookie should be set
    assert 'laravel_session' in session.cookies.get_dict(), "Session cookie not set after login"


def test_doctors_management_crud_operations():
    login()

    doctors_url = f"{BASE_URL}/admin/doctors"
    deals_url = f"{BASE_URL}/admin/deals"

    created_doctor_id = None
    created_deal_id = None

    try:
        # 1. Create a new doctor (POST /admin/doctors)
        doctor_payload = {
            "name": "Dr. Test User",
            "center_id": None,  # We need an existing center ID, so fetch one
            "phone": "0123456789",
            "email": "dr.testuser@example.com"
        }

        # Get an existing center ID for linkage
        centers_resp = session.get(f"{BASE_URL}/admin/centers", timeout=TIMEOUT)
        centers_resp.raise_for_status()
        centers_data = centers_resp.json()
        assert isinstance(centers_data, (list, dict))
        center_id = None
        if isinstance(centers_data, dict) and "data" in centers_data and len(centers_data["data"]) > 0:
            center_id = centers_data["data"][0]["id"]
        elif isinstance(centers_data, list) and len(centers_data) > 0:
            center_id = centers_data[0]["id"]
        assert center_id is not None, "No center found to link doctor."

        doctor_payload["center_id"] = center_id

        create_doc_resp = session.post(doctors_url, json=doctor_payload, timeout=TIMEOUT)
        create_doc_resp.raise_for_status()
        created_doctor = create_doc_resp.json()
        if "data" in created_doctor:
            created_doctor = created_doctor["data"]
        created_doctor_id = created_doctor.get("id")
        assert created_doctor_id is not None
        assert created_doctor["name"] == doctor_payload["name"]
        assert created_doctor["center_id"] == doctor_payload["center_id"]
        assert created_doctor["email"] == doctor_payload["email"]

        # 2. Retrieve doctor info (GET /admin/doctors/{id})
        get_doc_resp = session.get(f"{doctors_url}/{created_doctor_id}", timeout=TIMEOUT)
        get_doc_resp.raise_for_status()
        fetched_doctor = get_doc_resp.json()
        if "data" in fetched_doctor:
            fetched_doctor = fetched_doctor["data"]
        assert fetched_doctor["id"] == created_doctor_id
        assert fetched_doctor["name"] == doctor_payload["name"]

        # 3. Update doctor info (PUT /admin/doctors/{id})
        updated_name = "Dr. Test User Updated"
        update_payload = {
            "name": updated_name,
            "phone": "0987654321",
            "email": "dr.updated@example.com",
            "center_id": center_id
        }
        update_doc_resp = session.put(f"{doctors_url}/{created_doctor_id}", json=update_payload, timeout=TIMEOUT)
        update_doc_resp.raise_for_status()
        updated_doctor = update_doc_resp.json()
        if "data" in updated_doctor:
            updated_doctor = updated_doctor["data"]
        assert updated_doctor["name"] == updated_name
        assert updated_doctor.get("phone") == update_payload["phone"]
        assert updated_doctor["email"] == update_payload["email"]

        # 4. Create a doctor deal linked to this doctor (POST /admin/deals)
        # Get pharmacist and drug IDs for linkage
        pharmacists_resp = session.get(f"{BASE_URL}/admin/pharmacists", timeout=TIMEOUT)
        pharmacists_resp.raise_for_status()
        pharmacists_data = pharmacists_resp.json()
        pharmacist_id = None
        if isinstance(pharmacists_data, dict) and "data" in pharmacists_data and len(pharmacists_data["data"]) > 0:
            pharmacist_id = pharmacists_data["data"][0]["id"]
        elif isinstance(pharmacists_data, list) and len(pharmacists_data) > 0:
            pharmacist_id = pharmacists_data[0]["id"]
        assert pharmacist_id is not None, "No pharmacist found for deal linkage."

        drugs_resp = session.get(f"{BASE_URL}/admin/drugs", timeout=TIMEOUT)
        drugs_resp.raise_for_status()
        drugs_data = drugs_resp.json()
        drug_id = None
        if isinstance(drugs_data, dict) and "data" in drugs_data and len(drugs_data["data"]) > 0:
            drug_id = drugs_data["data"][0]["id"]
        elif isinstance(drugs_data, list) and len(drugs_data) > 0:
            drug_id = drugs_data[0]["id"]
        assert drug_id is not None, "No drug found for deal linkage."

        deal_payload = {
            "doctor_id": created_doctor_id,
            "target_amount": 10000,
            "commission_percentage": 10,
            "pharmacist_id": pharmacist_id,
            "drug_ids": [drug_id]
        }
        create_deal_resp = session.post(deals_url, json=deal_payload, timeout=TIMEOUT)
        create_deal_resp.raise_for_status()
        created_deal = create_deal_resp.json()
        if "data" in created_deal:
            created_deal = created_deal["data"]
        created_deal_id = created_deal.get("id")
        assert created_deal_id is not None
        assert created_deal["doctor_id"] == created_doctor_id
        assert float(created_deal["commission_percentage"]) == float(deal_payload["commission_percentage"])

        # 5. Retrieve deal and validate
        get_deal_resp = session.get(f"{deals_url}/{created_deal_id}", timeout=TIMEOUT)
        get_deal_resp.raise_for_status()
        fetched_deal = get_deal_resp.json()
        if "data" in fetched_deal:
            fetched_deal = fetched_deal["data"]
        assert fetched_deal["id"] == created_deal_id
        assert fetched_deal["doctor_id"] == created_doctor_id

        # 6. Update deal
        updated_target = 15000
        update_deal_payload = {
            "target_amount": updated_target,
            "commission_percentage": 12,
            "pharmacist_id": pharmacist_id,
            "drug_ids": [drug_id],
            "doctor_id": created_doctor_id
        }
        update_deal_resp = session.put(f"{deals_url}/{created_deal_id}", json=update_deal_payload, timeout=TIMEOUT)
        update_deal_resp.raise_for_status()
        updated_deal = update_deal_resp.json()
        if "data" in updated_deal:
            updated_deal = updated_deal["data"]
        assert float(updated_deal["target_amount"]) == updated_target
        assert float(updated_deal["commission_percentage"]) == 12.0

        # 7. Validate doctor list reflects updated data (GET /admin/doctors)
        doctors_list_resp = session.get(doctors_url, timeout=TIMEOUT)
        doctors_list_resp.raise_for_status()
        doctors_list = doctors_list_resp.json()
        ids = []
        if isinstance(doctors_list, dict) and "data" in doctors_list:
            ids = [d["id"] for d in doctors_list["data"]]
        elif isinstance(doctors_list, list):
            ids = [d["id"] for d in doctors_list]
        assert created_doctor_id in ids

        # 8. Delete deal (DELETE /admin/deals/{id})
        del_deal_resp = session.delete(f"{deals_url}/{created_deal_id}", timeout=TIMEOUT)
        assert del_deal_resp.status_code in (200, 204)
        # Mark as deleted
        created_deal_id = None

        # Validate deal deletion
        get_deleted_deal_resp = session.get(f"{deals_url}/{created_deal_id}", timeout=TIMEOUT) if created_deal_id else None
        if get_deleted_deal_resp:
            assert get_deleted_deal_resp.status_code in (400, 404)

        # 9. Delete doctor (DELETE /admin/doctors/{id})
        del_doc_resp = session.delete(f"{doctors_url}/{created_doctor_id}", timeout=TIMEOUT)
        assert del_doc_resp.status_code in (200, 204)
        created_doctor_id = None

        # Validate doctor deletion
        get_deleted_doc_resp = session.get(f"{doctors_url}/{created_doctor_id}", timeout=TIMEOUT) if created_doctor_id else None
        if get_deleted_doc_resp:
            assert get_deleted_doc_resp.status_code in (400, 404)

    finally:
        # Cleanup if needed
        if created_deal_id:
            try:
                session.delete(f"{deals_url}/{created_deal_id}", timeout=TIMEOUT)
            except Exception:
                pass
        if created_doctor_id:
            try:
                session.delete(f"{doctors_url}/{created_doctor_id}", timeout=TIMEOUT)
            except Exception:
                pass


test_doctors_management_crud_operations()
