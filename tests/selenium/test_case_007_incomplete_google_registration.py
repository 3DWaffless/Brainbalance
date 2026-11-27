# FILE: tests/selenium/test_case_007_incomplete_google_registration.py
import time
from selenium.webdriver.common.by import By
from selenium.webdriver.support.ui import WebDriverWait


def test_case_007_incomplete_google_registration(driver, helpers):
    """
    Case-007 (Proponent: Jhustine Dave Rivera)
    Module: Google Sign-In | Unit: Incomplete Google Registration
    Initiates Google Sign-In from /login, then tries to skip the final confirmation
    step by navigating directly to /dashboard. Verifies access is blocked.
    """
    driver.delete_all_cookies()

    # 1. Start on /login and locate the Google Sign-In button
    helpers.safe_get(f"{helpers.base_url}/login")
    google_btn = helpers.first_visible(By.CSS_SELECTOR, "a[href*='/auth/google']")
    if not google_btn:
        links = driver.find_elements(By.CSS_SELECTOR, "a[href*='/auth/google']")
        google_btn = links[0] if links else None

    assert google_btn is not None, "Google Sign-In link (/auth/google) not found on /login"

    # 2. Initiate Google OAuth redirect
    driver.execute_script("arguments[0].click();", google_btn)
    time.sleep(1.0)

    # 3. Attempt to skip the final confirmation step by navigating directly to /dashboard
    helpers.safe_get(f"{helpers.base_url}/dashboard")

    # 4. Verify unconfirmed user is blocked from /dashboard and redirected to /login
    WebDriverWait(driver, 15).until(lambda d: "/login" in d.current_url)
    helpers.save_evidence("Case-007")

    assert "/login" in driver.current_url, (
        f"Expected redirect to /login when skipping Google confirmation, got {driver.current_url}"
    )