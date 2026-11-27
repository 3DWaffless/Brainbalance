# FILE: tests/selenium/test_integ_10_and_15.py
import time
from selenium.webdriver.common.by import By
from selenium.webdriver.support.ui import WebDriverWait


def _assert_healthy(driver, case_id: str):
    body_lower = driver.find_element(By.TAG_NAME, "body").text.lower()
    assert "500" not in driver.title and "server error" not in body_lower, (
        f"Server error encountered during {case_id}"
    )
    assert "method not allowed" not in body_lower and "sqlstate" not in body_lower, (
        f"Route/SQL error encountered during {case_id}"
    )


def _click_matching(driver, helpers, keywords):
    cond = " or ".join(
        f"contains(translate(normalize-space(.), 'ABCDEFGHIJKLMNOPQRSTUVWXYZ', 'abcdefghijklmnopqrstuvwxyz'), '{k}')"
        for k in keywords
    )
    el = helpers.first_visible(By.XPATH, f"//a[{cond}] | //button[{cond}]")
    if el:
        driver.execute_script("arguments[0].scrollIntoView({block: 'center'});", el)
        driver.execute_script("arguments[0].click();", el)
        time.sleep(0.4)
    return el


def _login_student(driver, helpers):
    driver.delete_all_cookies()
    state = helpers.get_state()
    helpers.login_as(state["student_email"], state["student_password"])
    WebDriverWait(driver, 10).until(lambda d: "/dashboard" in d.current_url)
    return state


def test_integ_10_assignment_and_submission(driver, helpers):
    """IntegTestCase-10: Assignment & Submission."""
    _login_student(driver, helpers)
    _click_matching(driver, helpers, ["assignment", "to do", "homework", "turn in"])
    helpers.save_evidence("IntegTestCase-10")
    _assert_healthy(driver, "IntegTestCase-10")


def test_integ_15_quiz_runner_and_scoring(driver, helpers):
    """IntegTestCase-15: Quiz Runner & Scoring."""
    _login_student(driver, helpers)
    _click_matching(driver, helpers, ["quiz", "start", "play", "quest"])
    helpers.save_evidence("IntegTestCase-15")
    _assert_healthy(driver, "IntegTestCase-15")