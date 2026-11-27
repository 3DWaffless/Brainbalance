# FILE: tests/selenium/conftest.py
import json
import os
import time
import pytest
from selenium import webdriver
from selenium.common.exceptions import WebDriverException
from selenium.webdriver.chrome.options import Options
from selenium.webdriver.chrome.service import Service
from selenium.webdriver.common.by import By
from selenium.webdriver.support import expected_conditions as EC
from selenium.webdriver.support.ui import WebDriverWait
from webdriver_manager.chrome import ChromeDriverManager

BASE_URL = os.getenv("APP_URL", "http://127.0.0.1:8000")
BASE_DIR = os.path.dirname(os.path.abspath(__file__))
SCREENSHOT_DIR = os.path.join(BASE_DIR, "screenshots")
DOWNLOAD_DIR = os.path.join(BASE_DIR, "downloads")
STATE_FILE = os.path.join(BASE_DIR, ".test_state.json")

os.makedirs(SCREENSHOT_DIR, exist_ok=True)
os.makedirs(DOWNLOAD_DIR, exist_ok=True)

DEFAULT_STATE = {
    "student_email": os.getenv("TEST_STUDENT_EMAIL", "student_seed@brainbalance.test"),
    "student_password": os.getenv("TEST_STUDENT_PASSWORD", "StrongPass!2026"),
    "teacher_email": os.getenv("TEST_TEACHER_EMAIL", "teacher@brainbalance.com"),
    "teacher_password": os.getenv("TEST_TEACHER_PASSWORD", "password"),
    "admin_email": os.getenv("TEST_ADMIN_EMAIL", "admin@brainbalance.com"),
    "admin_password": os.getenv("TEST_ADMIN_PASSWORD", "password"),
    "class_code": "QWERTY",
    "chromedriver_path": "",
}


def load_state() -> dict:
    state = dict(DEFAULT_STATE)
    if os.path.exists(STATE_FILE):
        try:
            with open(STATE_FILE, "r", encoding="utf-8") as f:
                state.update(json.load(f))
        except Exception:
            pass
    return state


def save_state(updates: dict) -> dict:
    state = load_state()
    state.update(updates)
    with open(STATE_FILE, "w", encoding="utf-8") as f:
        json.dump(state, f, indent=2)
    return state


def get_cached_chromedriver() -> str:
    state = load_state()
    cached = state.get("chromedriver_path", "")
    if cached and os.path.exists(cached):
        return cached
    driver_path = ChromeDriverManager().install()
    save_state({"chromedriver_path": driver_path})
    return driver_path


@pytest.fixture(scope="session")
def driver():
    chrome_options = Options()
    chrome_options.page_load_strategy = "eager"
    chrome_options.add_argument("--window-size=1366,900")
    chrome_options.add_argument("--disable-gpu")
    chrome_options.add_argument("--no-sandbox")
    chrome_options.add_argument("--disable-dev-shm-usage")
    chrome_options.add_argument("--disable-extensions")
    chrome_options.add_argument("--disable-background-networking")
    chrome_options.add_experimental_option("prefs", {
        "download.default_directory": DOWNLOAD_DIR,
        "download.prompt_for_download": False,
    })

    service = Service(get_cached_chromedriver())
    browser = webdriver.Chrome(service=service, options=chrome_options)
    browser.implicitly_wait(0)
    yield browser

    try:
        browser.quit()
    except Exception:
        pass
    try:
        if service.process:
            service.process.kill()
    except Exception:
        pass


