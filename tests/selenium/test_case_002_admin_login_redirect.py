# FILE: tests/selenium/test_case_002_admin_login_redirect.py
from selenium.webdriver.support.ui import WebDriverWait


def test_case_002_admin_login_redirect(driver, helpers):
    """
    Case-002 (Proponent: Daniel Lorenzo Diamante)
    Verifies that logging in with an admin account redirects to /admin/dashboard
    instead of the standard user /dashboard.
    """
    state = helpers.get_state()
    helpers.login_as(state["admin_email"], state["admin_password"])
    WebDriverWait(driver, 15).until(lambda d: "/admin/dashboard" in d.current_url)
    helpers.save_evidence("Case-002")

    assert "/admin/dashboard" in driver.current_url, (
        f"Expected redirect to /admin/dashboard, got {driver.current_url}"
    )
    helpers.logout_user()