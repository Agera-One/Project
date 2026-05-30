"""
LinkedIn Jobs Scraper - Selenium Automation (Chrome)
Target : Back End Developer jobs di Indonesia
Output : linkedin_jobs_output.json
Fix    : Selector lebih robust + error handling lebih baik
"""

import json
import re
import time
import random
import logging
from datetime import datetime
from selenium import webdriver
from selenium.webdriver.common.by import By
from selenium.webdriver.chrome.options import Options
from selenium.webdriver.support.ui import WebDriverWait
from selenium.webdriver.support import expected_conditions as EC
from selenium.common.exceptions import (
    TimeoutException,
    NoSuchElementException,
    StaleElementReferenceException,
    WebDriverException,
)

# ─────────────────────────────────────────────
# KONFIGURASI
# ─────────────────────────────────────────────
TARGET_URL = (
    "https://www.linkedin.com/jobs/search/"
    "?currentJobId=4394706825"
    "&geoId=102478259"
    "&keywords=Back%20End%20Developer"
    "&origin=JOB_SEARCH_PAGE_KEYWORD_AUTOCOMPLETE"
    "&refresh=true"
)
OUTPUT_FILE   = "linkedin_jobs_output.json"
MAX_JOBS      = 50
SCROLL_PAUSE  = 2.0
WAIT_TIMEOUT  = 20      # Naikkan timeout lebih lama
DETAIL_WAIT   = 8       # Tunggu panel detail muncul

# ─────────────────────────────────────────────
# TECH STACK KEYWORDS
# ─────────────────────────────────────────────
TECH_KEYWORDS = {
    "languages": [
        "Python", "Java", "Go", "Golang", "PHP", "Ruby", "C#", "C\\+\\+",
        "Kotlin", "Scala", "Rust", "TypeScript", "JavaScript", "Elixir",
        "Perl", "Swift",
    ],
    "frameworks": [
        "Django", "Flask", "FastAPI", "Spring Boot", "Spring", "Laravel",
        "CodeIgniter", "Symfony", "NestJS", "Express\\.js", "Express",
        "Gin", "Echo", "Rails", "Ruby on Rails", "ASP\\.NET", "\\.NET",
        "Micronaut", "Quarkus", "Fiber",
    ],
    "databases": [
        "MySQL", "PostgreSQL", "MariaDB", "MongoDB", "Redis", "Cassandra",
        "Elasticsearch", "SQLite", "Oracle", "SQL Server", "DynamoDB",
        "CockroachDB", "InfluxDB", "Neo4j", "Firestore",
    ],
    "cloud_devops": [
        "AWS", "GCP", "Azure", "Docker", "Kubernetes", "K8s", "Terraform",
        "Ansible", "Jenkins", "GitHub Actions", "GitLab CI", "CircleCI",
        "Helm", "Prometheus", "Grafana", "EKS", "ECS", "Lambda",
        "Cloud Run", "Heroku", "DigitalOcean",
    ],
    "messaging": [
        "Kafka", "RabbitMQ", "ActiveMQ", "NATS", "Celery", "SQS", "SNS",
        "Redis Streams",
    ],
    "api_protocols": [
        "REST", "RESTful", "GraphQL", "gRPC", "WebSocket", "SOAP",
        "OpenAPI", "Swagger",
    ],
    "tools": [
        "Git", "GitHub", "GitLab", "Bitbucket", "Jira", "Confluence",
        "Linux", "Nginx", "Apache",
    ],
}

# ─────────────────────────────────────────────
# LOGGING
# ─────────────────────────────────────────────
logging.basicConfig(
    level=logging.INFO,
    format="%(asctime)s [%(levelname)s] %(message)s",
    datefmt="%H:%M:%S",
)
log = logging.getLogger(__name__)


# ─────────────────────────────────────────────
# DRIVER
# ─────────────────────────────────────────────
def create_driver() -> webdriver.Chrome:
    options = Options()
    # options.add_argument("--headless=new")
    options.add_argument("--no-sandbox")
    options.add_argument("--disable-dev-shm-usage")
    options.add_argument("--disable-blink-features=AutomationControlled")
    options.add_experimental_option("excludeSwitches", ["enable-automation"])
    options.add_experimental_option("useAutomationExtension", False)
    options.add_argument("--window-size=1440,900")
    options.add_argument(
        "user-agent=Mozilla/5.0 (Windows NT 10.0; Win64; x64) "
        "AppleWebKit/537.36 (KHTML, like Gecko) "
        "Chrome/124.0.0.0 Safari/537.36"
    )
    driver = webdriver.Chrome(options=options)
    driver.execute_script(
        "Object.defineProperty(navigator, 'webdriver', {get: () => undefined})"
    )
    return driver


