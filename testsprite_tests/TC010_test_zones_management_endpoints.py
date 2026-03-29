import requests
import random
import string

BASE_URL = "http://localhost:8000"
TIMEOUT = 30


def random_string(length=8):
    return ''.join(random.choices(string.ascii_letters + string.digits, k=length))


def create_resource(endpoint, data, session):
    resp = session.post(f"{BASE_URL}{endpoint}", json=data, timeout=TIMEOUT)
    resp.raise_for_status()
    return resp.json()["id"] if "id" in resp.json() else resp.json().get("data", {}).get("id")


def delete_resource(endpoint, resource_id, session):
    resp = session.delete(f"{BASE_URL}{endpoint}/{resource_id}", timeout=TIMEOUT)
    return resp


def test_zones_management_endpoints():
    session = requests.Session()

    # First GET /login to get CSRF token cookie
    login_page_resp = session.get(f"{BASE_URL}/login", timeout=TIMEOUT)
    login_page_resp.raise_for_status()

    # Get CSRF token from cookies
    csrf_token = session.cookies.get('XSRF-TOKEN')
    assert csrf_token is not None, "CSRF token not found in cookies"

    # Set headers with CSRF token for login - use correct header name
    headers = {
        "X-XSRF-TOKEN": csrf_token,
        "Referer": f"{BASE_URL}/login",
        "Content-Type": "application/x-www-form-urlencoded"
    }

    # Authenticate as admin to access the endpoints (simulate login)
    login_data = {"email": "admin@example.com", "password": "adminpassword", "_token": csrf_token}
    login_resp = session.post(f"{BASE_URL}/login", data=login_data, headers=headers, timeout=TIMEOUT)
    assert login_resp.status_code == 200, f"Login failed: {login_resp.text}"

    try:
        # Pre-create required linked resources: Warehouse, Center, Representative
        warehouse_data = {"name": f"Warehouse_{random_string()}", "location": "Test Location"}
        warehouse_id = create_resource("/admin/warehouses", warehouse_data, session)
        assert warehouse_id is not None

        # Province is required before Center creation (Center linked to Province)
        province_data = {"name": f"Province_{random_string()}"}
        province_id = create_resource("/admin/provinces", province_data, session)
        assert province_id is not None

        center_data = {"name": f"Center_{random_string()}", "province_id": province_id, "address": "Test Address"}
        center_id = create_resource("/admin/centers", center_data, session)
        assert center_id is not None

        representative_data = {"name": f"Rep_{random_string()}", "type": "medical", "phone": "123456789"}
        representative_id = create_resource("/admin/representatives", representative_data, session)
        assert representative_id is not None

        # Create zone linked to the above resources, line 1
        zone_payload = {
            "name": f"Zone_{random_string()}",
            "warehouse_id": warehouse_id,
            "center_id": center_id,
            "representative_id": representative_id,
            "line": 1
        }

        create_resp = session.post(f"{BASE_URL}/admin/zones", json=zone_payload, timeout=TIMEOUT)
        assert create_resp.status_code == 201, f"Zone creation failed: {create_resp.text}"
        zone_id = create_resp.json().get("id") or create_resp.json().get("data", {}).get("id")
        assert zone_id is not None

        # Retrieve zone by ID and verify content
        get_resp = session.get(f"{BASE_URL}/admin/zones/{zone_id}", timeout=TIMEOUT)
        assert get_resp.status_code == 200
        zone_data = get_resp.json()
        assert zone_data["name"] == zone_payload["name"]
        assert zone_data["warehouse_id"] == warehouse_id
        assert zone_data["center_id"] == center_id
        assert zone_data["representative_id"] == representative_id
        assert zone_data["line"] == 1

        # Update zone: change line to 2 and name
        updated_name = f"{zone_payload['name']}_updated"
        update_payload = {
            "name": updated_name,
            "line": 2,
            "warehouse_id": warehouse_id,
            "center_id": center_id,
            "representative_id": representative_id
        }
        update_resp = session.put(f"{BASE_URL}/admin/zones/{zone_id}", json=update_payload, timeout=TIMEOUT)
        assert update_resp.status_code == 200, f"Update failed: {update_resp.text}"

        # Validate update
        get_updated_resp = session.get(f"{BASE_URL}/admin/zones/{zone_id}", timeout=TIMEOUT)
        assert get_updated_resp.status_code == 200
        updated_zone = get_updated_resp.json()
        assert updated_zone["name"] == updated_name
        assert updated_zone["line"] == 2

        # Retrieve all zones and verify the zone is in the list
        list_resp = session.get(f"{BASE_URL}/admin/zones", timeout=TIMEOUT)
        assert list_resp.status_code == 200
        zones_list = list_resp.json()
        assert any(z.get("id") == zone_id for z in zones_list)

    finally:
        # Cleanup: delete created zone and linked resources
        if 'zone_id' in locals():
            del_resp = delete_resource("/admin/zones", zone_id, session)
            assert del_resp.status_code in [200, 204]

        if 'warehouse_id' in locals():
            del_resp = delete_resource("/admin/warehouses", warehouse_id, session)
            assert del_resp.status_code in [200, 204]

        if 'center_id' in locals():
            del_resp = delete_resource("/admin/centers", center_id, session)
            assert del_resp.status_code in [200, 204]

        if 'province_id' in locals():
            del_resp = delete_resource("/admin/provinces", province_id, session)
            assert del_resp.status_code in [200, 204]

        if 'representative_id' in locals():
            del_resp = delete_resource("/admin/representatives", representative_id, session)
            assert del_resp.status_code in [200, 204]


test_zones_management_endpoints()