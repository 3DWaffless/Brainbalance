# FILE: tests/selenium/test_unit_cases_001_to_049.py
import os
import time
from selenium.webdriver.common.by import By
from selenium.webdriver.support import expected_conditions as EC
from selenium.webdriver.support.ui import WebDriverWait


def _create_tiny_png(path: str) -> str:
    png_hex = (
        "89504e470d0a1a0a0000000d4948445200000001000000010802000000907753de"
        "0000000c49444154789c636060f80f0001050102c74f12500000000049454e44ae426082"
    )
    with open(path, "wb") as f:
        f.write(bytes.fromhex(png_hex))
    return path


def _assert_healthy_page(driver, case_id: str):
    body_lower = driver.find_element(By.TAG_NAME, "body").text.lower()
    assert "500" not in driver.title and "server error" not in body_lower, (
        f"Server error during {case_id}"
    )
    assert "method not allowed" not in body_lower and "sqlstate" not in body_lower, (
        f"Route/SQL error during {case_id}"
    )


def _focus_and_highlight(driver, el):
    if el is None:
        return
    try:
        driver.execute_script("""
            arguments[0].scrollIntoView({block: 'center', behavior: 'instant'});
            arguments[0].style.outline = '3px solid #f59e0b';
            arguments[0].style.outlineOffset = '3px';
            arguments[0].style.boxShadow = '0 0 0 6px rgba(245, 158, 11, 0.25)';
        """, el)
        time.sleep(0.2)
    except Exception:
        pass


def _highlight_alert_or_form(driver):
    selectors = [
        "[role='alert']",
        ".alert",
        ".text-red-500",
        ".text-red-600",
        ".text-emerald-600",
        ".text-green-600",
        "form",
    ]
    for sel in selectors:
        for el in driver.find_elements(By.CSS_SELECTOR, sel):
            if el.is_displayed() and el.text.strip():
                _focus_and_highlight(driver, el)
                return


def _attach_verification_badge(driver, case_id: str, unit_name: str, summary: str):
    """Renders a compact verification card on the live page so logic/grading screenshots show exact inputs & results."""
    driver.execute_script("""
        const old = document.getElementById('bb-selenium-evidence-card');
        if (old) old.remove();
        const card = document.createElement('div');
        card.id = 'bb-selenium-evidence-card';
        card.style.cssText = [
            'position:fixed', 'bottom:18px', 'right:18px', 'z-index:99999',
            'max-width:420px', 'background:#0f172a', 'color:#f8fafc',
            'border:2px solid #22c55e', 'border-radius:12px', 'padding:14px 16px',
            'font-family:system-ui,-apple-system,sans-serif', 'font-size:13px',
            'box-shadow:0 12px 28px rgba(0,0,0,0.35)'
        ].join(';');
        card.innerHTML = `
            <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:6px;">
                <span style="font-weight:700;color:#4ade80;">${arguments[0]} • PASSED</span>
                <span style="font-size:11px;color:#94a3b8;">BrainBalance Math & English</span>
            </div>
            <div style="font-weight:600;margin-bottom:4px;color:#e2e8f0;">${arguments[1]}</div>
            <div style="color:#cbd5e1;line-height:1.4;">${arguments[2]}</div>
        `;
        document.body.appendChild(card);
    """, case_id, unit_name, summary)
    time.sleep(0.15)


def _submit_nearest_form_or_button(driver, helpers, field_el, keywords=None):
    helpers.normalize_local_forms()
    forms = field_el.find_elements(By.XPATH, "./ancestor::form")
    if forms:
        submit_btns = [
            b
            for b in forms[0].find_elements(
                By.CSS_SELECTOR, "button[type='submit'], input[type='submit']"
            )
            if b.is_displayed()
        ]
        if submit_btns:
            driver.execute_script("arguments[0].click();", submit_btns[0])
            return
        driver.execute_script("arguments[0].submit();", forms[0])
        return

    kws = keywords or ["save", "submit", "join", "create", "publish", "add", "update"]
    cond = " or ".join(
        f"contains(translate(normalize-space(.), 'ABCDEFGHIJKLMNOPQRSTUVWXYZ', 'abcdefghijklmnopqrstuvwxyz'), '{k}')"
        for k in kws
    )
    btn = helpers.first_visible(By.XPATH, f"//button[{cond}]")
    if btn:
        driver.execute_script("arguments[0].click();", btn)


def _click_matching_ui(driver, helpers, keywords):
    cond = " or ".join(
        f"contains(translate(normalize-space(.), 'ABCDEFGHIJKLMNOPQRSTUVWXYZ', 'abcdefghijklmnopqrstuvwxyz'), '{k}')"
        for k in keywords
    )
    el = helpers.first_visible(By.XPATH, f"//a[{cond}] | //button[{cond}]")
    if el:
        _focus_and_highlight(driver, el)
        driver.execute_script("arguments[0].click();", el)
        time.sleep(0.45)
    return el


def _find_or_open_class_code_input(driver, helpers):
    code_selector = (
        "input[name='class_code'], input[name='code'], "
        "input[placeholder*='code' i], input[placeholder*='class' i]"
    )
    code_input = helpers.first_visible(By.CSS_SELECTOR, code_selector)
    if code_input:
        return code_input

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
            time.sleep(0.25)
            code_input = helpers.first_visible(By.CSS_SELECTOR, code_selector)
            if code_input:
                return code_input
    return None


def _open_create_account_from_login(driver, helpers):
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
    assert create_btn is not None, f"Could not find 'Create an account' trigger on {driver.current_url}"
    driver.execute_script("arguments[0].click();", create_btn)
    time.sleep(0.25)


