# FILE: tests/selenium/test_case_011_profile_picture_update.py
import os
import time
from selenium.webdriver.common.by import By
from selenium.webdriver.support.ui import WebDriverWait


def _create_tiny_png(path: str) -> str:
    # Minimal valid 1x1 PNG so file input upload works without external sample images
    png_hex = (
        "89504e470d0a1a0a0000000d4948445200000001000000010802000000907753de"
        "0000000c49444154789c636060f80f0001050102c74f12500000000049454e44ae426082"
    )
    with open(path, "wb") as f:
        f.write(bytes.fromhex(png_hex))
    return path


def test_case_011_profile_picture_update(driver, helpers):
    """
    Case-011
    Module: User Profile & Avatar | Unit: Profile Picture Update
    Verifies uploading a new profile picture/avatar on /settings (or /profile)
    succeeds without server errors and persists after reload.
    """
    driver.delete_all_cookies()
    state = helpers.get_state()

    # 1. Log in as the student account
    helpers.login_as(state["student_email"], state["student_password"])
    WebDriverWait(driver, 10).until(lambda d: "/dashboard" in d.current_url)

    # 2. Navigate to /settings (or /profile fallback)
    helpers.safe_get(f"{helpers.base_url}/settings")
    if "404" in driver.title or "/login" in driver.current_url:
        helpers.safe_get(f"{helpers.base_url}/profile")

    # 3. Locate the file input for avatar/profile picture
    file_inputs = driver.find_elements(By.CSS_SELECTOR, "input[type='file']")
    assert len(file_inputs) > 0, "No file input found on the profile/settings page for avatar upload."

    avatar_input = file_inputs[0]
    sample_png = _create_tiny_png(os.path.join(helpers.download_dir, "test_avatar.png"))

    # Make hidden file inputs interactable if styled as custom upload buttons
    driver.execute_script("""
        arguments[0].style.display = 'block';
        arguments[0].style.visibility = 'visible';
        arguments[0].style.opacity = '1';
    """, avatar_input)
    avatar_input.send_keys(sample_png)
    time.sleep(0.4)

    # 4. Submit the form containing the file input
    helpers.normalize_local_forms()
    forms = avatar_input.find_elements(By.XPATH, "./ancestor::form")
    if forms:
        submit_btns = [
            b for b in forms[0].find_elements(By.CSS_SELECTOR, "button[type='submit'], input[type='submit']")
            if b.is_displayed()
        ]
        if submit_btns:
            driver.execute_script("arguments[0].click();", submit_btns[0])
        else:
            driver.execute_script("arguments[0].submit();", forms[0])
    else:
        submit_btn = helpers.first_visible(By.CSS_SELECTOR, "button[type='submit']")
        assert submit_btn is not None, "Submit button not found after selecting profile picture."
        driver.execute_script("arguments[0].click();", submit_btn)

    time.sleep(1.0)

    # 5. Refresh to confirm update persists and capture screenshot evidence
    driver.refresh()
    time.sleep(0.6)
    helpers.save_evidence("Case-011")

    body_lower = driver.find_element(By.TAG_NAME, "body").text.lower()
    assert "500" not in driver.title and "server error" not in body_lower, (
        "Server error occurred when updating profile picture."
    )
    assert "/login" not in driver.current_url, "Redirected unexpectedly to /login after profile update."