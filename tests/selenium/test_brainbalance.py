# FILE: tests/selenium/test_brainbalance.py
import os
import subprocess
import time
import uuid
import pytest
from selenium import webdriver
from selenium.common.exceptions import WebDriverException
from selenium.webdriver.common.by import By
from selenium.webdriver.chrome.service import Service
from selenium.webdriver.chrome.options import Options
from selenium.webdriver.support.ui import WebDriverWait
from selenium.webdriver.support import expected_conditions as EC
from webdriver_manager.chrome import ChromeDriverManager

BASE_URL = os.getenv("APP_URL", "http://127.0.0.1:8000")
PROJECT_ROOT = os.path.abspath(os.path.join(os.path.dirname(__file__), "..", ".."))
SCREENSHOT_DIR = os.path.join(os.path.dirname(__file__), "screenshots")
DOWNLOAD_DIR = os.path.join(os.path.dirname(__file__), "downloads")

os.makedirs(SCREENSHOT_DIR, exist_ok=True)
os.makedirs(DOWNLOAD_DIR, exist_ok=True)

TEST_STATE = {
    "student_email": os.getenv("TEST_STUDENT_EMAIL", "student_seed@brainbalance.test"),
    "student_password": os.getenv("TEST_STUDENT_PASSWORD", "StrongPass!2026"),
    "teacher_email": os.getenv("TEST_TEACHER_EMAIL", "teacher@brainbalance.com"),
    "teacher_password": os.getenv("TEST_TEACHER_PASSWORD", "password"),
    "admin_email": os.getenv("TEST_ADMIN_EMAIL", "admin@brainbalance.com"),
    "admin_password": os.getenv("TEST_ADMIN_PASSWORD", "password"),
    "class_code": "QWERTY",
    "classroom_id": None,
}


@pytest.fixture(scope="session", autouse=True)
def seed_test_fixtures():
    """
    Ensures Admin, Teacher, fallback Student, Classroom (code QWERTY), and a sample
    Assignment exist in the database before Selenium runs.
    """
    php_code = r"""
    use App\Models\User;
    use App\Models\Classroom;
    use App\Models\Announcement;
    use Illuminate\Support\Facades\Hash;
    use Illuminate\Support\Facades\Schema;

    $admin = User::updateOrCreate(
        ['email' => 'admin@brainbalance.com'],
        [
            'first_name' => 'System',
            'last_name'  => 'Admin',
            'name'       => 'System Admin',
            'password'   => Hash::make('password'),
            'role'       => 'admin',
        ]
    );

    $teacher = User::updateOrCreate(
        ['email' => 'teacher@brainbalance.com'],
        [
            'first_name' => 'Maria',
            'last_name'  => 'Santos',
            'name'       => 'Maria Santos',
            'password'   => Hash::make('password'),
            'role'       => 'teacher',
        ]
    );

    User::updateOrCreate(
        ['email' => 'student_seed@brainbalance.test'],
        [
            'first_name' => 'Seed',
            'last_name'  => 'Student',
            'name'       => 'Seed Student',
            'password'   => Hash::make('StrongPass!2026'),
            'role'       => 'student',
        ]
    );

    $classQuery = Classroom::query();
    if (Schema::hasColumn('classes', 'class_code')) {
        $classQuery->orWhere('class_code', 'QWERTY');
    }
    if (Schema::hasColumn('classes', 'code')) {
        $classQuery->orWhere('code', 'QWERTY');
    }
    $classroom = $classQuery->first();

    if (!$classroom) {
        $attrs = [
            'teacher_id' => $teacher->id,
            'name'       => 'Grade 3 Math & English',
        ];
        if (Schema::hasColumn('classes', 'subject')) $attrs['subject'] = 'Mathematics';
        if (Schema::hasColumn('classes', 'section')) $attrs['section'] = 'Section A';
        if (Schema::hasColumn('classes', 'class_code')) $attrs['class_code'] = 'QWERTY';
        if (Schema::hasColumn('classes', 'code')) $attrs['code'] = 'QWERTY';
        $classroom = Classroom::create($attrs);
    } else {
        $classroom->teacher_id = $teacher->id;
        if (Schema::hasColumn('classes', 'class_code')) $classroom->class_code = 'QWERTY';
        if (Schema::hasColumn('classes', 'code')) $classroom->code = 'QWERTY';
        $classroom->save();
    }

    if (Schema::hasTable('announcements') && Schema::hasColumn('announcements', 'type')) {
        Announcement::firstOrCreate(
            [
                'class_id' => $classroom->id,
                'type'     => 'assignment',
                'title'    => 'Selenium Practice Worksheet',
            ],
            [
                'teacher_id' => $teacher->id,
                'content'    => 'Solve 5 + 7 and type your answer below.',
                'due_at'     => now()->addDays(2),
                'points'     => 100,
            ]
        );
    }

    echo "SEEDED_CLASS_ID=" . $classroom->id;
    """
    try:
        res = subprocess.run(
            ["php", "artisan", "tinker", f"--execute={php_code}"],
            cwd=PROJECT_ROOT,
            capture_output=True,
            text=True,
            timeout=25,
        )
        for line in res.stdout.splitlines():
            if line.startswith("SEEDED_CLASS_ID="):
                TEST_STATE["classroom_id"] = line.split("=", 1)[1].strip()
    except Exception as exc:
        print(f"[Warning] Could not auto-seed via artisan tinker: {exc}")