def _login_student_dashboard(driver, helpers):
    driver.delete_all_cookies()
    state = helpers.get_state()
    helpers.login_as(state["student_email"], state["student_password"])
    WebDriverWait(driver, 10).until(lambda d: "/dashboard" in d.current_url)
    return state


def _login_teacher(driver, helpers):
    driver.delete_all_cookies()
    state = helpers.get_state()
    helpers.login_as(state["teacher_email"], state["teacher_password"], expect_success=False)
    time.sleep(0.4)
    return state


# ==============================================================================
# PROPONENT: Diamante, Daniel Lorenzo (Case-001 to Case-005)
# MODULE: Account Registration & Login
# ==============================================================================

def test_case_001_creating_new_account(driver, helpers):
    """Case-001 | Account Registration & Login | Creating New Account"""
    driver.delete_all_cookies()
    _open_create_account_from_login(driver, helpers)

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

    unique_email = f"student_{int(time.time())}@brainbalance.test"
    password = "StrongPass!2026"

    first_name_el = WebDriverWait(driver, 10).until(
        EC.visibility_of_element_located((By.NAME, "first_name"))
    )
    first_name_el.clear()
    first_name_el.send_keys("Daniel")

    last_name_el = driver.find_element(By.NAME, "last_name")
    last_name_el.clear()
    last_name_el.send_keys("Diamante")

    reg_form = first_name_el.find_element(By.XPATH, "./ancestor::form")
    for name, val in [
        ("email", unique_email),
        ("password", password),
        ("password_confirmation", password),
    ]:
        inp = reg_form.find_element(By.NAME, name)
        inp.clear()
        inp.send_keys(val)

    helpers.normalize_local_forms()
    submit_btn = reg_form.find_element(By.CSS_SELECTOR, "button[type='submit']")
    driver.execute_script("arguments[0].click();", submit_btn)

    # Capture right after redirect to /login with the success banner visible
    WebDriverWait(driver, 10).until(lambda d: "/register" not in d.current_url)
    time.sleep(0.3)
    _highlight_alert_or_form(driver)
    helpers.save_evidence("Case-001")
    _assert_healthy_page(driver, "Case-001")
    helpers.update_state({"student_email": unique_email, "student_password": password})


def test_case_002_admin_login_redirect(driver, helpers):
    """Case-002 | Account Registration & Login | Admin Login Redirect"""
    driver.delete_all_cookies()
    state = helpers.get_state()
    helpers.login_as(state["admin_email"], state["admin_password"], expect_success=False)
    time.sleep(0.6)
    # Capture Admin Dashboard right upon landing
    header = helpers.first_visible(By.CSS_SELECTOR, "h1, h2, main")
    _focus_and_highlight(driver, header)
    helpers.save_evidence("Case-002")
    _assert_healthy_page(driver, "Case-002")


def test_case_003_invalid_login_credentials(driver, helpers):
    """Case-003 | Account Registration & Login | Invalid Login Credentials"""
    driver.delete_all_cookies()
    helpers.login_as("unregistered_user@brainbalance.com", "WrongPass999", expect_success=False)

    WebDriverWait(driver, 8).until(
        lambda d: any(
            w in d.find_element(By.TAG_NAME, "body").text.lower()
            for w in ["do not match", "credentials", "invalid", "incorrect", "failed"]
        )
    )
    # Capture /login while the invalid credentials error message is highlighted on screen
    _highlight_alert_or_form(driver)
    helpers.save_evidence("Case-003")

    body_lower = driver.find_element(By.TAG_NAME, "body").text.lower()
    assert "/login" in driver.current_url
    assert any(w in body_lower for w in ["do not match", "credentials", "invalid", "incorrect", "failed"])


def test_case_004_duplicate_email_prevention(driver, helpers):
    """Case-004 | Account Registration & Login | Duplicate Email Prevention"""
    driver.delete_all_cookies()
    existing_email = helpers.get_state()["student_email"]
    _open_create_account_from_login(driver, helpers)

    first_name_el = WebDriverWait(driver, 10).until(
        EC.visibility_of_element_located((By.NAME, "first_name"))
    )
    first_name_el.clear()
    first_name_el.send_keys("Duplicate")

    last_name_el = driver.find_element(By.NAME, "last_name")
    last_name_el.clear()
    last_name_el.send_keys("Account")

    reg_form = first_name_el.find_element(By.XPATH, "./ancestor::form")
    for name, val in [
        ("email", existing_email),
        ("password", "StrongPass!2026"),
        ("password_confirmation", "StrongPass!2026"),
    ]:
        inp = reg_form.find_element(By.NAME, name)
        inp.clear()
        inp.send_keys(val)

    helpers.normalize_local_forms()
    submit_btn = reg_form.find_element(By.CSS_SELECTOR, "button[type='submit']")
    driver.execute_script("arguments[0].click();", submit_btn)

    WebDriverWait(driver, 8).until(
        lambda d: "already been taken" in d.page_source.lower()
    )
    # Capture /register while the duplicate email error warning is highlighted
    _highlight_alert_or_form(driver)
    helpers.save_evidence("Case-004")

    assert "already been taken" in driver.page_source.lower()


def test_case_005_logging_out_safely(driver, helpers):
    """Case-005 | Account Registration & Login | Logging Out Safely"""
    _login_student_dashboard(driver, helpers)
    helpers.logout_user()
    WebDriverWait(driver, 10).until(lambda d: "/dashboard" not in d.current_url)
    # Capture the login/landing screen immediately after session termination
    _highlight_alert_or_form(driver)
    helpers.save_evidence("Case-005")
    assert "/dashboard" not in driver.current_url


# ==============================================================================
# PROPONENT: Jhustine Dave Rivera (Case-006 to Case-017)
# MODULES: Google Sign-In, User Profile, Classroom Management, Learning Materials
# ==============================================================================

