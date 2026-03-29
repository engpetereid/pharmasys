import requests

BASE_URL = "http://localhost:8000"
TIMEOUT = 30

# Credentials for admin user - replace with valid test credentials
ADMIN_CREDENTIALS = {
    "email": "admin@example.com",
    "password": "adminpassword"
}

def get_csrf_token_from_cookies(session):
    # Laravel typically stores XSRF-TOKEN cookie base64 encoded
    token = session.cookies.get('XSRF-TOKEN')
    if token:
        import urllib.parse
        return urllib.parse.unquote(token)
    return None

def test_admin_dashboard_data_retrieval():
    session = requests.Session()
    try:
        # Get login page to obtain CSRF token cookie
        login_page_resp = session.get(f"{BASE_URL}/login", timeout=TIMEOUT)
        assert login_page_resp.status_code == 200, f"Login page request failed with status {login_page_resp.status_code}"
        csrf_token = get_csrf_token_from_cookies(session)
        assert csrf_token, "CSRF token not found in cookies"

        # Prepare login data
        login_payload = {
            "email": ADMIN_CREDENTIALS["email"],
            "password": ADMIN_CREDENTIALS["password"],
            "_token": csrf_token
        }
        headers = {
            'X-CSRF-TOKEN': csrf_token,
            'Referer': f'{BASE_URL}/login'
        }

        # Login to obtain session cookie
        login_resp = session.post(f"{BASE_URL}/login", data=login_payload, headers=headers, timeout=TIMEOUT)
        assert login_resp.status_code == 200, f"Login failed with status {login_resp.status_code}"
        assert "Set-Cookie" in login_resp.headers or session.cookies.get_dict(), "Session cookie not found in login response"

        # Test dashboard data retrieval without filters
        dash_resp = session.get(f"{BASE_URL}/admin", timeout=TIMEOUT)
        assert dash_resp.status_code == 200, f"Dashboard request failed with status {dash_resp.status_code}"
        dash_json = dash_resp.json()
        # Validate presence of expected keys in dashboard response
        expected_keys = ["sales_statistics", "risky_zones", "top_performers", "latest_invoices", "overdue_invoices"]
        for key in expected_keys:
            assert key in dash_json, f"Missing key '{key}' in dashboard response"

        # Test dashboard data retrieval with month and year filters (e.g. March 2024)
        params = {"month": 3, "year": 2024}
        dash_filtered_resp = session.get(f"{BASE_URL}/admin", params=params, timeout=TIMEOUT)
        assert dash_filtered_resp.status_code == 200, f"Filtered dashboard request failed with status {dash_filtered_resp.status_code}"
        dash_filtered_json = dash_filtered_resp.json()
        for key in expected_keys:
            assert key in dash_filtered_json, f"Missing key '{key}' in filtered dashboard response"

        # Additional sanity checks for data types / contents
        # sales_statistics should be a dict and contain numeric values
        sales_stats = dash_json.get("sales_statistics", {})
        assert isinstance(sales_stats, dict), "sales_statistics is not a dictionary"
        for metric, value in sales_stats.items():
            assert isinstance(value, (int, float)), f"sales_statistics metric '{metric}' is not a number"

        # risky_zones should be a list
        assert isinstance(dash_json.get("risky_zones"), list), "risky_zones is not a list"

        # top_performers should be a list
        assert isinstance(dash_json.get("top_performers"), list), "top_performers is not a list"

        # latest_invoices should be a list
        assert isinstance(dash_json.get("latest_invoices"), list), "latest_invoices is not a list"

    finally:
        # Get fresh CSRF token for logout
        logout_page_resp = session.get(f"{BASE_URL}/logout", timeout=TIMEOUT)
        csrf_token_logout = get_csrf_token_from_cookies(session)
        if csrf_token_logout is None:
            csrf_token_logout = csrf_token  # fallback
        logout_payload = {"_token": csrf_token_logout}
        logout_headers = {
            'X-CSRF-TOKEN': csrf_token_logout,
            'Referer': f'{BASE_URL}/logout'
        }
        logout_resp = session.post(f"{BASE_URL}/logout", data=logout_payload, headers=logout_headers, timeout=TIMEOUT)
        assert logout_resp.status_code in (200, 204), f"Logout failed with status {logout_resp.status_code}"

test_admin_dashboard_data_retrieval()