# ─────────────────────────────────────────────
# HELPER
# ─────────────────────────────────────────────
def safe_text(driver_or_el, selector: str) -> str:
    """Coba ambil text dengan beberapa selector sekaligus."""
    for sel in selector.split("||"):
        sel = sel.strip()
        try:
            el = driver_or_el.find_element(By.CSS_SELECTOR, sel)
            txt = el.text.strip()
            if txt:
                return txt
        except (NoSuchElementException, WebDriverException):
            continue
    return ""


def safe_attr(driver_or_el, selector: str, attr: str) -> str:
    for sel in selector.split("||"):
        sel = sel.strip()
        try:
            el  = driver_or_el.find_element(By.CSS_SELECTOR, sel)
            val = el.get_attribute(attr)
            if val:
                return val.strip()
        except (NoSuchElementException, WebDriverException):
            continue
    return ""


def human_delay(min_s=1.0, max_s=2.5):
    time.sleep(random.uniform(min_s, max_s))


def extract_tech_stack(text: str) -> dict:
    found = {}
    for category, keywords in TECH_KEYWORDS.items():
        matched = []
        for kw in keywords:
            pattern = rf"(?<![a-zA-Z0-9_])({kw})(?![a-zA-Z0-9_])"
            try:
                if re.search(pattern, text, re.IGNORECASE):
                    clean = kw.replace("\\+\\+", "++").replace("\\.", ".")
                    matched.append(clean)
            except re.error:
                pass
        if matched:
            found[category] = list(dict.fromkeys(matched))
    found["all_tech_stack"] = list(dict.fromkeys(
        t for techs in found.values() for t in techs
    ))
    return found


# ─────────────────────────────────────────────
# SCROLL
# ─────────────────────────────────────────────
def scroll_job_list(driver: webdriver.Chrome, wait: WebDriverWait):
    LIST_SELECTORS = [
        "ul.scaffold-layout__list-container",
        ".jobs-search__results-list",
        ".jobs-search-results-list",
    ]
    panel = None
    for sel in LIST_SELECTORS:
        try:
            panel = wait.until(EC.presence_of_element_located((By.CSS_SELECTOR, sel)))
            break
        except TimeoutException:
            continue

    if not panel:
        log.warning("Panel list tidak ditemukan, skip scroll.")
        return

    last_h = driver.execute_script("return arguments[0].scrollHeight", panel)
    for _ in range(15):  # max 15x scroll
        driver.execute_script("arguments[0].scrollTop = arguments[0].scrollHeight", panel)
        time.sleep(SCROLL_PAUSE)
        new_h = driver.execute_script("return arguments[0].scrollHeight", panel)
        if new_h == last_h:
            break
        last_h = new_h
        log.info("  Scrolling daftar job...")


# ─────────────────────────────────────────────
# GET CARDS
# ─────────────────────────────────────────────
def get_job_cards(driver: webdriver.Chrome) -> list:
    CARD_SELECTORS = [
        "li[data-occludable-job-id]",
        "li.jobs-search-results__list-item",
        "div.job-search-card",
        "li.job-search-card",
    ]
    for sel in CARD_SELECTORS:
        cards = driver.find_elements(By.CSS_SELECTOR, sel)
        if cards:
            return cards
    return []


# ─────────────────────────────────────────────
# EXPAND DESCRIPTION
# ─────────────────────────────────────────────
def expand_description(driver: webdriver.Chrome):
    EXPAND_SELS = [
        "button.jobs-description__footer-button",
        "button.show-more-less-html__button--more",
        "button[aria-label='Click to see more description']",
        "footer.jobs-description__details button",
    ]
    for sel in EXPAND_SELS:
        try:
            btn = driver.find_element(By.CSS_SELECTOR, sel)
            driver.execute_script("arguments[0].click();", btn)
            time.sleep(1)
            return
        except (NoSuchElementException, WebDriverException):
            continue