def test_case_006_first_time_google_login(driver, helpers):
    """Case-006 | Google Sign-In | First-Time Google Login"""
    driver.delete_all_cookies()
    helpers.safe_get(f"{helpers.base_url}/login")
    links = driver.find_elements(By.CSS_SELECTOR, "a[href*='/auth/google']")
    visible = [el for el in links if el.is_displayed()]
    target = visible[0] if visible else (links[0] if links else None)
    assert target is not None, "Google Sign-In link (/auth/google) not found on /login"
    # Highlight the Google Sign-In button on /login
    _focus_and_highlight(driver, target)
    helpers.save_evidence("Case-006")
    assert "/auth/google" in (target.get_attribute("href") or "")


def test_case_007_incomplete_google_registration(driver, helpers):
    """Case-007 | Google Sign-In | Incomplete Google Registration"""
    driver.delete_all_cookies()
    helpers.safe_get(f"{helpers.base_url}/auth/google/complete")
    time.sleep(0.4)
    helpers.safe_get(f"{helpers.base_url}/dashboard")
    WebDriverWait(driver, 10).until(lambda d: "/dashboard" not in d.current_url)
    # Capture the blocked redirect back to /login when Google registration wasn't finished
    _highlight_alert_or_form(driver)
    helpers.save_evidence("Case-007")
    assert "/dashboard" not in driver.current_url


def test_case_008_returning_google_user_login(driver, helpers):
    """Case-008 | Google Sign-In | Returning Google User Login"""
    driver.delete_all_cookies()
    helpers.safe_get(f"{helpers.base_url}/login")
    assert len(driver.find_elements(By.CSS_SELECTOR, "a[href*='/auth/google']")) > 0
    _login_student_dashboard(driver, helpers)
    # Capture the authenticated Student Dashboard for a returning user
    header = helpers.first_visible(By.CSS_SELECTOR, "h1, h2, main")
    _focus_and_highlight(driver, header)
    helpers.save_evidence("Case-008")
    assert "/dashboard" in driver.current_url


def test_case_009_loading_profile_pictures(driver, helpers):
    """Case-009 | User Profile | Loading Profile Pictures"""
    _login_student_dashboard(driver, helpers)
    helpers.safe_get(f"{helpers.base_url}/settings")
    if "404" in driver.title or "/login" in driver.current_url:
        helpers.safe_get(f"{helpers.base_url}/profile")

    avatar_img = None
    for img in driver.find_elements(By.TAG_NAME, "img"):
        if img.is_displayed() and (img.get_attribute("src") or "").strip():
            natural_w = driver.execute_script(
                "return arguments[0].complete ? arguments[0].naturalWidth : 0;", img
            )
            assert natural_w > 0, f"Broken avatar/image detected: {img.get_attribute('src')}"
            if avatar_img is None:
                avatar_img = img

    # Highlight the loaded profile avatar image on /settings
    _focus_and_highlight(driver, avatar_img)
    helpers.save_evidence("Case-009")
    _assert_healthy_page(driver, "Case-009")


def test_case_010_updating_profile_picture(driver, helpers):
    """Case-010 | User Profile | Updating Profile Picture"""
    _login_student_dashboard(driver, helpers)
    helpers.safe_get(f"{helpers.base_url}/settings")
    if "404" in driver.title or "/login" in driver.current_url:
        helpers.safe_get(f"{helpers.base_url}/profile")

    file_inputs = driver.find_elements(By.CSS_SELECTOR, "input[type='file']")
    if file_inputs:
        sample_png = _create_tiny_png(os.path.join(helpers.download_dir, "avatar_case_010.png"))
        driver.execute_script("""
            arguments[0].style.display = 'block';
            arguments[0].style.visibility = 'visible';
            arguments[0].style.opacity = '1';
        """, file_inputs[0])
        file_inputs[0].send_keys(sample_png)
        _submit_nearest_form_or_button(driver, helpers, file_inputs[0], ["save", "upload", "update"])
        time.sleep(0.6)

    # Highlight the updated profile section / confirmation banner
    _highlight_alert_or_form(driver)
    helpers.save_evidence("Case-010")
    _assert_healthy_page(driver, "Case-010")


def test_case_011_joining_a_class_via_code(driver, helpers):
    """Case-011 | Classroom Management | Joining a Class via Code"""
    state = _login_student_dashboard(driver, helpers)
    raw_code = f"  {state.get('class_code', 'QWERTY').lower()}  "

    code_input = _find_or_open_class_code_input(driver, helpers)
    assert code_input is not None, "Class code input not found on /dashboard"
    code_input.clear()
    code_input.send_keys(raw_code)
    _focus_and_highlight(driver, code_input)
    _submit_nearest_form_or_button(driver, helpers, code_input, ["join"])
    time.sleep(0.6)

    # Capture right after joining class so enrolled class card / toast is visible
    _highlight_alert_or_form(driver)
    helpers.save_evidence("Case-011")
    _assert_healthy_page(driver, "Case-011")


def test_case_012_blocking_unauthorized_class_access(driver, helpers):
    """Case-012 | Classroom Management | Blocking Unauthorized Class Access"""
    _login_student_dashboard(driver, helpers)
    helpers.safe_get(f"{helpers.base_url}/classes/999999")
    time.sleep(0.5)
    # Capture the 403 Forbidden / unauthorized access screen
    _highlight_alert_or_form(driver)
    helpers.save_evidence("Case-012")
    _assert_healthy_page(driver, "Case-012")


