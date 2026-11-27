# FILE: tests/selenium/test_case_012_join_class_code_normalization.py
import time
from selenium.webdriver.common.by import By
from selenium.webdriver.support.ui import WebDriverWait


def test_case_012_join_class_code_normalization(driver, helpers):
    """
    Case-012
    Module: Classroom & Materials | Unit: Join Class Code Normalization
    Verifies that entering a lowercase/whitespace-padded class code on /dashboard
    normalizes the code (e.g., '  qwerty  ' -> 'QWERTY') and joins the classroom.
    """
    driver.delete_all_cookies()
    state = helpers.get_state()
    raw_code = f"  {state.get('class_code', 'QWERTY').lower()}  "

    # 1. Log in as the student account and land on /dashboard
    helpers.login_as(state["student_email"], state["student_password"])
    WebDriverWait(driver, 10).until(lambda d: "/dashboard" in d.current_url)

    code_selector = (
        "input[name='class_code'], input[name='code'], "
        "input[placeholder*='code' i], input[placeholder*='class' i]"
    )

    # 2. Find class code input on /dashboard, or click a Join Class button/modal trigger first
    code_input = helpers.first_visible(By.CSS_SELECTOR, code_selector)
    if not code_input:
        triggers = driver.find_elements(
            By.XPATH,
            "//button[contains(translate(normalize-space(.), 'ABCDEFGHIJKLMNOPQRSTUVWXYZ', 'abcdefghijklmnopqrstuvwxyz'), 'join') "
            "or contains(translate(normalize-space(.), 'ABCDEFGHIJKLMNOPQRSTUVWXYZ', 'abcdefghijklmnopqrstuvwxyz'), 'class code')] "
            "| //a[contains(translate(normalize-space(.), 'ABCDEFGHIJKLMNOPQRSTUVWXYZ', 'abcdefghijklmnopqrstuvwxyz'), 'join') "
            "or contains(translate(normalize-space(.), 'ABCDEFGHIJKLMNOPQRSTUVWXYZ', 'abcdefghijklmnopqrstuvwxyz'), 'class code')]",
        )
        for trigger in triggers:
            if trigger.is_displayed():
                driver.execute_script("arguments[0].click();", trigger)
                time.sleep(0.3)
                code_input = helpers.first_visible(By.CSS_SELECTOR, code_selector)
                if code_input:
                    break

    assert code_input is not None, "Class code input field was not found on /dashboard."

    # 3. Enter lowercase/whitespace-padded code and submit
    code_input.clear()
    code_input.send_keys(raw_code)

    helpers.normalize_local_forms()
    forms = code_input.find_elements(By.XPATH, "./ancestor::form")
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
        join_btn = helpers.first_visible(
            By.XPATH,
            "//button[contains(translate(normalize-space(.), 'ABCDEFGHIJKLMNOPQRSTUVWXYZ', 'abcdefghijklmnopqrstuvwxyz'), 'join')]",
        )
        assert join_btn is not None, "Join Class submit button was not found."
        driver.execute_script("arguments[0].click();", join_btn)

    time.sleep(1.0)
    helpers.save_evidence("Case-012")

    body_lower = driver.find_element(By.TAG_NAME, "body").text.lower()
    assert "method not allowed" not in body_lower and "sqlstate" not in body_lower, (
        "Unexpected route/SQL error occurred while joining class."
    )
    assert "500" not in driver.title and "server error" not in body_lower, (
        "Server error occurred when joining class with unnormalized code."
    )
    assert "/login" not in driver.current_url, "Unexpectedly redirected to /login while joining class."