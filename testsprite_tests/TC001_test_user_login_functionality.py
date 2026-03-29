import requests
from bs4 import BeautifulSoup

BASE_URL = "http://localhost:8000"
TIMEOUT = 30


def test_user_login_functionality():
    login_url = f"{BASE_URL}/login"
    session = requests.Session()

    try:
        # First, get the login page to fetch CSRF token
        response_get = session.get(login_url, timeout=TIMEOUT)
        assert response_get.status_code == 200, f"Expected 200 OK from login page, got {response_get.status_code}"

        # Parse CSRF token from the HTML
        soup = BeautifulSoup(response_get.text, 'html.parser')
        csrf_input = soup.find('input', {'name': '_token'})
        assert csrf_input is not None, "CSRF token input field not found"
        csrf_token = csrf_input.get('value')

        # Prepare login data with CSRF token
        credentials = {
            "email": "testuser@example.com",
            "password": "TestPassword123",
            "_token": csrf_token
        }

        # Headers
        headers = {
            "Content-Type": "application/x-www-form-urlencoded",
            "Accept": "text/html,application/xhtml+xml,application/xml"
        }

        # Post login
        response_post = session.post(login_url, data=credentials, headers=headers, allow_redirects=False, timeout=TIMEOUT)

        # Successful login usually returns 302 redirect in session-based Laravel auth
        assert response_post.status_code in (302, 303), f"Expected status code 302 or 303 but got {response_post.status_code}"

        # Check for 'set-cookie' header presence
        cookie_header_present = any(h.lower() == 'set-cookie' for h in response_post.headers.keys())
        assert cookie_header_present, "Session cookie not set in response headers"

    except requests.exceptions.RequestException as e:
        assert False, f"Request failed: {e}"


test_user_login_functionality()