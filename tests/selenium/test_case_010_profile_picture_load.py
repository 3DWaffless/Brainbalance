# FILE: tests/selenium/test_case_010_profile_picture_load.py
from selenium.webdriver.common.by import By
from selenium.webdriver.support.ui import WebDriverWait


def test_case_010_profile_picture_load(driver, helpers):
    """
    Case-010
    Module: User Profile & Avatar | Unit: Profile Picture Load
    Verifies that an authenticated user's profile/settings page loads cleanly
    and renders avatar/profile image elements without broken image states.
    """
    driver.delete_all_cookies()
    state = helpers.get_state()

    # 1. Log in as the student account
    helpers.login_as(state["student_email"], state["student_password"])
    WebDriverWait(driver, 10).until(lambda d: "/dashboard" in d.current_url)

    # 2. Navigate to /settings (or /profile fallback if /settings isn't used)
    helpers.safe_get(f"{helpers.base_url}/settings")
    if "404" in driver.title or "/login" in driver.current_url:
        helpers.safe_get(f"{helpers.base_url}/profile")

    WebDriverWait(driver, 10).until(
        lambda d: len(d.find_elements(By.TAG_NAME, "body")) > 0
    )

    # 3. Inspect any <img> elements on the page for broken loads
    images = driver.find_elements(By.TAG_NAME, "img")
    visible_imgs = [img for img in images if img.is_displayed()]

    for img in visible_imgs:
        src = (img.get_attribute("src") or "").strip()
        if src:
            natural_width = driver.execute_script(
                "return arguments[0].complete ? arguments[0].naturalWidth : 0;", img
            )
            assert natural_width > 0, f"Broken image detected on profile page: src={src!r}"

    # 4. Capture screenshot evidence
    helpers.save_evidence("Case-010")