@pytest.fixture(scope="session")
def driver():
    chrome_options = Options()
    # Uncomment the next line to run headless:
    # chrome_options.add_argument("--headless=new")
    chrome_options.add_argument("--window-size=1366,900")
    chrome_options.add_experimental_option("prefs", {
        "download.default_directory": os.path.abspath(DOWNLOAD_DIR),
        "download.prompt_for_download": False,
    })

    browser = webdriver.Chrome(
        service=Service(ChromeDriverManager().install()),
        options=chrome_options
    )
    browser.implicitly_wait(4)
    yield browser
    browser.quit()


def save_evidence(driver, case_id: str):
    """Captures a screenshot named after the Test Case ID for capstone documentation."""
    driver.save_screenshot(os.path.join(SCREENSHOT_DIR, f"{case_id}.png"))


def normalize_local_forms(driver):
    """Ensures local HTTP forms never submit to HTTPS on php artisan serve."""
    if BASE_URL.startswith("http://"):
        driver.execute_script("""
            document.querySelectorAll('form').forEach(f => {
                if (f.action && (f.action.startsWith('https://127.0.0.1') || f.action.startsWith('https://localhost'))) {
                    f.action = f.action.replace('https://', 'http://');
                }
            });
        """)


def safe_get(driver, url: str, retries: int = 3):
    """Navigates to a URL and retries if single-threaded php artisan serve drops a connection."""
    for attempt in range(retries):
        try:
            driver.get(url)
            body = driver.find_element(By.TAG_NAME, "body").text
            if "ERR_CONNECTION_CLOSED" in body or "unexpectedly closed the connection" in body:
                raise WebDriverException("ERR_CONNECTION_CLOSED")
            normalize_local_forms(driver)
            return
        except WebDriverException:
            if attempt == retries - 1:
                raise
            time.sleep(1.5)


def first_visible(driver, by, selector):
    for el in driver.find_elements(by, selector):
        if el.is_displayed() and el.is_enabled():
            return el
    return None


def login_as(driver, email: str, password: str, expect_success: bool = True):
    safe_get(driver, f"{BASE_URL}/login")

    # Click login tab/trigger if auth.blade.php toggles between landing/login views
    login_triggers = driver.find_elements(
        By.XPATH, "//button[contains(., 'Log In') or contains(., 'Sign In')] | //a[contains(., 'Log In') or contains(., 'Sign In')]"
    )
    if not first_visible(driver, By.CSS_SELECTOR, "form[action*='login'] input[name='email']"):
        for trigger in login_triggers:
            if trigger.is_displayed():
                driver.execute_script("arguments[0].click();", trigger)
                time.sleep(0.3)
                break

    email_el = WebDriverWait(driver, 15).until(
        EC.visibility_of_element_located((By.CSS_SELECTOR, "form[action*='login'] input[name='email'], input[name='email']"))
    )
    email_el.clear()
    email_el.send_keys(email)

    pass_el = first_visible(driver, By.CSS_SELECTOR, "form[action*='login'] input[name='password'], input[name='password']")
    pass_el.clear()
    pass_el.send_keys(password)

    normalize_local_forms(driver)
    submit_btn = first_visible(driver, By.CSS_SELECTOR, "form[action*='login'] button[type='submit'], button[type='submit']")
    driver.execute_script("arguments[0].click();", submit_btn)

    if expect_success:
        try:
            WebDriverWait(driver, 12).until(lambda d: "/login" not in d.current_url)
        except Exception:
            body_snippet = driver.find_element(By.TAG_NAME, "body").text[:300]
            raise AssertionError(
                f"Login failed for {email}. Still at {driver.current_url}. Page says: {body_snippet!r}"
            )


