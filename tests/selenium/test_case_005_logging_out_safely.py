# FILE: tests/selenium/test_case_005_logging_out_safely.py
from selenium.webdriver.support.ui import WebDriverWait


def test_case_005_logging_out_safely(driver, helpers):
    """
    Case-005 (Proponent: Daniel Lorenzo Diamante)
    Verifies that logging out terminates the authenticated session, leaves /dashboard,
    and blocks subsequent access to protected routes (/dashboard).
    """
    state = helpers.get_state()

    helpers.login_as(state["student_email"], state["student_password"])
    helpers.logout_user()
    WebDriverWait(driver, 15).until(lambda d: "/dashboard" not in d.current_url)

    helpers.save_evidence("Case-005")
    assert "/dashboard" not in driver.current_url, f"Still stuck on {driver.current_url} after logout"
    helpers.safe_get(f"{helpers.base_url}/dashboard")
    WebDriverWait(driver, 15).until(lambda d: "/login" in d.current_url)
    assert "/login" in driver.current_url, "Session remained active; /dashboard did not redirect to /login."