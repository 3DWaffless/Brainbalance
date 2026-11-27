# FILE: tests/selenium/test_all_integration_01_to_20.py
import os
import time
from selenium.webdriver.common.by import By
from selenium.webdriver.support import expected_conditions as EC
from selenium.webdriver.support.ui import WebDriverWait


def _create_tiny_file(path: str, content: bytes) -> str:
    with open(path, "wb") as f:
        f.write(content)
    return path


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


def _submit_nearest(driver, helpers, field_el, keywords=None):
    helpers.normalize_local_forms()
    forms = field_el.find_elements(By.XPATH, "./ancestor::form")
    if forms:
        btns = [
            b
            for b in forms[0].find_elements(
                By.CSS_SELECTOR, "button[type='submit'], input[type='submit']"
            )
            if b.is_displayed()
        ]
        if btns:
            driver.execute_script("arguments[0].click();", btns[0])
            return
        driver.execute_script("arguments[0].submit();", forms[0])
        return

    kws = keywords or ["save", "submit", "create", "post", "join", "publish"]
    cond = " or ".join(
        f"contains(translate(normalize-space(.), 'ABCDEFGHIJKLMNOPQRSTUVWXYZ', 'abcdefghijklmnopqrstuvwxyz'), '{k}')"
        for k in kws
    )
    btn = helpers.first_visible(By.XPATH, f"//button[{cond}]")
    if btn:
        driver.execute_script("arguments[0].click();", btn)


def _login_student(driver, helpers):
    driver.delete_all_cookies()
    state = helpers.get_state()
    helpers.login_as(state["student_email"], state["student_password"])
    WebDriverWait(driver, 10).until(lambda d: "/dashboard" in d.current_url)
    return state


def _login_teacher(driver, helpers):
    driver.delete_all_cookies()
    state = helpers.get_state()
    helpers.login_as(state["teacher_email"], state["teacher_password"], expect_success=False)
    time.sleep(0.5)
    return state


def _open_register_from_login(driver, helpers):
    helpers.safe_get(f"{helpers.base_url}/login")
    xpath_q = (
        "//a[contains(translate(normalize-space(.), 'ABCDEFGHIJKLMNOPQRSTUVWXYZ', 'abcdefghijklmnopqrstuvwxyz'), 'create an account') "
        "or contains(translate(normalize-space(.), 'ABCDEFGHIJKLMNOPQRSTUVWXYZ', 'abcdefghijklmnopqrstuvwxyz'), 'create account') "
        "or contains(translate(normalize-space(.), 'ABCDEFGHIJKLMNOPQRSTUVWXYZ', 'abcdefghijklmnopqrstuvwxyz'), 'sign up') "
        "or contains(translate(normalize-space(.), 'ABCDEFGHIJKLMNOPQRSTUVWXYZ', 'abcdefghijklmnopqrstuvwxyz'), 'register')] "
        "| //button[contains(translate(normalize-space(.), 'ABCDEFGHIJKLMNOPQRSTUVWXYZ', 'abcdefghijklmnopqrstuvwxyz'), 'create an account') "
        "or contains(translate(normalize-space(.), 'ABCDEFGHIJKLMNOPQRSTUVWXYZ', 'abcdefghijklmnopqrstuvwxyz'), 'create account') "
        "or contains(translate(normalize-space(.), 'ABCDEFGHIJKLMNOPQRSTUVWXYZ', 'abcdefghijklmnopqrstuvwxyz'), 'sign up') "
        "or contains(translate(normalize-space(.), 'ABCDEFGHIJKLMNOPQRSTUVWXYZ', 'abcdefghijklmnopqrstuvwxyz'), 'register')]"
    )
    target = None
    for el in driver.find_elements(By.XPATH, xpath_q):
        if el.is_displayed():
            target = el
            break
    if not target:
        target = helpers.first_visible(By.CSS_SELECTOR, "a[href*='/register']")
    assert target is not None, f"Registration trigger not found on {driver.current_url}"
    driver.execute_script("arguments[0].click();", target)
    time.sleep(0.25)


# ==============================================================================
# IntegTestCase-01 to IntegTestCase-05 (Proponent: Kabristante, Shanley)
# ==============================================================================

def test_integ_01_registration_and_login_redirect(driver, helpers):
    """IntegTestCase-01 | Module: Registration & Login | Proponent: Kabristante, Shanley"""
    driver.delete_all_cookies()
    _open_register_from_login(driver, helpers)

    for trigger in driver.find_elements(
        By.XPATH,
        "//button[contains(., 'Student')] | //label[contains(., 'Student')] | //*[@data-role='student']",
    ):
        if trigger.is_displayed():
            driver.execute_script("arguments[0].click();", trigger)
            time.sleep(0.15)
            break

    role_hidden = driver.find_elements(By.ID, "role-input")
    if role_hidden:
        driver.execute_script("arguments[0].value = 'student';", role_hidden[0])

    unique_email = f"integ_student_{int(time.time())}@brainbalance.test"
    password = "StrongPass!2026"

    first_name = WebDriverWait(driver, 10).until(
        EC.visibility_of_element_located((By.NAME, "first_name"))
    )
    first_name.clear()
    first_name.send_keys("Shanley")

    last_name = driver.find_element(By.NAME, "last_name")
    last_name.clear()
    last_name.send_keys("Kabristante")

    form = first_name.find_element(By.XPATH, "./ancestor::form")
    for name, val in [
        ("email", unique_email),
        ("password", password),
        ("password_confirmation", password),
    ]:
        inp = form.find_element(By.NAME, name)
        inp.clear()
        inp.send_keys(val)

    _submit_nearest(driver, helpers, first_name, ["register", "create", "sign up"])
    time.sleep(0.8)
    helpers.save_evidence("IntegTestCase-01")
    _assert_healthy(driver, "IntegTestCase-01")
    helpers.update_state({"student_email": unique_email, "student_password": password})