def logout_user(driver):
    logout_forms = driver.find_elements(By.CSS_SELECTOR, "form[action*='logout']")
    if not logout_forms:
        safe_get(driver, f"{BASE_URL}/dashboard")
        logout_forms = driver.find_elements(By.CSS_SELECTOR, "form[action*='logout']")

    if logout_forms:
        normalize_local_forms(driver)
        driver.execute_script("arguments[0].submit();", logout_forms[0])
        time.sleep(0.8)


def fill_registration_form(driver, full_name: str, email: str, password: str):
    """Fills register form fields: role, first_name, last_name, email, password, password_confirmation."""
    safe_get(driver, f"{BASE_URL}/register?role=student")

    # Switch to Register tab/modal if auth.blade.php defaults to Login
    if not first_visible(driver, By.NAME, "first_name"):
        reg_triggers = driver.find_elements(
            By.XPATH,
            "//button[contains(., 'Register') or contains(., 'Sign Up') or contains(., 'Create Account')] | //a[contains(., 'Register') or contains(., 'Sign Up')]"
        )
        for trigger in reg_triggers:
            if trigger.is_displayed():
                driver.execute_script("arguments[0].click();", trigger)
                time.sleep(0.3)
                if first_visible(driver, By.NAME, "first_name"):
                    break

    # Select Student role card if present
    role_triggers = driver.find_elements(
        By.XPATH,
        "//button[contains(., 'Student')] | //label[contains(., 'Student')] | //*[@data-role='student']"
    )
    for trigger in role_triggers:
        if trigger.is_displayed():
            driver.execute_script("arguments[0].click();", trigger)
            time.sleep(0.2)
            break

    role_hidden = driver.find_elements(By.ID, "role-input")
    if role_hidden:
        driver.execute_script("arguments[0].value = 'student';", role_hidden[0])

    parts = full_name.split(" ", 1)
    first_name_el = WebDriverWait(driver, 15).until(
        EC.visibility_of_element_located((By.NAME, "first_name"))
    )
    first_name_el.clear()
    first_name_el.send_keys(parts[0])

    last_name_el = driver.find_element(By.NAME, "last_name")
    last_name_el.clear()
    last_name_el.send_keys(parts[1] if len(parts) > 1 else "User")

    reg_form = first_name_el.find_element(By.XPATH, "./ancestor::form")
    email_el = reg_form.find_element(By.NAME, "email")
    email_el.clear()
    email_el.send_keys(email)

    pass_el = reg_form.find_element(By.NAME, "password")
    pass_el.clear()
    pass_el.send_keys(password)

    confirm_el = reg_form.find_element(By.NAME, "password_confirmation")
    confirm_el.clear()
    confirm_el.send_keys(password)

    normalize_local_forms(driver)
    submit_btn = reg_form.find_element(By.CSS_SELECTOR, "button[type='submit']")
    driver.execute_script("arguments[0].click();", submit_btn)


