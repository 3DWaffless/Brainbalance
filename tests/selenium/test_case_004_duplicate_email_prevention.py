# FILE: tests/selenium/test_case_004_duplicate_email_prevention.py
import time
from selenium.webdriver.common.by import By
from selenium.webdriver.support import expected_conditions as EC
from selenium.webdriver.support.ui import WebDriverWait


def test_case_004_duplicate_email_prevention(driver, helpers):
    """
    Case-004 (Proponent: Daniel Lorenzo Diamante)
    Verifies that attempting to register with an email address already in the database
    is blocked with Laravel's unique email validation error.
    """
    state = helpers.get_state()
    existing_email = state["student_email"]

    # 1. Start at /login and click "Create an account"
    helpers.safe_get(f"{helpers.base_url}/login")

    xpath_query = (
        "//a[contains(translate(normalize-space(.), 'ABCDEFGHIJKLMNOPQRSTUVWXYZ', 'abcdefghijklmnopqrstuvwxyz'), 'create an account') "
        "or contains(translate(normalize-space(.), 'ABCDEFGHIJKLMNOPQRSTUVWXYZ', 'abcdefghijklmnopqrstuvwxyz'), 'create account') "
        "or contains(translate(normalize-space(.), 'ABCDEFGHIJKLMNOPQRSTUVWXYZ', 'abcdefghijklmnopqrstuvwxyz'), 'sign up') "
        "or contains(translate(normalize-space(.), 'ABCDEFGHIJKLMNOPQRSTUVWXYZ', 'abcdefghijklmnopqrstuvwxyz'), 'register')] "
        "| //button[contains(translate(normalize-space(.), 'ABCDEFGHIJKLMNOPQRSTUVWXYZ', 'abcdefghijklmnopqrstuvwxyz'), 'create an account') "
        "or contains(translate(normalize-space(.), 'ABCDEFGHIJKLMNOPQRSTUVWXYZ', 'abcdefghijklmnopqrstuvwxyz'), 'create account') "
        "or contains(translate(normalize-space(.), 'ABCDEFGHIJKLMNOPQRSTUVWXYZ', 'abcdefghijklmnopqrstuvwxyz'), 'sign up') "
        "or contains(translate(normalize-space(.), 'ABCDEFGHIJKLMNOPQRSTUVWXYZ', 'abcdefghijklmnopqrstuvwxyz'), 'register')]"
    )

    create_btn = None
    for el in driver.find_elements(By.XPATH, xpath_query):
        if el.is_displayed():
            create_btn = el
            break

    if not create_btn:
        create_btn = helpers.first_visible(By.CSS_SELECTOR, "a[href*='/register']")

    assert create_btn is not None, f"Could not find 'Create an account' button/link on {driver.current_url}"
    driver.execute_script("arguments[0].click();", create_btn)
    time.sleep(0.4)

    # 2. Select Student role if shown
    role_triggers = driver.find_elements(
        By.XPATH,
        "//button[contains(., 'Student')] | //label[contains(., 'Student')] | //*[@data-role='student']",
    )
    for trigger in role_triggers:
        if trigger.is_displayed():
            driver.execute_script("arguments[0].click();", trigger)
            time.sleep(0.2)
            break

    role_hidden = driver.find_elements(By.ID, "role-input")
    if role_hidden:
        driver.execute_script("arguments[0].value = 'student';", role_hidden[0])

    # 3. Fill out the form using the email already created in Case-001
    first_name_el = WebDriverWait(driver, 15).until(
        EC.visibility_of_element_located((By.NAME, "first_name"))
    )
    first_name_el.clear()
    first_name_el.send_keys("Duplicate")

    last_name_el = driver.find_element(By.NAME, "last_name")
    last_name_el.clear()
    last_name_el.send_keys("User")

    reg_form = first_name_el.find_element(By.XPATH, "./ancestor::form")

    email_el = reg_form.find_element(By.NAME, "email")
    email_el.clear()
    email_el.send_keys(existing_email)

    pass_el = reg_form.find_element(By.NAME, "password")
    pass_el.clear()
    pass_el.send_keys("StrongPass!2026")

    confirm_el = reg_form.find_element(By.NAME, "password_confirmation")
    confirm_el.clear()
    confirm_el.send_keys("StrongPass!2026")

    helpers.normalize_local_forms()
    submit_btn = reg_form.find_element(By.CSS_SELECTOR, "button[type='submit']")
    driver.execute_script("arguments[0].click();", submit_btn)

    # 4. Wait for validation error and capture screenshot
    time.sleep(1.5)
    helpers.save_evidence("Case-004")

    page_text = driver.find_element(By.TAG_NAME, "body").text.lower()
    page_source = driver.page_source.lower()

    assert "already been taken" in page_text or "already been taken" in page_source, (
        f"Expected duplicate email error for {existing_email}, but it was not displayed."
    )