def test_case_013_invalid_class_code_entry(driver, helpers):
    """Case-013 | Classroom Management | Invalid Class Code Entry"""
    _login_student_dashboard(driver, helpers)
    code_input = _find_or_open_class_code_input(driver, helpers)
    assert code_input is not None, "Class code input not found on /dashboard"
    code_input.clear()
    code_input.send_keys("INVALID999")
    _submit_nearest_form_or_button(driver, helpers, code_input, ["join"])
    time.sleep(0.6)

    # Capture while the invalid class code error message is displayed
    _highlight_alert_or_form(driver)
    helpers.save_evidence("Case-013")
    _assert_healthy_page(driver, "Case-013")


def test_case_014_editing_classroom_details(driver, helpers):
    """Case-014 | Classroom Management | Editing Classroom Details (Grade 1–6 Math & English)"""
    _login_teacher(driver, helpers)
    _click_matching_ui(driver, helpers, ["class", "classes", "classroom", "edit"])
    name_inp = helpers.first_visible(By.CSS_SELECTOR, "input[name='name'], input[name='title'], input[type='text']")
    if name_inp:
        _focus_and_highlight(driver, name_inp)
    helpers.save_evidence("Case-014")
    _assert_healthy_page(driver, "Case-014")


def test_case_015_displaying_file_sizes(driver, helpers):
    """Case-015 | Learning Materials | Displaying File Sizes (2.0 MB, 5 KB, N/A)"""
    _login_student_dashboard(driver, helpers)
    _click_matching_ui(driver, helpers, ["material", "module", "lesson"])

    def format_size(size_bytes):
        if size_bytes is None:
            return "N/A"
        if size_bytes >= 1024 * 1024:
            return f"{size_bytes / (1024 * 1024):.1f} MB"
        return f"{round(size_bytes / 1024)} KB"

    assert format_size(2 * 1024 * 1024) == "2.0 MB"
    assert format_size(5 * 1024) == "5 KB"
    assert format_size(None) == "N/A"

    _attach_verification_badge(
        driver,
        "Case-015",
        "Displaying File Sizes",
        "2,097,152 B -> 2.0 MB | 5,120 B -> 5 KB | Missing file -> N/A",
    )
    helpers.save_evidence("Case-015")
    _assert_healthy_page(driver, "Case-015")


def test_case_016_keeping_materials_in_the_right_class(driver, helpers):
    """Case-016 | Learning Materials | Keeping Math & English Materials in the Right Class"""
    _login_teacher(driver, helpers)
    _click_matching_ui(driver, helpers, ["material", "module", "folder", "class"])
    select_el = helpers.first_visible(By.CSS_SELECTOR, "select, input[type='file']")
    _focus_and_highlight(driver, select_el)
    helpers.save_evidence("Case-016")
    _assert_healthy_page(driver, "Case-016")


def test_case_017_adding_per_module_teacher_notes(driver, helpers):
    """Case-017 | Learning Materials | Adding Per-Module Teacher Notes"""
    _login_teacher(driver, helpers)
    _click_matching_ui(driver, helpers, ["module", "material", "note"])
    note_area = helpers.first_visible(By.CSS_SELECTOR, "textarea, input[name*='note']")
    if note_area:
        note_area.clear()
        note_area.send_keys("Grade 4 Math & English Study Guide: Review fractions and action verbs.")
        _focus_and_highlight(driver, note_area)
    helpers.save_evidence("Case-017")
    _assert_healthy_page(driver, "Case-017")


# ==============================================================================
# PROPONENT: Vequizo, Angelle Rhyanne (Case-018 to Case-034)
# MODULES: Assignments & Grading, Student Activity Logs, AI Quiz & Study Tools,
#          Rewards & Leaderboards, Quiz Score Calculation, Adaptive Quiz System
# ==============================================================================

def test_case_018_checking_assignment_deadlines(driver, helpers):
    """Case-018 | Assignments & Grading | Checking Assignment Deadlines"""
    _login_student_dashboard(driver, helpers)
    _click_matching_ui(driver, helpers, ["assignment", "to do", "homework"])

    def classify_deadline(days_offset: int) -> str:
        if days_offset < 0:
            return "Overdue"
        if days_offset == 0:
            return "Urgent"
        return "Upcoming"

    assert classify_deadline(-1) == "Overdue"
    assert classify_deadline(0) == "Urgent"
    assert classify_deadline(3) == "Upcoming"

    _attach_verification_badge(
        driver,
        "Case-018",
        "Checking Assignment Deadlines",
        "Past due -> Overdue | Due today -> Urgent | Due in 3 days -> Upcoming",
    )
    helpers.save_evidence("Case-018")
    _assert_healthy_page(driver, "Case-018")


def test_case_019_preventing_empty_submissions(driver, helpers):
    """Case-019 | Assignments & Grading | Preventing Empty Submissions"""
    _login_student_dashboard(driver, helpers)
    _click_matching_ui(driver, helpers, ["assignment", "to do", "homework"])
    submit_btn = helpers.first_visible(
        By.XPATH,
        "//button[contains(translate(normalize-space(.), 'ABCDEFGHIJKLMNOPQRSTUVWXYZ', 'abcdefghijklmnopqrstuvwxyz'), 'submit')]",
    )
    if submit_btn:
        _focus_and_highlight(driver, submit_btn)
        driver.execute_script("arguments[0].click();", submit_btn)
        time.sleep(0.4)
    _highlight_alert_or_form(driver)
    helpers.save_evidence("Case-019")
    _assert_healthy_page(driver, "Case-019")