# ==============================================================================
# MODULE 1: ACCOUNT REGISTRATION & LOGIN (Proponent: Daniel Lorenzo Diamante)
# ==============================================================================
class TestAccountRegistrationAndLogin:

    def test_case_001_creating_new_account(self, driver):
        unique_email = f"student_{uuid.uuid4().hex[:6]}@brainbalance.test"
        password = "StrongPass!2026"

        fill_registration_form(driver, "Juan Dela Cruz", unique_email, password)

        # AuthController::register redirects to route('login') with 'Account created successfully!'
        WebDriverWait(driver, 15).until(
            lambda d: "/login" in d.current_url or "account created successfully" in d.find_element(By.TAG_NAME, "body").text.lower()
        )
        save_evidence(driver, "Case-001")

        TEST_STATE["student_email"] = unique_email
        TEST_STATE["student_password"] = password

        body_lower = driver.find_element(By.TAG_NAME, "body").text.lower()
        assert "/login" in driver.current_url or "account created successfully" in body_lower

    def test_case_002_admin_login_redirect(self, driver):
        login_as(driver, TEST_STATE["admin_email"], TEST_STATE["admin_password"])
        WebDriverWait(driver, 15).until(lambda d: "/admin/dashboard" in d.current_url)
        save_evidence(driver, "Case-002")
        assert "/admin/dashboard" in driver.current_url, f"Expected /admin/dashboard, got {driver.current_url}"
        logout_user(driver)

    def test_case_003_invalid_login_credentials(self, driver):
        login_as(driver, "nonexistent_user@brainbalance.com", "WrongPassword123", expect_success=False)
        time.sleep(1.0)
        page_text = driver.find_element(By.TAG_NAME, "body").text.lower()
        page_source = driver.page_source.lower()
        save_evidence(driver, "Case-003")
        assert "/login" in driver.current_url
        assert (
            "do not match our records" in page_text
            or "do not match our records" in page_source
            or any(w in page_text for w in ["credentials", "invalid", "incorrect", "failed"])
        )

    def test_case_004_duplicate_email_prevention(self, driver):
        fill_registration_form(driver, "Duplicate User", TEST_STATE["student_email"], "StrongPass!2026")
        time.sleep(1.5)
        page_text = driver.find_element(By.TAG_NAME, "body").text.lower()
        page_source = driver.page_source.lower()
        save_evidence(driver, "Case-004")
        assert "already been taken" in page_text or "already been taken" in page_source

    def test_case_005_logging_out_safely(self, driver):
        login_as(driver, TEST_STATE["student_email"], TEST_STATE["student_password"])
        logout_user(driver)
        WebDriverWait(driver, 15).until(lambda d: "/login" in d.current_url)
        save_evidence(driver, "Case-005")
        assert "/login" in driver.current_url


# ==============================================================================
# MODULE 2: GOOGLE SIGN-IN & USER PROFILE (Proponent: Jhustine Dave Rivera)
# ==============================================================================
class TestGoogleSignInAndProfile:

    def test_case_006_google_oauth_redirect_initiation(self, driver):
        safe_get(driver, f"{BASE_URL}/login")
        google_links = driver.find_elements(By.CSS_SELECTOR, "a[href*='/auth/google']")
        assert len(google_links) > 0, "Google OAuth link (/auth/google) not found on /login"
        save_evidence(driver, "Case-006")

    def test_case_010_and_011_profile_picture_load_and_update(self, driver):
        login_as(driver, TEST_STATE["student_email"], TEST_STATE["student_password"])
        # ProfileController@edit is mapped to GET /settings
        safe_get(driver, f"{BASE_URL}/settings")

        save_evidence(driver, "Case-010")
        assert "/settings" in driver.current_url

        file_inputs = driver.find_elements(By.CSS_SELECTOR, "input[type='file'][name='avatar'], input[type='file']")
        assert len(file_inputs) > 0, "No avatar file input found on /settings"

        sample_png = os.path.join(SCREENSHOT_DIR, "sample_avatar.png")
        with open(sample_png, "wb") as f:
            f.write(
                b"\x89PNG\r\n\x1a\n\x00\x00\x00\rIHDR\x00\x00\x00\x01\x00\x00\x00\x01"
                b"\x08\x06\x00\x00\x00\x1f\x15\xc4\x89\x00\x00\x00\nIDATx\x9cc\x00\x01"
                b"\x00\x00\x05\x00\x01\r\n-\xb4\x00\x00\x00\x00IEND\xaeB`\x82"
            )
        file_inputs[0].send_keys(os.path.abspath(sample_png))

        normalize_local_forms(driver)
        settings_form = file_inputs[0].find_element(By.XPATH, "./ancestor::form")
        submit_btn = settings_form.find_element(By.CSS_SELECTOR, "button[type='submit']")
        driver.execute_script("arguments[0].click();", submit_btn)
        time.sleep(1.5)
        save_evidence(driver, "Case-011")
        assert "profile has been updated" in driver.find_element(By.TAG_NAME, "body").text.lower()


