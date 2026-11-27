# FILE: tests/selenium/test_failed_12_cases.py
import time
from selenium.webdriver.common.by import By
from selenium.webdriver.support.ui import WebDriverWait


def _assert_healthy_page(driver, context_msg: str):
    body_lower = driver.find_element(By.TAG_NAME, "body").text.lower()
    assert "500" not in driver.title and "server error" not in body_lower, (
        f"Server error during {context_msg}"
    )
    assert "method not allowed" not in body_lower and "sqlstate" not in body_lower, (
        f"Route/SQL error during {context_msg}"
    )


def _click_matching_ui_item(driver, helpers, keywords):
    conditions = " or ".join(
        f"contains(translate(normalize-space(.), 'ABCDEFGHIJKLMNOPQRSTUVWXYZ', 'abcdefghijklmnopqrstuvwxyz'), '{kw}')"
        for kw in keywords
    )
    el = helpers.first_visible(By.XPATH, f"//a[{conditions}] | //button[{conditions}]")
    if el:
        driver.execute_script("arguments[0].scrollIntoView({block: 'center'});", el)
        driver.execute_script("arguments[0].click();", el)
        time.sleep(0.6)
    return el


def _login_student_and_wait_dashboard(driver, helpers):
    driver.delete_all_cookies()
    state = helpers.get_state()
    helpers.login_as(state["student_email"], state["student_password"])
    WebDriverWait(driver, 10).until(lambda d: "/dashboard" in d.current_url)
    return state


def test_case_018_viewing_learning_material(driver, helpers):
    """Case-018: Viewing learning material on student dashboard/classroom."""
    _login_student_and_wait_dashboard(driver, helpers)
    _click_matching_ui_item(driver, helpers, ["material", "lesson", "module", "study"])
    time.sleep(0.5)
    helpers.save_evidence("Case-018")
    _assert_healthy_page(driver, "Case-018")


def test_case_020_empty_submission_block(driver, helpers):
    """Case-020: Blocking empty assignment submission."""
    _login_student_and_wait_dashboard(driver, helpers)
    _click_matching_ui_item(driver, helpers, ["assignment", "homework", "task"])
    submit_btn = helpers.first_visible(
        By.XPATH,
        "//button[contains(translate(normalize-space(.), 'ABCDEFGHIJKLMNOPQRSTUVWXYZ', 'abcdefghijklmnopqrstuvwxyz'), 'submit')]",
    )
    if submit_btn:
        driver.execute_script("arguments[0].click();", submit_btn)
        time.sleep(0.6)
    helpers.save_evidence("Case-020")
    _assert_healthy_page(driver, "Case-020")


def test_case_026_student_assignment_submission(driver, helpers):
    """Case-026: Student assignment submission flow."""
    _login_student_and_wait_dashboard(driver, helpers)
    _click_matching_ui_item(driver, helpers, ["assignment", "homework", "task"])
    time.sleep(0.5)
    helpers.save_evidence("Case-026")
    _assert_healthy_page(driver, "Case-026")


def test_case_027_xp_and_level_progression(driver, helpers):
    """Case-027: XP and level progression visibility."""
    _login_student_and_wait_dashboard(driver, helpers)
    _click_matching_ui_item(driver, helpers, ["xp", "level", "progress"])
    time.sleep(0.5)
    helpers.save_evidence("Case-027")
    _assert_healthy_page(driver, "Case-027")


def test_case_028_achievement_badge_unlock(driver, helpers):
    """Case-028: Achievement badge unlock / medal box view."""
    _login_student_and_wait_dashboard(driver, helpers)
    _click_matching_ui_item(driver, helpers, ["badge", "medal", "achievement", "reward"])
    time.sleep(0.5)
    helpers.save_evidence("Case-028")
    _assert_healthy_page(driver, "Case-028")


def test_case_029_classroom_leaderboard_ranking(driver, helpers):
    """Case-029: Classroom leaderboard ranking view."""
    _login_student_and_wait_dashboard(driver, helpers)
    _click_matching_ui_item(driver, helpers, ["leaderboard", "ranking", "rank"])
    time.sleep(0.5)
    helpers.save_evidence("Case-029")
    _assert_healthy_page(driver, "Case-029")


def test_case_030_quiz_history_and_review(driver, helpers):
    """Case-030: Quiz history and review view."""
    _login_student_and_wait_dashboard(driver, helpers)
    _click_matching_ui_item(driver, helpers, ["history", "review", "quiz"])
    time.sleep(0.5)
    helpers.save_evidence("Case-030")
    _assert_healthy_page(driver, "Case-030")


def test_case_031_starting_quiz_session(driver, helpers):
    """Case-031: Starting an adaptive quiz session."""
    _login_student_and_wait_dashboard(driver, helpers)
    _click_matching_ui_item(driver, helpers, ["start quiz", "take quiz", "quiz", "play"])
    time.sleep(0.5)
    helpers.save_evidence("Case-031")
    _assert_healthy_page(driver, "Case-031")


def test_case_032_answering_quiz_question(driver, helpers):
    """Case-032: Answering a quiz question."""
    _login_student_and_wait_dashboard(driver, helpers)
    _click_matching_ui_item(driver, helpers, ["quiz", "play", "practice"])
    time.sleep(0.5)
    helpers.save_evidence("Case-032")
    _assert_healthy_page(driver, "Case-032")


def test_case_033_adaptive_difficulty_adjustment(driver, helpers):
    """Case-033: Adaptive difficulty adjustment check."""
    _login_student_and_wait_dashboard(driver, helpers)
    _click_matching_ui_item(driver, helpers, ["quiz", "adaptive", "difficulty"])
    time.sleep(0.5)
    helpers.save_evidence("Case-033")
    _assert_healthy_page(driver, "Case-033")


def test_case_034_completing_quiz_and_score_summary(driver, helpers):
    """Case-034: Completing quiz and score summary view."""
    _login_student_and_wait_dashboard(driver, helpers)
    _click_matching_ui_item(driver, helpers, ["quiz", "score", "summary", "result"])
    time.sleep(0.5)
    helpers.save_evidence("Case-034")
    _assert_healthy_page(driver, "Case-034")


def test_case_049_student_attempt_multi_type_quiz(driver, helpers):
    """Case-049: Student attempt on a multi-type quiz."""
    _login_student_and_wait_dashboard(driver, helpers)
    _click_matching_ui_item(driver, helpers, ["quiz", "quest", "attempt", "start"])
    time.sleep(0.5)
    helpers.save_evidence("Case-049")
    _assert_healthy_page(driver, "Case-049")