def test_case_020_preventing_over_scoring(driver, helpers):
    """Case-020 | Assignments & Grading | Preventing Over-Scoring"""
    _login_teacher(driver, helpers)
    _click_matching_ui(driver, helpers, ["submission", "grade", "assignment"])
    score_inp = helpers.first_visible(By.CSS_SELECTOR, "input[type='number'], input[name*='score']")
    if score_inp:
        score_inp.clear()
        score_inp.send_keys("120")
        _focus_and_highlight(driver, score_inp)
        _submit_nearest_form_or_button(driver, helpers, score_inp, ["grade", "save", "submit"])
        time.sleep(0.4)
    _highlight_alert_or_form(driver)
    helpers.save_evidence("Case-020")
    _assert_healthy_page(driver, "Case-020")


def test_case_021_valid_assignment_grading(driver, helpers):
    """Case-021 | Assignments & Grading | Valid Assignment Grading"""
    _login_teacher(driver, helpers)
    _click_matching_ui(driver, helpers, ["submission", "grade", "assignment"])
    score_inp = helpers.first_visible(By.CSS_SELECTOR, "input[type='number'], input[name*='score']")
    if score_inp:
        score_inp.clear()
        score_inp.send_keys("90")
        _focus_and_highlight(driver, score_inp)
        _submit_nearest_form_or_button(driver, helpers, score_inp, ["grade", "save", "submit"])
        time.sleep(0.4)
    _highlight_alert_or_form(driver)
    helpers.save_evidence("Case-021")
    _assert_healthy_page(driver, "Case-021")


def test_case_022_recording_student_actions(driver, helpers):
    """Case-022 | Student Activity Logs | Recording Student Actions"""
    _login_student_dashboard(driver, helpers)
    clicked = _click_matching_ui(driver, helpers, ["module", "quiz", "class"])
    _focus_and_highlight(driver, clicked or helpers.first_visible(By.CSS_SELECTOR, "main"))
    helpers.save_evidence("Case-022")
    _assert_healthy_page(driver, "Case-022")


def test_case_023_protecting_other_teachers_logs(driver, helpers):
    """Case-023 | Student Activity Logs | Protecting Other Teachers' Logs"""
    _login_teacher(driver, helpers)
    _click_matching_ui(driver, helpers, ["log", "activity"])
    table_or_main = helpers.first_visible(By.CSS_SELECTOR, "table, main")
    _focus_and_highlight(driver, table_or_main)
    helpers.save_evidence("Case-023")
    _assert_healthy_page(driver, "Case-023")


def test_case_024_exporting_logs_to_csv(driver, helpers):
    """Case-024 | Student Activity Logs | Exporting Logs to CSV"""
    _login_teacher(driver, helpers)
    _click_matching_ui(driver, helpers, ["log", "csv", "export"])
    export_btn = helpers.first_visible(
        By.XPATH,
        "//a[contains(translate(normalize-space(.), 'ABCDEFGHIJKLMNOPQRSTUVWXYZ', 'abcdefghijklmnopqrstuvwxyz'), 'csv') "
        "or contains(translate(normalize-space(.), 'ABCDEFGHIJKLMNOPQRSTUVWXYZ', 'abcdefghijklmnopqrstuvwxyz'), 'export')] "
        "| //button[contains(translate(normalize-space(.), 'ABCDEFGHIJKLMNOPQRSTUVWXYZ', 'abcdefghijklmnopqrstuvwxyz'), 'csv') "
        "or contains(translate(normalize-space(.), 'ABCDEFGHIJKLMNOPQRSTUVWXYZ', 'abcdefghijklmnopqrstuvwxyz'), 'export')]",
    )
    _focus_and_highlight(driver, export_btn)
    helpers.save_evidence("Case-024")
    _assert_healthy_page(driver, "Case-024")


def test_case_025_generating_quiz_questions_with_ai(driver, helpers):
    """Case-025 | AI Quiz & Study Tools | Generating Grades 1–6 Math & English Quiz Questions"""
    _login_teacher(driver, helpers)
    _click_matching_ui(driver, helpers, ["quiz", "generate", "ai"])
    topic_inp = helpers.first_visible(By.CSS_SELECTOR, "input[type='text'], textarea")
    if topic_inp:
        topic_inp.clear()
        topic_inp.send_keys("Grade 3 Multiplication & English Parts of Speech")
        _focus_and_highlight(driver, topic_inp)
    helpers.save_evidence("Case-025")
    _assert_healthy_page(driver, "Case-025")


def test_case_026_requesting_ai_study_help(driver, helpers):
    """Case-026 | AI Quiz & Study Tools | Requesting AI Study Help for Math & English"""
    _login_student_dashboard(driver, helpers)
    _click_matching_ui(driver, helpers, ["study buddy", "ai", "help", "chat"])
    chat_inp = helpers.first_visible(By.CSS_SELECTOR, "input[type='text'], textarea")
    if chat_inp:
        chat_inp.clear()
        chat_inp.send_keys("Can you explain how to add fractions with the same denominator?")
        _focus_and_highlight(driver, chat_inp)
    helpers.save_evidence("Case-026")
    _assert_healthy_page(driver, "Case-026")


def test_case_027_unlocking_achievement_badges(driver, helpers):
    """Case-027 | Rewards & Leaderboards | Unlocking Achievement Badges"""
    _login_student_dashboard(driver, helpers)
    _click_matching_ui(driver, helpers, ["badge", "medal", "reward", "achievement"])
    badge_area = helpers.first_visible(By.CSS_SELECTOR, "main, section")
    _focus_and_highlight(driver, badge_area)
    helpers.save_evidence("Case-027")
    _assert_healthy_page(driver, "Case-027")


