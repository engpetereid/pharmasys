import requests
from bs4 import BeautifulSoup

BASE_URL = "http://localhost:8000"
TIMEOUT = 30

# Credentials for an admin user to authenticate session-based access
ADMIN_CREDENTIALS = {
    "email": "admin@example.com",
    "password": "adminpassword"
}

def authenticate_session():
    session = requests.Session()
    login_url = f"{BASE_URL}/login"
    try:
        # Get login page to get cookies and CSRF token
        resp = session.get(login_url, timeout=TIMEOUT)
        resp.raise_for_status()

        # Parse CSRF token from HTML
        soup = BeautifulSoup(resp.text, 'html.parser')
        token_input = soup.find('input', {'name': '_token'})
        csrf_token = token_input['value'] if token_input else None
        assert csrf_token, "CSRF token not found on login page"

        # Prepare data with CSRF token
        login_data = {
            "email": ADMIN_CREDENTIALS['email'],
            "password": ADMIN_CREDENTIALS['password'],
            "_token": csrf_token
        }

        # Post credentials to login
        resp = session.post(login_url, data=login_data, timeout=TIMEOUT, allow_redirects=True)
        resp.raise_for_status()

        # Confirm login success by checking URL or content
        if resp.url.endswith("/login") or resp.status_code != 200:
            raise Exception("Authentication failed: Invalid credentials or login unsuccessful.")
        return session
    except Exception as e:
        raise Exception(f"Authentication failed: {e}") from e

def test_admin_dashboard_endpoints():
    session = authenticate_session()
    try:
        # Test main admin dashboard endpoint with no filters
        url_main = f"{BASE_URL}/admin"
        params = {}  # No month/year filter for initial test
        resp_main = session.get(url_main, params=params, timeout=TIMEOUT)
        assert resp_main.status_code == 200, f"Main admin dashboard status code is {resp_main.status_code}, expected 200"
        data_main = resp_main.json()
        # Validate main elements presence in the response based on dashboard features
        assert "sales_statistics" in data_main, "Missing 'sales_statistics' in main dashboard response"
        assert "risky_zones" in data_main, "Missing 'risky_zones' in main dashboard response"
        assert "top_performers" in data_main, "Missing 'top_performers' in main dashboard response"
        assert "latest_invoices" in data_main, "Missing 'latest_invoices' in main dashboard response"
        assert "overdue_invoices" in data_main, "Missing 'overdue_invoices' in main dashboard response"

        # Test main admin dashboard with month/year filter
        params_filter = {"month": 6, "year": 2024}
        resp_filter = session.get(url_main, params=params_filter, timeout=TIMEOUT)
        assert resp_filter.status_code == 200, f"Filtered main admin dashboard status code is {resp_filter.status_code}, expected 200"
        data_filter = resp_filter.json()
        # Similar keys must be present
        assert "sales_statistics" in data_filter, "Missing 'sales_statistics' in filtered main dashboard response"
        assert "risky_zones" in data_filter, "Missing 'risky_zones' in filtered main dashboard response"

        # To test line-specific dashboard endpoint, need a valid line id; since no id given, get a zone to identify line
        zones_url = f"{BASE_URL}/admin/zones"
        resp_zones = session.get(zones_url, timeout=TIMEOUT)
        resp_zones.raise_for_status()
        zones = resp_zones.json()
        assert isinstance(zones, list), "Zones response is not a list"
        assert len(zones) > 0, "Zones list is empty, cannot test line dashboard"
        zone_id = zones[0].get("id")
        assert zone_id, "Zone entry missing 'id' field"

        url_line = f"{BASE_URL}/admin/dashboard/line/{zone_id}"
        resp_line = session.get(url_line, params=params_filter, timeout=TIMEOUT)
        assert resp_line.status_code == 200, f"Line dashboard status code is {resp_line.status_code}, expected 200"
        data_line = resp_line.json()
        # Validate keys presence similar to main dashboard
        assert "sales_statistics" in data_line, "Missing 'sales_statistics' in line dashboard response"
        assert "risky_zones" in data_line, "Missing 'risky_zones' in line dashboard response"
        assert "top_performers" in data_line, "Missing 'top_performers' in line dashboard response"
        assert "latest_invoices" in data_line, "Missing 'latest_invoices' in line dashboard response"
        assert "overdue_invoices" in data_line, "Missing 'overdue_invoices' in line dashboard response"

        # Test month/year filtering on line dashboard works by checking the response content
        resp_line_filtered = session.get(url_line, params={"month": 1, "year": 2024}, timeout=TIMEOUT)
        assert resp_line_filtered.status_code == 200, f"Filtered line dashboard status code is {resp_line_filtered.status_code}, expected 200"
        data_line_filtered = resp_line_filtered.json()
        # Ensure keys exist again
        assert "sales_statistics" in data_line_filtered, "Missing 'sales_statistics' in filtered line dashboard response"
        assert "risky_zones" in data_line_filtered, "Missing 'risky_zones' in filtered line dashboard response"

    finally:
        session.post(f"{BASE_URL}/logout", timeout=TIMEOUT)

test_admin_dashboard_endpoints()