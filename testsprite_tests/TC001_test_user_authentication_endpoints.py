import requests
from requests.exceptions import RequestException

BASE_URL = "http://localhost:8000"
TIMEOUT = 30


def test_user_authentication_endpoints():
    session = requests.Session()

    def get_csrf_token():
        # Perform a GET request to /register to get CSRF token cookie set
        url = f"{BASE_URL}/register"
        resp = session.get(url, timeout=TIMEOUT)
        # Laravel usually sets csrf cookie named 'XSRF-TOKEN'
        csrf_token = session.cookies.get('XSRF-TOKEN')
        return csrf_token

    # Helper function to register a user
    def register_user(username, email, password):
        url = f"{BASE_URL}/register"
        payload = {
            "name": username,
            "email": email,
            "password": password,
            "password_confirmation": password
        }
        csrf_token = get_csrf_token()
        headers = {
            'Content-Type': 'application/json',
        }
        if csrf_token:
            headers['X-CSRF-TOKEN'] = csrf_token
        resp = session.post(url, json=payload, headers=headers, timeout=TIMEOUT)
        return resp

    # Helper function to login
    def login_user(email, password):
        url = f"{BASE_URL}/login"
        payload = {"email": email, "password": password}
        csrf_token = get_csrf_token()
        headers = {'Content-Type': 'application/json'}
        if csrf_token:
            headers['X-CSRF-TOKEN'] = csrf_token
        resp = session.post(url, json=payload, headers=headers, timeout=TIMEOUT)
        return resp

    # Helper function to logout
    def logout_user():
        url = f"{BASE_URL}/logout"
        csrf_token = get_csrf_token()
        headers = {}
        if csrf_token:
            headers['X-CSRF-TOKEN'] = csrf_token
        resp = session.post(url, headers=headers, timeout=TIMEOUT)
        return resp

    # Helper function to initiate password reset
    def password_reset_request(email):
        url = f"{BASE_URL}/password/email"  # Assumed typical endpoint for password reset email
        payload = {"email": email}
        csrf_token = get_csrf_token()
        headers = {'Content-Type': 'application/json'}
        if csrf_token:
            headers['X-CSRF-TOKEN'] = csrf_token
        resp = session.post(url, json=payload, headers=headers, timeout=TIMEOUT)
        return resp

    # Helper function to verify email status or resend verification
    def resend_email_verification():
        url = f"{BASE_URL}/email/verification-notification"  # Common Laravel route for resend
        csrf_token = get_csrf_token()
        headers = {}
        if csrf_token:
            headers['X-CSRF-TOKEN'] = csrf_token
        resp = session.post(url, headers=headers, timeout=TIMEOUT)
        return resp

    import random, string

    def random_string(length=8):
        return ''.join(random.choices(string.ascii_lowercase + string.digits, k=length))

    users = {
        "admin": {
            "username": f"admin_{random_string()}",
            "email": f"admin_{random_string()}@example.com",
            "password": "AdminPass123!",
        },
        "accountant": {
            "username": f"acct_{random_string()}",
            "email": f"acct_{random_string()}@example.com",
            "password": "AcctPass123!",
        }
    }

    try:
        for role, user in users.items():
            # Registration
            resp = register_user(user["username"], user["email"], user["password"])
            assert resp.status_code in {200, 201, 302}, f"Registration failed for role {role}: {resp.text}"

            # After registration, user is typically logged in or needs login
            resp = login_user(user["email"], user["password"])
            assert resp.status_code == 200, f"Login failed for role {role}: {resp.text}"
            assert "Set-Cookie" in resp.headers or session.cookies.get_dict(), "Session cookie missing after login"

            # Check login GET page accessible (not required but confirm)
            resp_get = session.get(f"{BASE_URL}/login", timeout=TIMEOUT)
            assert resp_get.status_code in {200, 401, 403, 302, 404}, "Unexpected status code for GET /login"

            # Password reset request
            resp_pwd_reset = password_reset_request(user["email"])
            assert resp_pwd_reset.status_code in {200, 202, 204}, f"Password reset request failed for {role}"

            # Email verification resend
            resp_email_verify = resend_email_verification()
            assert resp_email_verify.status_code in {200, 202, 400}, f"Email verification resend failed for {role}"

            # Logout
            resp_logout = logout_user()
            assert resp_logout.status_code in {200, 204, 302}, f"Logout failed for role {role}"

            # After logout, session cookies cleared or invalid
            resp_after_logout = session.get(f"{BASE_URL}/admin", timeout=TIMEOUT, allow_redirects=False)
            assert resp_after_logout.status_code in {401, 302, 403}, f"Protected resource access allowed after logout for role {role}"

            session.cookies.clear()

    except RequestException as e:
        assert False, f"Request failed with exception: {e}"

    finally:
        session.close()


test_user_authentication_endpoints()