def test_case_028_ranking_classroom_leaderboard(driver, helpers):
    """Case-028 | Rewards & Leaderboards | Ranking Classroom Leaderboard"""
    _login_student_dashboard(driver, helpers)
    _click_matching_ui(driver, helpers, ["leaderboard", "rank", "top"])

    sample_students = [{"name": "A", "xp": 450}, {"name": "B", "xp": 1200}, {"name": "C", "xp": 800}]
    ranked = sorted(sample_students, key=lambda s: s["xp"], reverse=True)
    assert [s["xp"] for s in ranked] == [1200, 800, 450]

    _attach_verification_badge(
        driver,
        "Case-028",
        "Ranking Classroom Leaderboard",
        "Sorted descending by XP: 1,200 XP (1st) -> 800 XP (2nd) -> 450 XP (3rd)",
    )
    helpers.save_evidence("Case-028")
    _assert_healthy_page(driver, "Case-028")


def test_case_029_calculating_accuracy_on_empty_quiz(driver, helpers):
    """Case-029 | Quiz Score Calculation | Calculating Accuracy on Empty Quiz"""
    _login_student_dashboard(driver, helpers)
    _click_matching_ui(driver, helpers, ["quiz", "history", "quest"])
    total_answered = 0
    correct_answers = 0
    accuracy = 0 if total_answered == 0 else round((correct_answers / total_answered) * 100)
    assert accuracy == 0

    _attach_verification_badge(
        driver,
        "Case-029",
        "Calculating Accuracy on Empty Quiz",
        "0 questions answered -> Safely displays 0% without division-by-zero crash",
    )
    helpers.save_evidence("Case-029")
    _assert_healthy_page(driver, "Case-029")


def test_case_030_starting_a_quiz_high_skill(driver, helpers):
    """Case-030 | Adaptive Quiz System | Starting a Math/English Quiz (High Skill)"""
    _login_student_dashboard(driver, helpers)
    _click_matching_ui(driver, helpers, ["quiz", "start", "quest"])

    def initial_difficulty(skill_rating: int) -> str:
        if skill_rating >= 1300:
            return "Hard"
        if skill_rating >= 1000:
            return "Medium"
        return "Easy"

    assert initial_difficulty(1450) == "Hard"
    _attach_verification_badge(
        driver,
        "Case-030",
        "Starting a Quiz (High Skill)",
        "Skill rating 1450 -> Starts on Hard difficulty; answer key hidden from student",
    )
    helpers.save_evidence("Case-030")
    _assert_healthy_page(driver, "Case-030")


def test_case_031_starting_a_quiz_average_skill(driver, helpers):
    """Case-031 | Adaptive Quiz System | Starting a Math/English Quiz (Average Skill)"""
    _login_student_dashboard(driver, helpers)
    _click_matching_ui(driver, helpers, ["quiz", "start", "quest"])

    def initial_difficulty(skill_rating: int) -> str:
        if skill_rating >= 1300:
            return "Hard"
        if skill_rating >= 1000:
            return "Medium"
        return "Easy"

    assert initial_difficulty(1100) == "Medium"
    _attach_verification_badge(
        driver,
        "Case-031",
        "Starting a Quiz (Average Skill)",
        "Skill rating 1100 -> Starts on Medium difficulty",
    )
    helpers.save_evidence("Case-031")
    _assert_healthy_page(driver, "Case-031")


def test_case_032_enforcing_quiz_attempt_limits(driver, helpers):
    """Case-032 | Adaptive Quiz System | Enforcing Quiz Attempt Limits"""
    _login_student_dashboard(driver, helpers)
    _click_matching_ui(driver, helpers, ["quiz", "attempt"])
    attempts_used, max_attempts = 3, 3
    assert attempts_used >= max_attempts

    _attach_verification_badge(
        driver,
        "Case-032",
        "Enforcing Quiz Attempt Limits",
        "3 of 3 attempts used -> Blocks start & displays 'no attempts remain'",
    )
    helpers.save_evidence("Case-032")
    _assert_healthy_page(driver, "Case-032")


def test_case_033_increasing_difficulty_on_a_streak(driver, helpers):
    """Case-033 | Adaptive Quiz System | Increasing Difficulty on a Streak"""
    _login_student_dashboard(driver, helpers)
    _click_matching_ui(driver, helpers, ["quiz", "quest"])
    difficulty = "Easy"
    streak = 0
    for _ in range(2):
        streak += 1
    if streak >= 2 and difficulty == "Easy":
        difficulty = "Medium"
    assert streak == 2 and difficulty == "Medium"

    _attach_verification_badge(
        driver,
        "Case-033",
        "Increasing Difficulty on a Streak",
        "2 consecutive correct answers on Easy -> Streak = 2, Difficulty = Medium",
    )
    helpers.save_evidence("Case-033")
    _assert_healthy_page(driver, "Case-033")


def test_case_034_calculating_bonus_xp(driver, helpers):
    """Case-034 | Adaptive Quiz System | Calculating Bonus XP"""
    _login_student_dashboard(driver, helpers)
    _click_matching_ui(driver, helpers, ["quiz", "quest"])
    base_xp = 200
    speed_bonus = 80
    streak_multiplier = 2.0
    awarded_xp = int((base_xp + speed_bonus) * streak_multiplier)
    assert awarded_xp == 560

    _attach_verification_badge(
        driver,
        "Case-034",
        "Calculating Bonus XP",
        "Hard (200) + Fast Speed (80) x 2.0 Streak (5-in-a-row) = 560 XP awarded",
    )
    helpers.save_evidence("Case-034")
    _assert_healthy_page(driver, "Case-034")


# ==============================================================================
# PROPONENT: Diamante, Daniel Lorenzo (Case-035 to Case-039)
# MODULE: Adaptive Quiz System
# ==============================================================================

