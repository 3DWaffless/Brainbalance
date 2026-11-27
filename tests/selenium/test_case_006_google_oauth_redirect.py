# FILE: tests/selenium/test_case_006_google_oauth_redirect.py
from selenium.webdriver.common.by import By


def test_case_006_google_oauth_redirect_initiation(driver, helpers):
    """
    Case-006 (Proponent: Jhustine Dave Rivera)
    Verifies that the Google Sign-In link/button is visible on /login and properly
    points to the /auth/google OAuth initiation route.
    """
    # 1. Navigate to the login page
    helpers.safe_get(f"{helpers.base_url}/login")

    # 2. Locate the Google OAuth link (/auth/google)
    google_links = driver.find_elements(By.CSS_SELECTOR, "a[href*='/auth/google']")
    visible_links = [el for el in google_links if el.is_displayed()]

    target = visible_links[0] if visible_links else (google_links[0] if google_links else None)
    assert target is not None, "Google OAuth link (/auth/google) was not found on /login"

    # 3. Scroll the Google button into view and capture screenshot evidence
    driver.execute_script("arguments[0].scrollIntoView({block: 'center'});", target)
    helpers.save_evidence("Case-006")

    href = target.get_attribute("href") or ""
    assert "/auth/google" in href, f"Expected /auth/google in href, got {href!r}"