# ─────────────────────────────────────────────
# EXTRACT DETAIL — STRATEGI BERLAPIS
# ─────────────────────────────────────────────
def extract_job_detail(driver: webdriver.Chrome) -> dict:
    """
    Ekstrak detail job dari panel kanan.
    Menggunakan XPath + CSS berlapis agar tidak mudah gagal.
    """
    detail = {
        "job_title": "",
        "company_name": "",
        "location": "",
        "work_type_and_level": [],
        "applicant_info": "",
        "linkedin_skills": [],
        "description": "",
        "tech_stack": {"all_tech_stack": []},
        "apply_url": driver.current_url,
    }

    # ── Tunggu sampai SALAH SATU panel muncul ─────────────────
    PANEL_SELECTORS = [
        ".job-view-layout",
        ".jobs-details",
        ".jobs-unified-top-card",
        ".jobs-details__main-content",
        "[data-job-id]",
        "main",  # fallback paling luas
    ]
    panel = None
    for sel in PANEL_SELECTORS:
        try:
            panel = WebDriverWait(driver, DETAIL_WAIT).until(
                EC.presence_of_element_located((By.CSS_SELECTOR, sel))
            )
            log.debug(f"  Panel ditemukan: {sel}")
            break
        except TimeoutException:
            continue

    if not panel:
        log.warning("  Panel detail tidak ditemukan sama sekali.")
        return detail

    time.sleep(1.5)  # Beri waktu render penuh

    # ── Job Title ─────────────────────────────────────────────
    TITLE_SELS = [
        "h1.t-24",
        "h2.t-24",
        ".job-details-jobs-unified-top-card__job-title h1",
        ".jobs-unified-top-card__job-title h1",
        ".t-24.t-bold",
        "h1",
    ]
    for sel in TITLE_SELS:
        try:
            txt = driver.find_element(By.CSS_SELECTOR, sel).text.strip()
            if txt:
                detail["job_title"] = txt
                break
        except (NoSuchElementException, WebDriverException):
            continue

    # ── Company Name ──────────────────────────────────────────
    COMPANY_SELS = [
        ".job-details-jobs-unified-top-card__company-name a",
        ".jobs-unified-top-card__company-name a",
        ".jobs-unified-top-card__subtitle-primary-grouping a",
        ".topcard__org-name-link",
        "a.ember-view.t-black.t-normal",
    ]
    for sel in COMPANY_SELS:
        try:
            txt = driver.find_element(By.CSS_SELECTOR, sel).text.strip()
            if txt:
                detail["company_name"] = txt
                break
        except (NoSuchElementException, WebDriverException):
            continue

    # ── Location ──────────────────────────────────────────────
    LOCATION_SELS = [
        ".job-details-jobs-unified-top-card__bullet",
        ".jobs-unified-top-card__bullet",
        ".jobs-unified-top-card__workplace-type",
        ".topcard__flavor--bullet",
        ".jobs-unified-top-card__subtitle-primary-grouping span.tvm__text",
    ]
    for sel in LOCATION_SELS:
        try:
            txt = driver.find_element(By.CSS_SELECTOR, sel).text.strip()
            if txt:
                detail["location"] = txt
                break
        except (NoSuchElementException, WebDriverException):
            continue

    # ── Work Type & Level ─────────────────────────────────────
    BADGE_SELS = [
        ".job-details-jobs-unified-top-card__job-insight span",
        ".jobs-unified-top-card__job-insight span",
        ".description__job-criteria-text",
        "li.description__job-criteria-item span",
    ]
    for sel in BADGE_SELS:
        try:
            els = driver.find_elements(By.CSS_SELECTOR, sel)
            texts = [e.text.strip() for e in els if e.text.strip()]
            if texts:
                detail["work_type_and_level"] = texts
                break
        except WebDriverException:
            continue

    # ── Applicant Info ────────────────────────────────────────
    APPLICANT_SELS = [
        ".jobs-unified-top-card__applicant-count",
        ".tvm__text--positive",
        ".jobs-unified-top-card__subtitle-primary-grouping .tvm__text",
        "span.tvm__text.tvm__text--neutral",
    ]
    for sel in APPLICANT_SELS:
        try:
            txt = driver.find_element(By.CSS_SELECTOR, sel).text.strip()
            if txt:
                detail["applicant_info"] = txt
                break
        except (NoSuchElementException, WebDriverException):
            continue

    # ── LinkedIn Skills (badge resmi) ─────────────────────────
    SKILL_SELS = [
        ".job-details-skill-match-status-list li",
        ".jobs-pref-match__skill-tag",
        "button[id*='skills-to-highlight'] span",
        ".job-details-how-you-match__skills-item",
    ]
    skills = set()
    for sel in SKILL_SELS:
        try:
            els = driver.find_elements(By.CSS_SELECTOR, sel)
            for el in els:
                t = el.text.strip()
                if t:
                    skills.add(t)
        except WebDriverException:
            continue
    detail["linkedin_skills"] = list(skills)

    # ── Description ───────────────────────────────────────────
    expand_description(driver)
    DESC_SELS = [
        ".jobs-description-content__text",
        ".jobs-box__html-content",
        ".jobs-description__content",
        "#job-details",
        ".description__text",
        "div.show-more-less-html__markup",
    ]
    for sel in DESC_SELS:
        try:
            txt = driver.find_element(By.CSS_SELECTOR, sel).text.strip()
            if txt:
                detail["description"] = txt
                break
        except (NoSuchElementException, WebDriverException):
            continue

    # ── Tech Stack ────────────────────────────────────────────
    if detail["description"]:
        detail["tech_stack"] = extract_tech_stack(detail["description"])

    # ── Apply URL ─────────────────────────────────────────────
    APPLY_SELS = [
        "a.jobs-apply-button",
        "button.jobs-apply-button",
        "a[data-control-name='jobdetails_topcard_inapply']",
    ]
    for sel in APPLY_SELS:
        try:
            url = driver.find_element(By.CSS_SELECTOR, sel).get_attribute("href")
            if url:
                detail["apply_url"] = url
                break
        except (NoSuchElementException, WebDriverException):
            continue

    return detail