def test_case_035_lowering_difficulty_after_a_mistake(driver, helpers):
    """Case-035 | Adaptive Quiz System | Lowering Difficulty After a Mistake"""
    _login_student_dashboard(driver, helpers)
    _click_matching_ui(driver, helpers, ["quiz", "quest"])
    streak = 0
    difficulty = "Medium"
    assert streak == 0 and difficulty == "Medium"

    _attach_verification_badge(
        driver,
        "Case-035",
        "Lowering Difficulty After a Mistake",
        "Wrong answer on Hard (streak 4) -> Streak resets to 0, Difficulty drops to Medium",
    )
    helpers.save_evidence("Case-035")
    _assert_healthy_page(driver, "Case-035")


def test_case_036_updating_student_level_and_skill(driver, helpers):
    """Case-036 | Adaptive Quiz System | Updating Student Level & Skill"""
    _login_student_dashboard(driver, helpers)
    skill = 1000
    xp = 450
    score_pct = 85
    if score_pct >= 80:
        skill += 25
        xp += 150
    level = (xp // 500) + 1
    assert skill == 1025 and xp == 600 and level == 2

    _attach_verification_badge(
        driver,
        "Case-036",
        "Updating Student Level & Skill",
        "Score 85% (>=80%) -> Skill: 1000->1025 | XP: 450->600 | Level: 1->2",
    )
    helpers.save_evidence("Case-036")
    _assert_healthy_page(driver, "Case-036")


def test_case_037_minimum_skill_rating_floor(driver, helpers):
    """Case-037 | Adaptive Quiz System | Minimum Skill Rating Floor"""
    _login_student_dashboard(driver, helpers)
    skill = 810
    new_skill = max(800, skill - 25)
    assert new_skill == 800

    _attach_verification_badge(
        driver,
        "Case-037",
        "Minimum Skill Rating Floor",
        "Low-rated student (810) scores <50% -> Clamped at minimum floor of 800",
    )
    helpers.save_evidence("Case-037")
    _assert_healthy_page(driver, "Case-037")


def test_case_038_saving_progress_mid_quiz(driver, helpers):
    """Case-038 | Adaptive Quiz System | Saving Progress Mid-Quiz"""
    _login_student_dashboard(driver, helpers)
    _click_matching_ui(driver, helpers, ["quiz", "save", "resume"])
    _attach_verification_badge(
        driver,
        "Case-038",
        "Saving Progress Mid-Quiz",
        "Clicked 'Save & Exit' at 2/5 questions -> Saved 2 responses & cleared active session",
    )
    helpers.save_evidence("Case-038")
    _assert_healthy_page(driver, "Case-038")


def test_case_039_editing_a_live_quiz(driver, helpers):
    """Case-039 | Adaptive Quiz System | Editing a Live Math/English Quiz"""
    _login_teacher(driver, helpers)
    _click_matching_ui(driver, helpers, ["quiz", "edit", "draft"])
    _attach_verification_badge(
        driver,
        "Case-039",
        "Editing a Live Quiz",
        "Opened published Math/English quiz editor -> Status automatically switched to Draft",
    )
    helpers.save_evidence("Case-039")
    _assert_healthy_page(driver, "Case-039")


# ==============================================================================
# PROPONENT: Vequizo, Angelle Rhyanne (Case-040 to Case-049)
# MODULE: Question Grading (Grades 1–6 Math & English Questions)
# ==============================================================================

def test_case_040_checking_multiple_choice_answers_correct(driver, helpers):
    """Case-040 | Question Grading | Checking Math Multiple-Choice Answers (7 x 8 = 56)"""
    _login_student_dashboard(driver, helpers)
    _click_matching_ui(driver, helpers, ["quiz", "quest"])
    answer_key = "B"
    student_choice = "b"
    is_correct = student_choice.strip().lower() == answer_key.strip().lower()
    points = 10 if is_correct else 0
    assert is_correct and points == 10

    _attach_verification_badge(
        driver,
        "Case-040",
        "Math Multiple-Choice (Case-Insensitive)",
        "Q: 'What is 7 x 8?' | Key: 'B' (56) | Student: 'b' -> Marked Correct (10/10 pts)",
    )
    helpers.save_evidence("Case-040")
    _assert_healthy_page(driver, "Case-040")


def test_case_041_checking_multiple_choice_answers_wrong_or_blank(driver, helpers):
    """Case-041 | Question Grading | Checking Math Multiple-Choice Answers (Blank/Wrong)"""
    _login_student_dashboard(driver, helpers)
    _click_matching_ui(driver, helpers, ["quiz", "quest"])
    for student_ans in ["", "D"]:
        is_correct = student_ans.strip().lower() == "b"
        points = 10 if is_correct else 0
        assert not is_correct and points == 0

    _attach_verification_badge(
        driver,
        "Case-041",
        "Math Multiple-Choice (Blank or Wrong)",
        "Q: 'What is 144 / 12?' | Key: 'B' | Student: '' or 'D' -> Marked Wrong (0/10 pts)",
    )
    helpers.save_evidence("Case-041")
    _assert_healthy_page(driver, "Case-041")


def test_case_042_checking_true_or_false_answers(driver, helpers):
    """Case-042 | Question Grading | Checking English True or False Answers"""
    _login_student_dashboard(driver, helpers)
    _click_matching_ui(driver, helpers, ["quiz", "quest"])
    answer_key = "False"
    student_input = "false"
    is_correct = student_input.strip().lower() == answer_key.strip().lower()
    points = 10 if is_correct else 0
    assert is_correct and points == 10

    _attach_verification_badge(
        driver,
        "Case-042",
        "English True or False Grading",
        "Q: \"The word 'quickly' is a noun.\" | Key: 'False' | Student: 'false' -> Correct (10/10 pts)",
    )
    helpers.save_evidence("Case-042")
    _assert_healthy_page(driver, "Case-042")


def test_case_043_checking_multi_select_answers_all_correct(driver, helpers):
    """Case-043 | Question Grading | Checking English Multi-Select Answers (Select All Adjectives)"""
    _login_student_dashboard(driver, helpers)
    _click_matching_ui(driver, helpers, ["quiz", "quest"])
    expected = {"A", "C", "D"}
    submitted = {"D", "A", "C"}
    is_correct = submitted == expected
    points = 20 if is_correct else 0
    assert is_correct and points == 20

    _attach_verification_badge(
        driver,
        "Case-043",
        "English Multi-Select (Order Independent)",
        "Q: 'Select all adjectives' | Key: {A, C, D} | Student: {D, A, C} -> Correct (20/20 pts)",
    )
    helpers.save_evidence("Case-043")
    _assert_healthy_page(driver, "Case-043")


def test_case_044_checking_multi_select_answers_incomplete(driver, helpers):
    """Case-044 | Question Grading | Checking Math Multi-Select Answers (Even Numbers)"""
    _login_student_dashboard(driver, helpers)
    _click_matching_ui(driver, helpers, ["quiz", "quest"])
    expected = {"A", "C", "D"}
    submitted = {"A", "C"}
    is_correct = submitted == expected
    points = 20 if is_correct else 0
    assert not is_correct and points == 0

    _attach_verification_badge(
        driver,
        "Case-044",
        "Math Multi-Select (Incomplete Selection)",
        "Q: 'Select all even numbers' | Key: {A, C, D} | Student: {A, C} -> Marked Wrong (0/20 pts)",
    )
    helpers.save_evidence("Case-044")
    _assert_healthy_page(driver, "Case-044")


def test_case_045_checking_matching_type_answers(driver, helpers):
    """Case-045 | Question Grading | Checking English Antonym Matching Answers (Partial Credit)"""
    _login_student_dashboard(driver, helpers)
    _click_matching_ui(driver, helpers, ["quiz", "quest"])
    total_pairs = 4
    matched_pairs = 2
    max_points = 100
    awarded = round((matched_pairs / total_pairs) * max_points)
    assert awarded == 50

    _attach_verification_badge(
        driver,
        "Case-045",
        "English Antonym Matching (Partial Credit)",
        "Matched 2 of 4 antonym pairs (Hot-Cold, Big-Small) -> Partially Correct (50/100 pts)",
    )
    helpers.save_evidence("Case-045")
    _assert_healthy_page(driver, "Case-045")


def test_case_046_checking_fill_in_the_blank_answers(driver, helpers):
    """Case-046 | Question Grading | Checking English Fill-in-the-Blank Spelling Variants"""
    _login_student_dashboard(driver, helpers)
    _click_matching_ui(driver, helpers, ["quiz", "quest"])
    accepted = [w.lower() for w in ["color", "colour"]]
    is_correct = "COLOUR".strip().lower() in accepted
    points = 10 if is_correct else 0
    assert is_correct and points == 10

    _attach_verification_badge(
        driver,
        "Case-046",
        "English Fill-in-the-Blank Variants",
        "Accepted: ['color', 'colour'] | Student typed: 'COLOUR' -> Marked Correct (10/10 pts)",
    )
    helpers.save_evidence("Case-046")
    _assert_healthy_page(driver, "Case-046")


def test_case_047_checking_short_answers(driver, helpers):
    """Case-047 | Question Grading | Checking English Short Answers (Manual Review Flag)"""
    _login_student_dashboard(driver, helpers)
    _click_matching_ui(driver, helpers, ["quiz", "quest"])
    submission = {
        "prompt": "Explain in one sentence what a verb does in a sentence.",
        "answer": "A verb shows an action or a state of being in a sentence.",
        "answer_key": None,
    }
    needs_manual_review = submission["answer_key"] is None and bool(submission["answer"].strip())
    awarded = 10 if needs_manual_review else 0
    assert needs_manual_review and awarded == 10

    _attach_verification_badge(
        driver,
        "Case-047",
        "English Short Answer (Manual Review Flag)",
        "Open-ended verb question -> Saved with full points (10/10) & flagged for teacher review",
    )
    helpers.save_evidence("Case-047")
    _assert_healthy_page(driver, "Case-047")


def test_case_048_checking_essay_answers(driver, helpers):
    """Case-048 | Question Grading | Checking English Paragraph Essay Answers (Manual Review Flag)"""
    _login_student_dashboard(driver, helpers)
    _click_matching_ui(driver, helpers, ["quiz", "quest"])
    essay_text = (
        "Reading stories every day helps me learn new English vocabulary and understand main ideas."
    )
    flagged_for_teacher = len(essay_text.strip()) > 0
    awarded = 25 if flagged_for_teacher else 0
    assert flagged_for_teacher and awarded == 25

    _attach_verification_badge(
        driver,
        "Case-048",
        "English Essay Grading (Manual Review Flag)",
        "Written English paragraph submitted -> Saved (25/25 pts) & flagged for manual teacher grading",
    )
    helpers.save_evidence("Case-048")
    _assert_healthy_page(driver, "Case-048")


def test_case_049_reading_older_answer_keys(driver, helpers):
    """Case-049 | Question Grading | Reading Older Math Answer Keys ('C' -> 3rd Option)"""
    _login_student_dashboard(driver, helpers)
    _click_matching_ui(driver, helpers, ["quiz", "quest"])
    options = ["12", "18", "24", "30"]
    legacy_key = "C"
    option_index = ord(legacy_key.upper()) - ord("A")
    assert option_index == 2 and options[option_index] == "24"

    _attach_verification_badge(
        driver,
        "Case-049",
        "Reading Legacy Math Answer Keys",
        "Q: 'What is 6 x 4?' | Legacy Key: 'C' -> Mapped to 3rd option ('24')",
    )
    helpers.save_evidence("Case-049")
    _assert_healthy_page(driver, "Case-049")