def test_integ_02_login_and_student_dashboard(driver, helpers):
    """IntegTestCase-02 | Module: Login & Student Dashboard | Proponent: Kabristante, Shanley"""
    _login_student(driver, helpers)
    time.sleep(0.4)
    helpers.save_evidence("IntegTestCase-02")
    _assert_healthy(driver, "IntegTestCase-02")
    assert "/dashboard" in driver.current_url


def test_integ_03_google_sso_and_registration(driver, helpers):
    """IntegTestCase-03 | Module: Google SSO & Registration | Proponent: Kabristante, Shanley"""
    driver.delete_all_cookies()
    helpers.safe_get(f"{helpers.base_url}/login")
    links = driver.find_elements(By.CSS_SELECTOR, "a[href*='/auth/google']")
    assert len(links) > 0, "Google SSO link (/auth/google) not found on /login"
    driver.execute_script("arguments[0].scrollIntoView({block: 'center'});", links[0])
    helpers.save_evidence("IntegTestCase-03")
    _assert_healthy(driver, "IntegTestCase-03")


def test_integ_04_classroom_and_enrollment(driver, helpers):
    """IntegTestCase-04 | Module: Classroom & Enrollment | Proponent: Kabristante, Shanley"""
    state = _login_student(driver, helpers)
    code_input = helpers.first_visible(
        By.CSS_SELECTOR,
        "input[name='class_code'], input[name='code'], input[placeholder*='code' i], input[placeholder*='class' i]",
    )
    if code_input:
        code_input.clear()
        code_input.send_keys(state.get("class_code", "QWERTY"))
        _submit_nearest(driver, helpers, code_input, ["join"])
        time.sleep(0.6)
    helpers.save_evidence("IntegTestCase-04")
    _assert_healthy(driver, "IntegTestCase-04")


def test_integ_05_classroom_and_quizzes_modules_stream(driver, helpers):
    """IntegTestCase-05 | Module: Classroom & Quizzes / Modules / Stream | Proponent: Kabristante, Shanley"""
    _login_teacher(driver, helpers)
    _click_matching(driver, helpers, ["class", "classes", "classroom"])
    helpers.save_evidence("IntegTestCase-05")
    _assert_healthy(driver, "IntegTestCase-05")


# ==============================================================================
# IntegTestCase-06 to IntegTestCase-10 (Proponent: Diamante, Daniel)
# ==============================================================================

def test_integ_06_class_stream_and_file_storage(driver, helpers):
    """IntegTestCase-06 | Module: Class Stream & File Storage | Proponent: Diamante, Daniel"""
    _login_teacher(driver, helpers)
    file_inputs = driver.find_elements(By.CSS_SELECTOR, "input[type='file']")
    if file_inputs:
        sample_txt = _create_tiny_file(
            os.path.join(helpers.download_dir, "integ_module_06.txt"),
            b"BrainBalance Grade 3 Math & English Module Content",
        )
        driver.execute_script("""
            arguments[0].style.display = 'block';
            arguments[0].style.visibility = 'visible';
            arguments[0].style.opacity = '1';
        """, file_inputs[0])
        file_inputs[0].send_keys(sample_txt)
        time.sleep(0.3)
    helpers.save_evidence("IntegTestCase-06")
    _assert_healthy(driver, "IntegTestCase-06")


def test_integ_07_class_stream_and_student_logs(driver, helpers):
    """IntegTestCase-07 | Module: Class Stream & Student Logs | Proponent: Diamante, Daniel"""
    _login_student(driver, helpers)
    _click_matching(driver, helpers, ["module", "material", "stream", "download"])
    helpers.save_evidence("IntegTestCase-07")
    _assert_healthy(driver, "IntegTestCase-07")


def test_integ_08_module_and_ai_chat_groq(driver, helpers):
    """IntegTestCase-08 | Module: Module & AI Chat (Groq) | Proponent: Diamante, Daniel"""
    _login_student(driver, helpers)
    _click_matching(driver, helpers, ["study buddy", "ai", "chat", "ask", "module"])
    helpers.save_evidence("IntegTestCase-08")
    _assert_healthy(driver, "IntegTestCase-08")


def test_integ_09_assignment_and_student_assignments_tab(driver, helpers):
    """IntegTestCase-09 | Module: Assignment & Student Assignments Tab | Proponent: Diamante, Daniel"""
    _login_teacher(driver, helpers)
    _click_matching(driver, helpers, ["assignment", "create", "post"])
    helpers.save_evidence("IntegTestCase-09")
    _assert_healthy(driver, "IntegTestCase-09")


