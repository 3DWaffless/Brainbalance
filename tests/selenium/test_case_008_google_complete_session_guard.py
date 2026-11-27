# FILE: tests/selenium/test_case_008_google_complete_session_guard.py
from selenium.webdriver.common.by import By
from selenium.webdriver.support.ui import WebDriverWait


def test_case_008_google_complete_session_guard(driver, helpers):
    """
    Case-008 (Proponent: Jhustine Dave Rivera)
    Module: Google Sign-In | Unit: Unverified Google Session Guard
    Verifies SocialiteController::showCompleteForm() blocks direct access to
    /auth/google/complete when no Google OAuth session exists and redirects to /register.
    """
    driver.delete_all_cookies()

    # 1. Navigate directly to /auth/google/complete without signing in via Google first
    helpers.safe_get(f"{helpers.base_url}/auth/google/complete")

    # 2. Wait for redirect to /register
    WebDriverWait(driver, 15).until(lambda d: "/register" in d.current_url)
    helpers.save_evidence("Case-008")

    body_lower = driver.find_element(By.TAG_NAME, "body").text.lower()
    source_lower = driver.page_source.lower()

    assert "/register" in driver.current_url, (
        f"Expected redirect to /register, got {driver.current_url}"
    )
    assert (
        "signing in with google first" in body_lower
        or "signing in with google first" in source_lower
    ), "Expected Google sign-in session warning message was not displayed."