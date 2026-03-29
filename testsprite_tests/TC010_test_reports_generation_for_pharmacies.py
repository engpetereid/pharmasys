import requests
from requests.exceptions import RequestException

BASE_URL = "http://localhost:8000"
TIMEOUT = 30

# Credentials for login - assumed to be valid existing admin user
ADMIN_CREDENTIALS = {
    "email": "admin@example.com",
    "password": "adminpassword"
}

def test_reports_generation_for_pharmacies():
    session = requests.Session()
    try:
        # Login to obtain session cookies for authentication
        login_resp = session.post(
            f"{BASE_URL}/login",
            data=ADMIN_CREDENTIALS,
            timeout=TIMEOUT,
            allow_redirects=False
        )
        assert login_resp.status_code in (200, 302), f"Login failed with status {login_resp.status_code}"

        # 1) Create a Province
        province_data = {
            "name": "TestProvince"
        }
        province_resp = session.post(f"{BASE_URL}/admin/provinces", json=province_data, timeout=TIMEOUT)
        assert province_resp.status_code == 201, f"Failed to create province: {province_resp.text}"
        province = province_resp.json()
        province_id = province.get("id")
        assert province_id, "Province ID missing in response"

        # 2) Create a Center linked to the Province
        center_data = {
            "name": "TestCenter",
            "province_id": province_id
        }
        center_resp = session.post(f"{BASE_URL}/admin/centers", json=center_data, timeout=TIMEOUT)
        assert center_resp.status_code == 201, f"Failed to create center: {center_resp.text}"
        center = center_resp.json()
        center_id = center.get("id")
        assert center_id, "Center ID missing in response"

        # 3) Create a Pharmacist linked to the Center
        pharmacist_data = {
            "name": "Test Pharmacist",
            "center_id": center_id,
            "email": "pharmacist@example.com"
        }
        pharmacist_resp = session.post(f"{BASE_URL}/admin/pharmacists", json=pharmacist_data, timeout=TIMEOUT)
        assert pharmacist_resp.status_code == 201, f"Failed to create pharmacist: {pharmacist_resp.text}"
        pharmacist = pharmacist_resp.json()
        pharmacist_id = pharmacist.get("id")
        assert pharmacist_id, "Pharmacist ID missing in response"

        # 4) Retrieve the report for the province
        rep_province_resp = session.get(f"{BASE_URL}/admin/reports/province/{province_id}", timeout=TIMEOUT)
        assert rep_province_resp.status_code == 200, f"Failed to get province report: {rep_province_resp.text}"
        rep_province_json = rep_province_resp.json()
        assert "sales_statistics" in rep_province_json, "Province report missing sales_statistics"

        # 5) Retrieve the report for the center
        rep_center_resp = session.get(f"{BASE_URL}/admin/reports/center/{center_id}", timeout=TIMEOUT)
        assert rep_center_resp.status_code == 200, f"Failed to get center report: {rep_center_resp.text}"
        rep_center_json = rep_center_resp.json()
        assert "sales_statistics" in rep_center_json, "Center report missing sales_statistics"

        # 6) Retrieve the report for the pharmacist
        rep_pharmacist_resp = session.get(f"{BASE_URL}/admin/reports/pharmacist/{pharmacist_id}", timeout=TIMEOUT)
        assert rep_pharmacist_resp.status_code == 200, f"Failed to get pharmacist report: {rep_pharmacist_resp.text}"
        rep_pharmacist_json = rep_pharmacist_resp.json()
        assert "sales_statistics" in rep_pharmacist_json, "Pharmacist report missing sales_statistics"

    finally:
        # Clean up created resources in reverse order
        try:
            if 'pharmacist_id' in locals():
                del_resp = session.delete(f"{BASE_URL}/admin/pharmacists/{pharmacist_id}", timeout=TIMEOUT)
                assert del_resp.status_code in (200, 204), f"Failed to delete pharmacist: {del_resp.text}"
        except RequestException:
            pass
        try:
            if 'center_id' in locals():
                del_resp = session.delete(f"{BASE_URL}/admin/centers/{center_id}", timeout=TIMEOUT)
                assert del_resp.status_code in (200, 204), f"Failed to delete center: {del_resp.text}"
        except RequestException:
            pass
        try:
            if 'province_id' in locals():
                del_resp = session.delete(f"{BASE_URL}/admin/provinces/{province_id}", timeout=TIMEOUT)
                assert del_resp.status_code in (200, 204), f"Failed to delete province: {del_resp.text}"
        except RequestException:
            pass
        # Logout to clean session cookies
        try:
            session.post(f"{BASE_URL}/logout", timeout=TIMEOUT)
        except RequestException:
            pass

test_reports_generation_for_pharmacies()