# FILE: tests/selenium/test_case_003_invalid_login_credentials.py
import time
from selenium.webdriver.common.by import By


def test_case_003_invalid_login_credentials(driver, helpers):
    """
    Case-003 (Proponent: Daniel Lorenzo Diamante)
    Verifies that submitting invalid login credentials keeps the user on /login
    and displays the authentication error message.
    """
    # 1. Attempt login with invalid credentials
    helpers.login_as(
        "nonexistent_user@brainbalance.com",
        "WrongPassword123",
        expect_success=False,
    )

    time.sleep(1.0)

    # 2. Capture screenshot evidence
    helpers.save_evidence("Case-003")

    # 3. Verify URL stays on /login and error message is rendered
    page_text = driver.find_element(By.TAG_NAME, "body").text.lower()
    page_source = driver.page_source.lower()

    assert "/login" in driver.current_url, f"Expected to remain on /login, got {driver.current_url}"
    assert (
        "do not match our records" in page_text
        or "do not match our records" in page_source
        or any(word in page_text for word in ["credentials", "invalid", "incorrect", "failed"])
    ), f"Expected login error message not found on {driver.current_url}"