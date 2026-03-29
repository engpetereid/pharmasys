import requests
from bs4 import BeautifulSoup

BASE_URL = "http://localhost:8000"
TIMEOUT = 30

session = requests.Session()

def login_as_admin():
    login_page_url = BASE_URL + "/login"
    # Get login page to retrieve CSRF token
    resp = session.get(login_page_url, timeout=TIMEOUT)
    resp.raise_for_status()
    soup = BeautifulSoup(resp.text, 'html.parser')
    token = soup.find('input', {'name': '_token'})
    csrf_token = token['value'] if token else ''

    login_url = BASE_URL + "/login"
    credentials = {
        "email": "admin@example.com",
        "password": "password",
        "_token": csrf_token
    }
    headers = {"Content-Type": "application/x-www-form-urlencoded"}
    resp = session.post(login_url, data=credentials, headers=headers, timeout=TIMEOUT)
    resp.raise_for_status()
    assert resp.status_code == 200 or resp.status_code == 302

def test_doctors_management_endpoints():
    login_as_admin()

    # Helper: Create center as doctors must be linked to centers
    def create_province():
        province_data = {"name": "Test Province"}
        resp = session.post(f"{BASE_URL}/admin/provinces", json=province_data, timeout=TIMEOUT)
        resp.raise_for_status()
        assert resp.status_code == 201
        province_id = resp.json().get("id")
        return province_id

    center_payload = {
        "name": "Test Center for Doctor",
        "province_id": create_province()
    }

    center = None
    doctor = None
    updated_doctor = None
    deal = None

    def cleanup_center(center_id):
        if center_id:
            session.delete(f"{BASE_URL}/admin/centers/{center_id}", timeout=TIMEOUT)

    def cleanup_doctor(doctor_id):
        if doctor_id:
            session.delete(f"{BASE_URL}/admin/doctors/{doctor_id}", timeout=TIMEOUT)

    def cleanup_deal(deal_id):
        if deal_id:
            session.delete(f"{BASE_URL}/admin/deals/{deal_id}", timeout=TIMEOUT)

    try:
        # Create Center
        resp = session.post(f"{BASE_URL}/admin/centers", json=center_payload, timeout=TIMEOUT)
        resp.raise_for_status()
        assert resp.status_code == 201
        center = resp.json()
        center_id = center.get("id")
        assert center_id is not None

        # Create Doctor linked to Center with deals and commissions
        doctor_payload = {
            "name": "Dr. John Test",
            "center_id": center_id,
            "commission_percentage": 10.5,
            "deals": []
        }
        resp = session.post(f"{BASE_URL}/admin/doctors", json=doctor_payload, timeout=TIMEOUT)
        resp.raise_for_status()
        assert resp.status_code == 201
        doctor = resp.json()
        doctor_id = doctor.get("id")
        assert doctor_id is not None
        assert doctor.get("name") == doctor_payload["name"]
        assert doctor.get("center_id") == center_id
        assert float(doctor.get("commission_percentage", 0)) == 10.5

        # Retrieve Doctor
        resp = session.get(f"{BASE_URL}/admin/doctors/{doctor_id}", timeout=TIMEOUT)
        resp.raise_for_status()
        assert resp.status_code == 200
        retrieved_doctor = resp.json()
        assert retrieved_doctor.get("id") == doctor_id

        # Update Doctor (change name and commission_percentage)
        update_payload = {
            "name": "Dr. John Updated",
            "commission_percentage": 15.0
        }
        resp = session.put(f"{BASE_URL}/admin/doctors/{doctor_id}", json=update_payload, timeout=TIMEOUT)
        resp.raise_for_status()
        assert resp.status_code == 200
        updated_doctor = resp.json()
        assert updated_doctor.get("name") == update_payload["name"]
        assert float(updated_doctor.get("commission_percentage", 0)) == 15.0

        # Create a deal linked to the doctor
        deal_payload = {
            "doctor_id": doctor_id,
            "target_amount": 5000,
            "commission_percentage": 12.5,
            "linked_pharmacist_ids": [],
            "linked_drug_ids": []
        }
        resp = session.post(f"{BASE_URL}/admin/deals", json=deal_payload, timeout=TIMEOUT)
        resp.raise_for_status()
        assert resp.status_code == 201
        deal = resp.json()
        deal_id = deal.get("id")
        assert deal_id is not None
        assert deal.get("doctor_id") == doctor_id
        assert float(deal.get("commission_percentage", 0)) == 12.5

        # Retrieve deal
        resp = session.get(f"{BASE_URL}/admin/deals/{deal_id}", timeout=TIMEOUT)
        resp.raise_for_status()
        assert resp.status_code == 200
        deal_retrieved = resp.json()
        assert deal_retrieved.get("id") == deal_id

        # Update deal
        update_deal_payload = {
            "target_amount": 6000,
            "commission_percentage": 13.0
        }
        resp = session.put(f"{BASE_URL}/admin/deals/{deal_id}", json=update_deal_payload, timeout=TIMEOUT)
        resp.raise_for_status()
        assert resp.status_code == 200
        deal_updated = resp.json()
        assert int(deal_updated.get("target_amount", 0)) == 6000
        assert float(deal_updated.get("commission_percentage", 0)) == 13.0

    finally:
        # Clean up deal
        if 'deal' in locals() and deal:
            try:
                cleanup_deal(deal.get("id"))
            except Exception:
                pass

        # Clean up doctor
        if 'doctor' in locals() and doctor:
            try:
                cleanup_doctor(doctor.get("id"))
            except Exception:
                pass

        # Clean up center
        if 'center' in locals() and center:
            try:
                cleanup_center(center.get("id"))
            except Exception:
                pass

test_doctors_management_endpoints()