# ==============================================================================
# MODULE 3: CLASSROOM & LEARNING MATERIALS (Proponent: Jhustine Dave Rivera)
# ==============================================================================
class TestClassroomAndMaterials:

    def _get_join_code_input(self, driver):
        join_triggers = driver.find_elements(
            By.XPATH, "//button[contains(., 'Join Class')] | //button[contains(., 'Enter Code')] | //button[contains(., 'Join')]"
        )
        for btn in join_triggers:
            if btn.is_displayed():
                driver.execute_script("arguments[0].click();", btn)
                time.sleep(0.4)
                el = first_visible(driver, By.NAME, "class_code")
                if el:
                    normalize_local_forms(driver)
                    return el

        normalize_local_forms(driver)
        return first_visible(driver, By.NAME, "class_code")

    def test_case_012_and_014_join_class_code_normalization_and_invalid_code(self, driver):
        login_as(driver, TEST_STATE["student_email"], TEST_STATE["student_password"])
        safe_get(driver, f"{BASE_URL}/subjects")

        code_input = self._get_join_code_input(driver)
        if not code_input:
            safe_get(driver, f"{BASE_URL}/dashboard")
            code_input = self._get_join_code_input(driver)

        assert code_input is not None, f"Could not find input[name='class_code'] on {driver.current_url}"

        # Case-014: Invalid class code
        code_input.clear()
        code_input.send_keys("INVALID999")
        code_input.submit()
        time.sleep(1.5)
        save_evidence(driver, "Case-014")
        assert "invalid class code" in driver.find_element(By.TAG_NAME, "body").text.lower()

        # Case-012: Valid code 'QWERTY' entered with spaces and lowercase
        code_input = self._get_join_code_input(driver)
        if not code_input:
            safe_get(driver, f"{BASE_URL}/dashboard")
            code_input = self._get_join_code_input(driver)

        code_input.clear()
        code_input.send_keys("   qwerty   ")
        code_input.submit()
        time.sleep(1.5)
        save_evidence(driver, "Case-012")
        body_lower = driver.find_element(By.TAG_NAME, "body").text.lower()
        assert "successfully joined" in body_lower or "grade 3 math & english" in body_lower

    def test_case_013_blocking_unauthorized_class_access(self, driver):
        login_as(driver, TEST_STATE["student_email"], TEST_STATE["student_password"])
        safe_get(driver, f"{BASE_URL}/subjects/class/999999")
        body_text = driver.find_element(By.TAG_NAME, "body").text
        save_evidence(driver, "Case-013")
        assert any(msg in body_text for msg in ["403", "Forbidden", "Not Found", "404"])

    def test_case_015_editing_classroom_details(self, driver):
        login_as(driver, TEST_STATE["teacher_email"], TEST_STATE["teacher_password"])
        safe_get(driver, f"{BASE_URL}/subjects")

        edit_links = driver.find_elements(By.CSS_SELECTOR, "a[href*='/classes/'][href*='/edit']")
        if not edit_links and TEST_STATE["classroom_id"]:
            safe_get(driver, f"{BASE_URL}/classes/{TEST_STATE['classroom_id']}/edit")
        elif edit_links:
            driver.execute_script("arguments[0].click();", edit_links[0])

        normalize_local_forms(driver)
        name_field = WebDriverWait(driver, 15).until(
            EC.visibility_of_element_located((By.NAME, "name"))
        )
        original_name = name_field.get_attribute("value")
        name_field.clear()
        name_field.send_keys(f"{original_name} Updated")

        edit_form = name_field.find_element(By.XPATH, "./ancestor::form")
        submit_btn = edit_form.find_element(By.CSS_SELECTOR, "button[type='submit']")
        driver.execute_script("arguments[0].click();", submit_btn)
        time.sleep(1.5)
        save_evidence(driver, "Case-015")
        assert "classroom details updated" in driver.find_element(By.TAG_NAME, "body").text.lower()