class SeleniumHelpers:
    def __init__(self, driver):
        self.driver = driver
        self.base_url = BASE_URL
        self.screenshot_dir = SCREENSHOT_DIR
        self.download_dir = DOWNLOAD_DIR

    def save_evidence(self, case_id: str) -> str:
        path = os.path.join(self.screenshot_dir, f"{case_id}.png")
        self.driver.save_screenshot(path)
        return path

    def normalize_local_forms(self):
        if self.base_url.startswith("http://"):
            self.driver.execute_script("""
                document.querySelectorAll('form').forEach(f => {
                    if (f.action && (f.action.startsWith('https://127.0.0.1') || f.action.startsWith('https://localhost'))) {
                        f.action = f.action.replace('https://', 'http://');
                    }
                });
            """)

    def safe_get(self, url: str, retries: int = 2):
        for attempt in range(retries):
            try:
                self.driver.get(url)
                body = self.driver.find_element(By.TAG_NAME, "body").text
                if "ERR_CONNECTION_CLOSED" in body or "unexpectedly closed the connection" in body:
                    raise WebDriverException("ERR_CONNECTION_CLOSED")
                self.normalize_local_forms()
                return
            except WebDriverException:
                if attempt == retries - 1:
                    raise
                time.sleep(0.8)

    def first_visible(self, by, selector):
        for el in self.driver.find_elements(by, selector):
            if el.is_displayed() and el.is_enabled():
                return el
        return None

    def get_state(self) -> dict:
        return load_state()

    def update_state(self, updates: dict) -> dict:
        return save_state(updates)

    def _ensure_student_account(self, email: str, password: str):
        self.safe_get(f"{self.base_url}/register")
        first_name_els = self.driver.find_elements(By.NAME, "first_name")
        if not first_name_els:
            return

        first_name = first_name_els[0]
        self.driver.execute_script("arguments[0].scrollIntoView({block: 'center'});", first_name)
        first_name.clear()
        first_name.send_keys("Test")

        last_name = self.driver.find_element(By.NAME, "last_name")
        last_name.clear()
        last_name.send_keys("Student")

        form = first_name.find_element(By.XPATH, "./ancestor::form")
        for field_name, val in [
            ("email", email),
            ("password", password),
            ("password_confirmation", password),
        ]:
            matches = form.find_elements(By.NAME, field_name)
            if matches:
                matches[0].clear()
                matches[0].send_keys(val)

        for trigger in self.driver.find_elements(
            By.XPATH,
            "//button[contains(., 'Student')] | //label[contains(., 'Student')] | //*[@data-role='student']",
        ):
            if trigger.is_displayed():
                self.driver.execute_script("arguments[0].click();", trigger)
                time.sleep(0.15)
                break

        role_hidden = self.driver.find_elements(By.ID, "role-input")
        if role_hidden:
            self.driver.execute_script("arguments[0].value = 'student';", role_hidden[0])

        self.normalize_local_forms()
        submit_btn = form.find_element(By.CSS_SELECTOR, "button[type='submit']")
        self.driver.execute_script("arguments[0].click();", submit_btn)
        time.sleep(0.8)

    def login_as(self, email: str, password: str, expect_success: bool = True):
        for attempt in range(2):
            self.safe_get(f"{self.base_url}/login")
            try:
                WebDriverWait(self.driver, 4).until(
                    lambda d: d.execute_script("return document.readyState") == "complete"
                )
            except Exception:
                pass

            if not self.first_visible(By.CSS_SELECTOR, "input[name='email']"):
                for trigger in self.driver.find_elements(
                    By.XPATH,
                    "//button[contains(translate(normalize-space(.), 'ABCDEFGHIJKLMNOPQRSTUVWXYZ', 'abcdefghijklmnopqrstuvwxyz'), 'log in') "
                    "or contains(translate(normalize-space(.), 'ABCDEFGHIJKLMNOPQRSTUVWXYZ', 'abcdefghijklmnopqrstuvwxyz'), 'login') "
                    "or contains(translate(normalize-space(.), 'ABCDEFGHIJKLMNOPQRSTUVWXYZ', 'abcdefghijklmnopqrstuvwxyz'), 'sign in')]",
                ):
                    if trigger.is_displayed():
                        self.driver.execute_script("arguments[0].click();", trigger)
                        time.sleep(0.2)
                        if self.first_visible(By.CSS_SELECTOR, "input[name='email']"):
                            break

            email_el = WebDriverWait(self.driver, 10).until(
                EC.visibility_of_element_located((By.CSS_SELECTOR, "input[name='email']"))
            )
            email_el.clear()
            email_el.send_keys(email)

            pass_el = WebDriverWait(self.driver, 10).until(
                EC.visibility_of_element_located((By.CSS_SELECTOR, "input[name='password']"))
            )
            pass_el.clear()
            pass_el.send_keys(password)

            self.normalize_local_forms()
            form_matches = email_el.find_elements(By.XPATH, "./ancestor::form")
            if form_matches:
                submits = [
                    b
                    for b in form_matches[0].find_elements(
                        By.CSS_SELECTOR, "button[type='submit'], input[type='submit']"
                    )
                    if b.is_displayed()
                ]
                submit_btn = submits[0] if submits else self.first_visible(By.CSS_SELECTOR, "button[type='submit']")
            else:
                submit_btn = self.first_visible(By.CSS_SELECTOR, "button[type='submit']")

            assert submit_btn is not None, f"Login submit button not found on {self.driver.current_url}"
            self.driver.execute_script("arguments[0].click();", submit_btn)

            if not expect_success:
                try:
                    WebDriverWait(self.driver, 5).until(
                        lambda d: "/login" not in d.current_url
                        or any(
                            w in d.find_element(By.TAG_NAME, "body").text.lower()
                            for w in ["do not match", "credentials", "invalid", "incorrect", "failed"]
                        )
                    )
                except Exception:
                    if attempt == 0:
                        continue
                return

            try:
                WebDriverWait(self.driver, 10).until(lambda d: "/login" not in d.current_url)
                return
            except Exception:
                time.sleep(0.5)
                if "/login" not in self.driver.current_url:
                    return

                self.driver.refresh()
                time.sleep(0.4)
                if "/login" not in self.driver.current_url:
                    return

                body_text = self.driver.find_element(By.TAG_NAME, "body").text.lower()
                if attempt == 0:
                    if "do not match" in body_text or "credentials" in body_text or "invalid" in body_text:
                        self._ensure_student_account(email, password)
                    continue

                snippet = self.driver.find_element(By.TAG_NAME, "body").text[:300]
                raise AssertionError(
                    f"Login failed for {email}. Still at {self.driver.current_url}. Page says: {snippet!r}"
                )

    def logout_user(self):
        self.normalize_local_forms()
        btn = self.first_visible(By.CSS_SELECTOR, "form[action*='logout'] button[type='submit']")

        if not btn:
            btn = self.first_visible(
                By.XPATH,
                "//button[@type='submit' and contains(translate(normalize-space(.), 'ABCDEFGHIJKLMNOPQRSTUVWXYZ', 'abcdefghijklmnopqrstuvwxyz'), 'log out')]",
            )

        if not btn:
            toggles = self.driver.find_elements(
                By.CSS_SELECTOR,
                "aside button, header button, nav button, [data-dropdown-toggle], button[aria-haspopup='true']",
            )
            for toggle in toggles:
                if toggle.is_displayed():
                    self.driver.execute_script("arguments[0].click();", toggle)
                    time.sleep(0.2)
                    btn = self.first_visible(By.CSS_SELECTOR, "form[action*='logout'] button[type='submit']")
                    if btn:
                        break

        assert btn is not None, f"Visible 'Log out' submit button not found on {self.driver.current_url}"

        self.normalize_local_forms()
        self.driver.execute_script("""
            window.confirm = function() { return true; };
            arguments[0].scrollIntoView({block: 'center'});
            arguments[0].click();
        """, btn)
        time.sleep(0.4)


@pytest.fixture
def helpers(driver):
    return SeleniumHelpers(driver)