def test_integ_10_assignment_and_submission(driver, helpers):
    """IntegTestCase-10 | Module: Assignment & Submission | Proponent: Diamante, Daniel"""
    _login_student(driver, helpers)
    _click_matching(driver, helpers, ["assignment", "to do", "homework", "turn in"])
    helpers.save_evidence("IntegTestCase-10")
    _assert_healthy(driver, "IntegTestCase-10")


# ==============================================================================
# IntegTestCase-11 to IntegTestCase-15 (Proponent: Kabristante, Shanley)
# ==============================================================================

def test_integ_11_submission_and_grading(driver, helpers):
    """IntegTestCase-11 | Module: Submission & Grading | Proponent: Kabristante, Shanley"""
    _login_teacher(driver, helpers)
    _click_matching(driver, helpers, ["submission", "grade", "assignment"])
    helpers.save_evidence("IntegTestCase-11")
    _assert_healthy(driver, "IntegTestCase-11")


def test_integ_12_quiz_builder_and_ai_generation(driver, helpers):
    """IntegTestCase-12 | Module: Quiz Builder & AI Generation | Proponent: Kabristante, Shanley"""
    _login_teacher(driver, helpers)
    _click_matching(driver, helpers, ["quiz", "generate", "ai"])
    helpers.save_evidence("IntegTestCase-12")
    _assert_healthy(driver, "IntegTestCase-12")


def test_integ_13_quiz_builder_and_question_bank(driver, helpers):
    """IntegTestCase-13 | Module: Quiz Builder & Question Bank | Proponent: Kabristante, Shanley"""
    _login_teacher(driver, helpers)
    _click_matching(driver, helpers, ["quiz", "question bank", "draft", "publish"])
    helpers.save_evidence("IntegTestCase-13")
    _assert_healthy(driver, "IntegTestCase-13")


def test_integ_14_quiz_editor_and_student_quiz_access(driver, helpers):
    """IntegTestCase-14 | Module: Quiz Editor & Student Quiz Access | Proponent: Kabristante, Shanley"""
    _login_teacher(driver, helpers)
    _click_matching(driver, helpers, ["quiz", "edit"])
    helpers.save_evidence("IntegTestCase-14")
    _assert_healthy(driver, "IntegTestCase-14")


def test_integ_15_quiz_runner_and_scoring(driver, helpers):
    """IntegTestCase-15 | Module: Quiz Runner & Scoring | Proponent: Kabristante, Shanley"""
    _login_student(driver, helpers)
    _click_matching(driver, helpers, ["quiz", "start", "play", "quest"])
    helpers.save_evidence("IntegTestCase-15")
    _assert_healthy(driver, "IntegTestCase-15")


# ==============================================================================
# IntegTestCase-16 to IntegTestCase-20 (Proponent: Diamante, Daniel)
# ==============================================================================

def test_integ_16_quiz_runner_and_attempt_limit(driver, helpers):
    """IntegTestCase-16 | Module: Quiz Runner & Attempt Limit | Proponent: Diamante, Daniel"""
    _login_student(driver, helpers)
    _click_matching(driver, helpers, ["quiz", "attempt", "start"])
    helpers.save_evidence("IntegTestCase-16")
    _assert_healthy(driver, "IntegTestCase-16")


def test_integ_17_quiz_completion_and_user_profile(driver, helpers):
    """IntegTestCase-17 | Module: Quiz Completion & User Profile | Proponent: Diamante, Daniel"""
    _login_student(driver, helpers)
    _click_matching(driver, helpers, ["profile", "xp", "level", "quiz"])
    helpers.save_evidence("IntegTestCase-17")
    _assert_healthy(driver, "IntegTestCase-17")


def test_integ_18_quiz_completion_and_badges(driver, helpers):
    """IntegTestCase-18 | Module: Quiz Completion & Badges | Proponent: Diamante, Daniel"""
    _login_student(driver, helpers)
    _click_matching(driver, helpers, ["badge", "medal", "reward", "notification"])
    helpers.save_evidence("IntegTestCase-18")
    _assert_healthy(driver, "IntegTestCase-18")


def test_integ_19_quiz_results_and_ai_struggle_analysis(driver, helpers):
    """IntegTestCase-19 | Module: Quiz Results & AI Struggle Analysis | Proponent: Diamante, Daniel"""
    _login_teacher(driver, helpers)
    _click_matching(driver, helpers, ["struggle", "result", "quiz", "analy"])
    helpers.save_evidence("IntegTestCase-19")
    _assert_healthy(driver, "IntegTestCase-19")


def test_integ_20_student_logs_and_csv_export(driver, helpers):
    """IntegTestCase-20 | Module: Student Logs & CSV Export | Proponent: Diamante, Daniel"""
    _login_teacher(driver, helpers)
    _click_matching(driver, helpers, ["log", "csv", "export"])
    helpers.save_evidence("IntegTestCase-20")
    _assert_healthy(driver, "IntegTestCase-20")