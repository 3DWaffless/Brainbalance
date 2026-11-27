# FILE: tests/selenium/test_case_001_creating_new_account.py
import time
import uuid
from selenium.webdriver.common.by import By
from selenium.webdriver.support import expected_conditions as EC
from selenium.webdriver.support.ui import WebDriverWait


def test_case_001_creating_new_account(driver, helpers):
    """
    Case-001 (Proponent: Daniel Lorenzo Diamante)
    Starts on /login, clicks the "Create an account" button/link first,
    fills out the student registration form, and verifies redirection to /login.
    """
    unique_email = f"student_{uuid.uuid4().hex[:6]}@brainbalance.test"
    password = "StrongPass!2026"

    # 1. Start at the login page
    helpers.safe_get(f"{helpers.base_url}/login")

    # 2. Find and click the "Create an account" button or link first
    create_btn = None
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

    for el in driver.find_elements(By.XPATH, xpath_query):
        if el.is_displayed():
            create_btn = el
            break

    if not create_btn:
        create_btn = helpers.first_visible(By.CSS_SELECTOR, "a[href*='/register']")

    assert create_btn is not None, f"Could not find 'Create an account' button/link on {driver.current_url}"
    driver.execute_script("arguments[0].click();", create_btn)
    time.sleep(0.4)

    # 3. Select Student role card if displayed
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

    # 4. Fill out the registration form
    first_name_el = WebDriverWait(driver, 15).until(
        EC.visibility_of_element_located((By.NAME, "first_name"))
    )
    first_name_el.clear()
    first_name_el.send_keys("Juan")

    last_name_el = driver.find_element(By.NAME, "last_name")
    last_name_el.clear()
    last_name_el.send_keys("Dela Cruz")

    reg_form = first_name_el.find_element(By.XPATH, "./ancestor::form")

    email_el = reg_form.find_element(By.NAME, "email")
    email_el.clear()
    email_el.send_keys(unique_email)

    pass_el = reg_form.find_element(By.NAME, "password")
    pass_el.clear()
    pass_el.send_keys(password)

    confirm_el = reg_form.find_element(By.NAME, "password_confirmation")
    confirm_el.clear()
    confirm_el.send_keys(password)

    helpers.normalize_local_forms()
    submit_btn = reg_form.find_element(By.CSS_SELECTOR, "button[type='submit']")
    driver.execute_script("arguments[0].click();", submit_btn)

    # 5. Verify redirect back to /login with confirmation flash message
    WebDriverWait(driver, 15).until(
        lambda d: "/login" in d.current_url
        or "account created successfully" in d.find_element(By.TAG_NAME, "body").text.lower()
    )
    helpers.save_evidence("Case-001")

    # Save registered student credentials for subsequent test files
    helpers.update_state({
        "student_email": unique_email,
        "student_password": password,
    })

    body_lower = driver.find_element(By.TAG_NAME, "body").text.lower()
    assert "/login" in driver.current_url or "account created successfully" in body_lower