# ─────────────────────────────────────────────
# MAIN SCRAPER
# ─────────────────────────────────────────────
def scrape_jobs(driver: webdriver.Chrome) -> list:
    wait      = WebDriverWait(driver, WAIT_TIMEOUT)
    jobs_data = []

    log.info(f"Membuka halaman: {TARGET_URL}")
    driver.get(TARGET_URL)
    human_delay(4, 6)

    scroll_job_list(driver, wait)
    human_delay(1, 2)

    cards = get_job_cards(driver)
    if not cards:
        log.error("❌ Tidak ada kartu job. Kemungkinan perlu login LinkedIn.")
        return jobs_data

    total = min(len(cards), MAX_JOBS) if MAX_JOBS else len(cards)
    log.info(f"Ditemukan {len(cards)} job → memproses {total}")

    for idx in range(total):
        job_entry = {"card_index": idx + 1}
        try:
            # Re-fetch untuk hindari stale
            cards = get_job_cards(driver)
            if idx >= len(cards):
                log.warning(f"[{idx+1}] Kartu tidak tersedia, skip.")
                continue
            card = cards[idx]

            # ── Data kartu ────────────────────────────────────
            job_entry["job_id"] = (
                card.get_attribute("data-occludable-job-id")
                or card.get_attribute("data-job-id")
                or card.get_attribute("data-entity-urn") or ""
            )
            job_entry["card_title"]    = safe_text(card, "h3 || h4 || .base-search-card__title || a.job-card-list__title")
            job_entry["card_company"]  = safe_text(card, "h4 || .base-search-card__subtitle || .job-card-container__primary-description")
            job_entry["card_location"] = safe_text(card, ".job-search-card__location || .base-search-card__metadata || .job-card-container__metadata-item")
            job_entry["posted_date"]   = safe_text(card, "time || .job-search-card__listdate || .job-card-container__listed-status")
            job_entry["card_url"]      = safe_attr(card, "a.base-card__full-link || a.job-card-list__title || a", "href")

            # ── Klik kartu ────────────────────────────────────
            try:
                link = card.find_element(
                    By.CSS_SELECTOR,
                    "a.base-card__full-link, a.job-card-list__title, a"
                )
                driver.execute_script("arguments[0].click();", link)
                human_delay(3, 5)  # Tunggu panel detail render
            except (NoSuchElementException, WebDriverException) as e:
                log.warning(f"[{idx+1}] Tidak bisa klik kartu: {e}")
                jobs_data.append(job_entry)
                continue

            # ── Ekstrak detail ────────────────────────────────
            detail = extract_job_detail(driver)
            job_entry.update(detail)

            tech_summary = ", ".join(
                detail.get("tech_stack", {}).get("all_tech_stack", [])
            ) or "—"
            log.info(
                f"[{idx+1}/{total}] ✓ "
                f"{job_entry.get('card_title') or detail.get('job_title') or '?'} "
                f"@ {job_entry.get('card_company') or detail.get('company_name') or '?'} "
                f"| Tech: {tech_summary}"
            )

        except StaleElementReferenceException:
            log.warning(f"[{idx+1}] StaleElement, lewati.")
        except Exception as e:
            log.error(f"[{idx+1}] Error: {e}")

        # Simpan entry meski detail gagal
        jobs_data.append(job_entry)

    return jobs_data


# ─────────────────────────────────────────────
# SAVE JSON
# ─────────────────────────────────────────────
def save_to_json(data: list, filename: str):
    output = {
        "scraped_at": datetime.now().isoformat(),
        "source_url": TARGET_URL,
        "total_jobs": len(data),
        "jobs": data,
    }
    with open(filename, "w", encoding="utf-8") as f:
        json.dump(output, f, ensure_ascii=False, indent=2)
    log.info(f"✅ Tersimpan: {filename}  ({len(data)} job)")


# ─────────────────────────────────────────────
# MAIN
# ─────────────────────────────────────────────
def main():
    driver = create_driver()
    try:
        jobs = scrape_jobs(driver)
        save_to_json(jobs, OUTPUT_FILE)
    finally:
        driver.quit()
        log.info("Browser Chrome ditutup.")


if __name__ == "__main__":
    main()