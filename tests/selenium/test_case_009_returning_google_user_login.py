# FILE: tests/selenium/test_case_009_returning_google_user_login.py
from selenium.webdriver.common.by import By
from selenium.webdriver.support.ui import WebDriverWait


def test_case_009_returning_google_user_login(driver, helpers):
    """
    Case-009 (Proponent: Jhustine Dave Rivera)
    Module: Google Sign-In | Unit: Returning Google User Login
    Verifies the Google Sign-In option on /login and confirms an existing
    registered user signs straight into /dashboard without repeating registration.
    """
    driver.delete_all_cookies() 
    state = helpers.get_state()

    # 1. Confirm Google Sign-In link is available on /login
    helpers.safe_get(f"{helpers.base_url}/login")
    google_links = driver.find_elements(By.CSS_SELECTOR, "a[href*='/auth/google']")
    assert len(google_links) > 0, "Google Sign-In link (/auth/google) not found on /login"

    # 2. Sign in with the existing user account and verify direct landing on /dashboard
    helpers.login_as(state["student_email"], state["student_password"])
    WebDriverWait(driver, 10).until(lambda d: "/dashboard" in d.current_url)
    helpers.save_evidence("Case-009")

    assert "/dashboard" in driver.current_url, (
        f"Expected returning user to land on /dashboard, got {driver.current_url}"
    )
    assert "/auth/google/complete" not in driver.current_url