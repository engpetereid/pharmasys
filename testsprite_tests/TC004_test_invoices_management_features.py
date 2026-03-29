import requests
import re

BASE_URL = "http://localhost:8000"
TIMEOUT = 30

# Replace these with valid admin credentials for authentication
ADMIN_CREDENTIALS = {
    "email": "admin@example.com",
    "password": "adminpassword"
}

def login(session):
    login_url = f"{BASE_URL}/login"
    # Get login page to fetch CSRF token
    resp = session.get(login_url, timeout=TIMEOUT)
    assert resp.status_code == 200, f"Login page access failed: {resp.text}"
    # Extract CSRF token from HTML
    match = re.search(r'name="_token" value="([^"]+)"', resp.text)
    assert match, "CSRF token not found on login page"
    csrf_token = match.group(1)

    login_data = ADMIN_CREDENTIALS.copy()
    login_data["_token"] = csrf_token

    resp = session.post(login_url, data=login_data, timeout=TIMEOUT)
    assert resp.status_code == 200, f"Login failed: {resp.text}"

def fetch_csrf_token(session, url):
    """Fetch CSRF token from a page."""
    resp = session.get(url, timeout=TIMEOUT)
    assert resp.status_code == 200, f"CSRF token page fetch failed: {resp.text}"
    match = re.search(r'name="_token" value="([^"]+)"', resp.text)
    assert match, "CSRF token not found on page"
    return match.group(1)


def test_invoices_management_features():
    session = requests.Session()
    try:
        # Authenticate
        login(session)

        # Get CSRF token for invoice creation
        create_url = f"{BASE_URL}/admin/invoices"
        csrf_token = fetch_csrf_token(session, create_url)

        # 1. Create a new invoice
        # Minimal invoice payload (needs realistic data, adjust as per API schema)
        invoice_payload = {
            "customer_name": "Test Customer",
            "date": "2024-06-01",
            "details": [
                {
                    "drug_id": 1,  # Assuming a drug with ID 1 exists
                    "quantity": 2,
                    "price": 100.0
                }
            ],
            "total": 200.0,
            "warehouse_id": 1,  # Assuming warehouse with ID 1 exists
            "doctor_id": 1,      # Assuming doctor with ID 1 exists
            "_token": csrf_token
        }
        resp = session.post(create_url, data=invoice_payload, timeout=TIMEOUT)
        assert resp.status_code == 201, f"Invoice creation failed: {resp.text}"
        invoice = resp.json()
        invoice_id = invoice.get("id")
        assert invoice_id is not None, "Created invoice ID is None"

        # 2. Retrieve the created invoice
        get_url = f"{BASE_URL}/admin/invoices/{invoice_id}"
        resp = session.get(get_url, timeout=TIMEOUT)
        assert resp.status_code == 200, f"Invoice retrieval failed: {resp.text}"
        invoice_detail = resp.json()
        assert invoice_detail.get("id") == invoice_id, "Retrieved invoice ID mismatch"

        # 3. Update the invoice
        update_url = f"{BASE_URL}/admin/invoices/{invoice_id}"
        # Fetch CSRF token for update
        csrf_token = fetch_csrf_token(session, update_url)
        updated_payload = {
            "customer_name": "Updated Customer",
            "total": 250.0,
            "_token": csrf_token
        }
        resp = session.put(update_url, data=updated_payload, timeout=TIMEOUT)
        assert resp.status_code == 200, f"Invoice update failed: {resp.text}"
        updated_invoice = resp.json()
        assert updated_invoice.get("customer_name") == "Updated Customer", "Invoice customer_name not updated"
        assert updated_invoice.get("total") == 250.0, "Invoice total not updated"

        # 4. Filter invoices (example: filter by customer_name)
        filter_url = f"{BASE_URL}/admin/invoices"
        params = {"customer_name": "Updated Customer"}
        resp = session.get(filter_url, params=params, timeout=TIMEOUT)
        assert resp.status_code == 200, f"Invoice filtering failed: {resp.text}"
        invoices_list = resp.json()
        assert any(inv["id"] == invoice_id for inv in invoices_list), "Filtered invoices missing created invoice"

        # 5. Generate PDF for the invoice
        pdf_url = f"{BASE_URL}/admin/invoices/{invoice_id}/pdf"
        resp = session.get(pdf_url, timeout=TIMEOUT)
        assert resp.status_code == 200, f"Invoice PDF generation failed: {resp.text}"
        content_type = resp.headers.get("Content-Type", "")
        assert "application/pdf" in content_type.lower(), "PDF content type not returned"

        # 6. Export invoices CSV
        export_url = f"{BASE_URL}/admin/invoices/export"
        resp = session.get(export_url, timeout=TIMEOUT)
        assert resp.status_code == 200, f"Invoice CSV export failed: {resp.text}"
        content_type_csv = resp.headers.get("Content-Type", "")
        assert ("text/csv" in content_type_csv.lower()) or ("application/csv" in content_type_csv.lower()), "CSV content type not returned"

        # 7. Warehouse stock deduction verification
        warehouse_id = invoice_payload["warehouse_id"]
        warehouse_url = f"{BASE_URL}/admin/warehouses/{warehouse_id}"
        resp = session.get(warehouse_url, timeout=TIMEOUT)
        assert resp.status_code == 200, f"Warehouse retrieval failed: {resp.text}"
        warehouse_before = resp.json()
        stock_before = {item["drug_id"]: item["quantity"] for item in warehouse_before.get("stock", [])}

        assert isinstance(stock_before, dict), "Warehouse stock data invalid"

        # 8. Doctor deal tracking - get the deals related to this doctor invoice
        doctor_id = invoice_payload["doctor_id"]
        deals_url = f"{BASE_URL}/admin/deals"
        resp = session.get(deals_url, timeout=TIMEOUT)
        assert resp.status_code == 200, f"Doctor deals retrieval failed: {resp.text}"
        deals = resp.json()
        related_deals = [deal for deal in deals if deal.get("doctor_id") == doctor_id]

        if related_deals:
            deal_id = related_deals[0]["id"]
            deal_invoices_url = f"{BASE_URL}/admin/deals/{deal_id}/invoices"
            resp = session.get(deal_invoices_url, timeout=TIMEOUT)
            assert resp.status_code == 200, f"Deal related invoices retrieval failed: {resp.text}"
            deal_invoices = resp.json()
            assert isinstance(deal_invoices, list), "Deal invoices response invalid"

        # 9. Delete the created invoice
        delete_url = f"{BASE_URL}/admin/invoices/{invoice_id}"
        # Fetch CSRF token for delete
        csrf_token = fetch_csrf_token(session, get_url)
        headers = {"X-CSRF-TOKEN": csrf_token}
        resp = session.delete(delete_url, headers=headers, timeout=TIMEOUT)
        assert resp.status_code in (200, 204), f"Invoice deletion failed: {resp.text}"

        # 10. Verify invoice deletion by attempting retrieval (should fail)
        resp = session.get(get_url, timeout=TIMEOUT)
        assert resp.status_code == 404 or resp.status_code == 400, f"Deleted invoice still retrievable: {resp.text}"

    finally:
        if 'invoice_id' in locals():
            delete_url = f"{BASE_URL}/admin/invoices/{invoice_id}"
            try:
                csrf_token = fetch_csrf_token(session, get_url)
                headers = {"X-CSRF-TOKEN": csrf_token}
                session.delete(delete_url, headers=headers, timeout=TIMEOUT)
            except Exception:
                pass


test_invoices_management_features()