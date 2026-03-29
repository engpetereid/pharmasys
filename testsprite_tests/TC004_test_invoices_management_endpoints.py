import requests
import json

BASE_URL = "http://localhost:8000"
TIMEOUT = 30

# Credentials for an admin user to authenticate and access protected endpoints
ADMIN_CREDENTIALS = {
    "email": "admin@example.com",
    "password": "adminpassword"
}

def authenticate_session():
    """Authenticate and return a requests.Session with the logged-in user."""
    session = requests.Session()
    login_url = f"{BASE_URL}/login"
    try:
        resp = session.get(login_url, timeout=TIMEOUT)
        resp.raise_for_status()
        # Normally a CSRF token or other headers might be needed here; skipping for brevity
        login_resp = session.post(login_url, data=ADMIN_CREDENTIALS, timeout=TIMEOUT)
        login_resp.raise_for_status()
        # Assuming login success redirects or returns 200
        assert login_resp.status_code in (200, 302)
        return session
    except Exception as e:
        raise RuntimeError(f"Failed to authenticate: {e}")

def test_invoices_management_endpoints():
    session = authenticate_session()
    headers = {"Accept": "application/json"}

    invoice_id = None
    doctor_deal_id = None
    warehouse_id = None

    try:
        # === Step 1: Create a warehouse for stock deduction testing ===
        # Create warehouse with minimal required data (name)
        warehouse_payload = {
            "name": "Test Warehouse for Invoice"
        }
        resp = session.post(f"{BASE_URL}/admin/warehouses", json=warehouse_payload, headers=headers, timeout=TIMEOUT)
        resp.raise_for_status()
        warehouse_data = resp.json()
        warehouse_id = warehouse_data.get("id")
        assert warehouse_id is not None

        # === Step 2: Create a doctor needed for doctor deal linking ===
        doctor_payload = {
            "name": "Dr. Test Invoice",
            "center_id": None  # We will create a center below or fetch one
        }

        # We need a center_id linked to the doctor; create a center first
        center_payload = {
            "name": "Test Center for Invoice",
            "province_id": None  # Create or find a province first
        }

        # Create a province first
        province_payload = {"name": "Test Province for Invoice"}
        province_resp = session.post(f"{BASE_URL}/admin/provinces", json=province_payload, headers=headers, timeout=TIMEOUT)
        province_resp.raise_for_status()
        province_data = province_resp.json()
        province_id = province_data.get("id")
        assert province_id is not None

        # Now create center using province_id
        center_payload["province_id"] = province_id
        center_resp = session.post(f"{BASE_URL}/admin/centers", json=center_payload, headers=headers, timeout=TIMEOUT)
        center_resp.raise_for_status()
        center_data = center_resp.json()
        center_id = center_data.get("id")
        assert center_id is not None

        # Now set center_id in doctor payload
        doctor_payload["center_id"] = center_id

        doctor_resp = session.post(f"{BASE_URL}/admin/doctors", json=doctor_payload, headers=headers, timeout=TIMEOUT)
        doctor_resp.raise_for_status()
        doctor_data = doctor_resp.json()
        doctor_id = doctor_data.get("id")
        assert doctor_id is not None

        # === Step 3: Create doctor deal to track with invoices ===
        deal_payload = {
            "doctor_id": doctor_id,
            "target_amount": 1000,
            "commission_percentage": 5,
            "pharmacist_id": None,
            "drug_ids": []
        }

        # We need a pharmacist for the deal (pharmacist linked to center)
        pharmacist_payload = {
            "name": "Pharmacist Invoice Test",
            "center_id": center_id
        }
        pharmacist_resp = session.post(f"{BASE_URL}/admin/pharmacists", json=pharmacist_payload, headers=headers, timeout=TIMEOUT)
        pharmacist_resp.raise_for_status()
        pharmacist_data = pharmacist_resp.json()
        pharmacist_id = pharmacist_data.get("id")
        assert pharmacist_id is not None
        deal_payload["pharmacist_id"] = pharmacist_id

        # Create a drug as well to include in the deal
        drug_payload = {
            "name": "Invoice Test Drug",
            "price": 10.5,
            "line": 1
        }
        drug_resp = session.post(f"{BASE_URL}/admin/drugs", json=drug_payload, headers=headers, timeout=TIMEOUT)
        drug_resp.raise_for_status()
        drug_data = drug_resp.json()
        drug_id = drug_data.get("id")
        assert drug_id is not None
        deal_payload["drug_ids"] = [drug_id]

        deal_resp = session.post(f"{BASE_URL}/admin/deals", json=deal_payload, headers=headers, timeout=TIMEOUT)
        deal_resp.raise_for_status()
        deal_data = deal_resp.json()
        doctor_deal_id = deal_data.get("id")
        assert doctor_deal_id is not None

        # === Step 4: Create an invoice linked to above entities ===
        invoice_payload = {
            "doctor_id": doctor_id,
            "pharmacist_id": pharmacist_id,
            "warehouse_id": warehouse_id,
            "invoice_date": "2024-01-01",
            "details": [
                {
                    "drug_id": drug_id,
                    "quantity": 5,
                    "unit_price": 10.5,
                    "line": 1
                }
            ],
            "total": 52.5
        }
        resp = session.post(f"{BASE_URL}/admin/invoices", json=invoice_payload, headers=headers, timeout=TIMEOUT)
        resp.raise_for_status()
        invoice_data = resp.json()
        invoice_id = invoice_data.get("id")
        assert invoice_id is not None

        # === Step 5: Test filtering invoices ===
        filter_params = {"doctor_id": doctor_id}
        filter_resp = session.get(f"{BASE_URL}/admin/invoices", params=filter_params, headers=headers, timeout=TIMEOUT)
        filter_resp.raise_for_status()
        filtered = filter_resp.json()
        assert any(inv["id"] == invoice_id for inv in filtered)

        # === Step 6: Generate PDF for invoice ===
        pdf_resp = session.get(f"{BASE_URL}/admin/invoices/{invoice_id}/pdf", headers={}, timeout=TIMEOUT)
        pdf_resp.raise_for_status()
        assert pdf_resp.headers["Content-Type"] in ("application/pdf", "application/octet-stream")
        assert pdf_resp.content  # some bytes content must exist

        # === Step 7: Export invoices as CSV ===
        csv_resp = session.get(f"{BASE_URL}/admin/invoices/export", headers={}, timeout=TIMEOUT)
        csv_resp.raise_for_status()
        content_type = csv_resp.headers.get("Content-Type", "")
        assert "csv" in content_type or "text/csv" in content_type
        assert csv_resp.content

        # === Step 8: Check warehouse stock deduction (simulate stock add then invoice stock deduction) ===
        # Add stock first to warehouse for the drug
        stock_add_payload = {
            "drug_id": drug_id,
            "quantity": 10
        }
        add_stock_resp = session.post(f"{BASE_URL}/admin/warehouses/{warehouse_id}/stock/add", json=stock_add_payload, headers=headers, timeout=TIMEOUT)
        add_stock_resp.raise_for_status()

        # We assume invoice creation deducted stock,
        # So get warehouse details and verify stock has been deducted accordingly
        warehouse_resp = session.get(f"{BASE_URL}/admin/warehouses/{warehouse_id}", headers=headers, timeout=TIMEOUT)
        warehouse_resp.raise_for_status()
        warehouse_info = warehouse_resp.json()
        inventory_items = warehouse_info.get("inventory", [])
        drug_stock = None
        for item in inventory_items:
            if item.get("drug_id") == drug_id:
                drug_stock = item.get("quantity")
                break
        # Initial stock added 10, invoice used 5, so expect remaining stock 5 or less (assuming deduction on invoice creation)
        assert drug_stock is not None and drug_stock <= 5

        # === Step 9: Doctor deal tracking - get invoices for deal ===
        deal_invoices_resp = session.get(f"{BASE_URL}/admin/deals/{doctor_deal_id}/invoices", headers=headers, timeout=TIMEOUT)
        deal_invoices_resp.raise_for_status()
        deal_invoices = deal_invoices_resp.json()
        # The created invoice should appear under this deal
        assert any(inv["id"] == invoice_id for inv in deal_invoices)

    finally:
        # Cleanup in reverse order of creation if IDs exist
        if invoice_id:
            try:
                session.delete(f"{BASE_URL}/admin/invoices/{invoice_id}", headers=headers, timeout=TIMEOUT)
            except Exception:
                pass
        if doctor_deal_id:
            try:
                session.delete(f"{BASE_URL}/admin/deals/{doctor_deal_id}", headers=headers, timeout=TIMEOUT)
            except Exception:
                pass
        if drug_id:
            try:
                session.delete(f"{BASE_URL}/admin/drugs/{drug_id}", headers=headers, timeout=TIMEOUT)
            except Exception:
                pass
        if pharmacist_id:
            try:
                session.delete(f"{BASE_URL}/admin/pharmacists/{pharmacist_id}", headers=headers, timeout=TIMEOUT)
            except Exception:
                pass
        if doctor_id:
            try:
                session.delete(f"{BASE_URL}/admin/doctors/{doctor_id}", headers=headers, timeout=TIMEOUT)
            except Exception:
                pass
        if center_id:
            try:
                session.delete(f"{BASE_URL}/admin/centers/{center_id}", headers=headers, timeout=TIMEOUT)
            except Exception:
                pass
        if province_id:
            try:
                session.delete(f"{BASE_URL}/admin/provinces/{province_id}", headers=headers, timeout=TIMEOUT)
            except Exception:
                pass
        if warehouse_id:
            try:
                session.delete(f"{BASE_URL}/admin/warehouses/{warehouse_id}", headers=headers, timeout=TIMEOUT)
            except Exception:
                pass

test_invoices_management_endpoints()