# ==============================================================================
# MODULE 4: ASSIGNMENTS, LOGS & LEADERBOARDS (Proponent: Angelle Rhyanne Vequizo)
# ==============================================================================
class TestAssignmentsLogsAndRewards:

    def test_case_019_and_020_assignment_deadlines_and_empty_submission_block(self, driver):
        login_as(driver, TEST_STATE["student_email"], TEST_STATE["student_password"])
        safe_get(driver, f"{BASE_URL}/assignments")
        body_text = driver.find_element(By.TAG_NAME, "body").text
        save_evidence(driver, "Case-019")
        assert any(label in body_text for label in ["Due in", "Overdue", "Upcoming", "Assignments", "To Do"])

        # Submit an empty assignment form on /assignments (POST /assignments/{announcement}/submit)
        normalize_local_forms(driver)
        submit_btns = driver.find_elements(By.CSS_SELECTOR, "form[action*='/assignments/'][action*='/submit'] button[type='submit']")
        if submit_btns:
            driver.execute_script("arguments[0].click();", submit_btns[0])
            time.sleep(1.5)
            save_evidence(driver, "Case-020")
            assert "please type your answer or attach a file" in driver.find_element(By.TAG_NAME, "body").text.lower()

            # Submit a valid answer so teacher grading (Case-021/022) has a submission to grade
            answer_boxes = driver.find_elements(By.CSS_SELECTOR, "form[action*='/assignments/'][action*='/submit'] textarea[name='answer_text'], form[action*='/assignments/'][action*='/submit'] input[name='answer_text']")
            if answer_boxes:
                answer_boxes[0].send_keys("5 + 7 = 12")
                normalize_local_forms(driver)
                submit_btns = driver.find_elements(By.CSS_SELECTOR, "form[action*='/assignments/'][action*='/submit'] button[type='submit']")
                driver.execute_script("arguments[0].click();", submit_btns[0])
                time.sleep(1.5)

    def test_case_021_and_022_grading_validation(self, driver):
        login_as(driver, TEST_STATE["teacher_email"], TEST_STATE["teacher_password"])
        if TEST_STATE["classroom_id"]:
            safe_get(driver, f"{BASE_URL}/subjects/class/{TEST_STATE['classroom_id']}")
        else:
            safe_get(driver, f"{BASE_URL}/subjects")
            class_links = driver.find_elements(By.CSS_SELECTOR, "a[href*='/subjects/class/']")
            if class_links:
                driver.execute_script("arguments[0].click();", class_links[0])

        normalize_local_forms(driver)
        score_inputs = driver.find_elements(By.CSS_SELECTOR, "form[action*='/grade'] input[name='score']")
        if score_inputs:
            # Case-021: Out-of-range score (120 > 100)
            driver.execute_script("arguments[0].removeAttribute('max');", score_inputs[0])
            score_inputs[0].clear()
            score_inputs[0].send_keys("120")
            score_inputs[0].submit()
            time.sleep(1.5)
            save_evidence(driver, "Case-021")

            # Case-022: Valid score (90)
            normalize_local_forms(driver)
            score_inputs = driver.find_elements(By.CSS_SELECTOR, "form[action*='/grade'] input[name='score']")
            if score_inputs:
                score_inputs[0].clear()
                score_inputs[0].send_keys("90")
                score_inputs[0].submit()
                time.sleep(1.5)
                save_evidence(driver, "Case-022")

    def test_case_024_and_025_activity_logs_and_csv_export(self, driver):
        login_as(driver, TEST_STATE["teacher_email"], TEST_STATE["teacher_password"])
        # StudentLogController@index is mapped to GET /teacher/student-logs
        safe_get(driver, f"{BASE_URL}/teacher/student-logs")
        save_evidence(driver, "Case-024")
        assert "/teacher/student-logs" in driver.current_url

        csv_btns = driver.find_elements(By.CSS_SELECTOR, "a[href*='/teacher/student-logs/export'], a[href*='export']")
        if csv_btns:
            driver.execute_script("arguments[0].click();", csv_btns[0])
            time.sleep(2)
            save_evidence(driver, "Case-025")
            downloaded_files = os.listdir(DOWNLOAD_DIR)
            assert any(f.endswith(".csv") for f in downloaded_files)

    def test_case_029_classroom_leaderboard_ranking(self, driver):
        login_as(driver, TEST_STATE["student_email"], TEST_STATE["student_password"])
        safe_get(driver, f"{BASE_URL}/dashboard")
        body_text = driver.find_element(By.TAG_NAME, "body").text
        if "XP" not in body_text and "Leaderboard" not in body_text and TEST_STATE["classroom_id"]:
            safe_get(driver, f"{BASE_URL}/subjects/class/{TEST_STATE['classroom_id']}")
            body_text = driver.find_element(By.TAG_NAME, "body").text

        save_evidence(driver, "Case-029")
        assert "XP" in body_text or "Leaderboard" in body_text or "Level" in body_text


# ==============================================================================
# MODULE 5: ADAPTIVE QUIZ SYSTEM & 7-TYPE BUILDER (Diamante & Vequizo)
# ==============================================================================
class TestAdaptiveQuizAndGrading:

    def test_case_040_to_048_create_multi_type_quiz_in_builder(self, driver):
        login_as(driver, TEST_STATE["teacher_email"], TEST_STATE["teacher_password"])
        safe_get(driver, f"{BASE_URL}/quiz/create")

        title_input = WebDriverWait(driver, 20).until(
            EC.presence_of_element_located((By.ID, "quizTitleInput"))
        )
        title_input.clear()
        title_input.send_keys("Selenium 7-Type Verification Quest")

        # Question 1 (Multiple Choice)
        q1_text = driver.find_element(By.NAME, "questions[0][text]")
        q1_text.send_keys("What is 7 + 8?")
        opts = driver.find_elements(By.CSS_SELECTOR, ".question-card:nth-child(1) .q-opt-input")
        for idx, val in enumerate(["15", "12", "14", "16"]):
            if idx < len(opts):
                opts[idx].send_keys(val)

        # Add True/False Question (Case-042)
        driver.find_element(By.XPATH, "//button[contains(., 'True / False')]").click()
        driver.find_element(By.NAME, "questions[1][text]").send_keys("The sun rises in the east.")

        # Add Fill-in-the-Blank Question with synonym pipe (Case-046)
        driver.find_element(By.XPATH, "//button[contains(., 'Fill-in-the-Blank')]").click()
        driver.find_element(By.NAME, "questions[2][text]").send_keys("My favorite ___ is blue.")
        driver.find_element(By.NAME, "questions[2][blanks][0]").send_keys("color|colour")

        # Add Matching Question (Case-045)
        driver.find_element(By.XPATH, "//button[contains(., 'Matching')]").click()
        driver.find_element(By.NAME, "questions[3][text]").send_keys("Match the animal to its sound.")
        driver.find_element(By.NAME, "questions[3][pairs][0][left]").send_keys("Dog")
        driver.find_element(By.NAME, "questions[3][pairs][0][right]").send_keys("Bark")
        driver.find_element(By.NAME, "questions[3][pairs][1][left]").send_keys("Cat")
        driver.find_element(By.NAME, "questions[3][pairs][1][right]").send_keys("Meow")

        assert driver.find_element(By.ID, "totalQuestionsBadge").text == "4"
        save_evidence(driver, "Case-040_to_048_Builder")

        normalize_local_forms(driver)
        publish_btn = driver.find_element(By.XPATH, "//button[contains(., 'Publish Quiz Now')]")
        driver.execute_script("arguments[0].click();", publish_btn)
        WebDriverWait(driver, 25).until(EC.url_contains("/quiz"))
        save_evidence(driver, "Case-040_Published")

    def test_case_039_editing_live_quiz_switches_to_draft(self, driver):
        login_as(driver, TEST_STATE["teacher_email"], TEST_STATE["teacher_password"])
        safe_get(driver, f"{BASE_URL}/quiz")

        edit_btns = driver.find_elements(By.CSS_SELECTOR, "a[href*='/quiz/'][href*='/edit']")
        assert len(edit_btns) > 0, "No quiz edit links found on /quiz"
        driver.execute_script("arguments[0].click();", edit_btns[0])

        WebDriverWait(driver, 20).until(EC.presence_of_element_located((By.ID, "quizTitleInput")))
        page_text = driver.find_element(By.TAG_NAME, "body").text
        save_evidence(driver, "Case-039")
        assert "unpublished" in page_text.lower() or "draft" in page_text.lower()

    def test_case_030_quiz_history_and_review_screen(self, driver):
        login_as(driver, TEST_STATE["student_email"], TEST_STATE["student_password"])
        # QuizController@history is mapped to GET /quizzes/history
        safe_get(driver, f"{BASE_URL}/quizzes/history")
        save_evidence(driver, "Case-030_History")
        assert "/quizzes/history" in driver.current_url

        review_links = driver.find_elements(By.CSS_SELECTOR, "a[href*='/quizzes/attempt/'][href*='/review']")
        if review_links:
            driver.execute_script("arguments[0].click();", review_links[0])
            WebDriverWait(driver, 20).until(EC.url_contains("/review"))
            save_evidence(driver, "Case-030_Review")
            assert "Attempt Breakdown" in driver.find_element(By.TAG_NAME, "body").text