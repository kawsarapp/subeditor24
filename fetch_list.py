import sys
import io
import time
import random
from curl_cffi import requests

# ==========================================
# 🔥 UNIVERSAL ENCODING FIX (Windows/Linux)
# ==========================================
if sys.platform.startswith('win'):
    sys.stdout.reconfigure(encoding='utf-8')
    sys.stderr.reconfigure(encoding='utf-8')
else:
    sys.stdout = io.TextIOWrapper(sys.stdout.buffer, encoding='utf-8')
    sys.stderr = io.TextIOWrapper(sys.stderr.buffer, encoding='utf-8')

try:
    target_url = sys.argv[1]
except IndexError:
    print("Error: No URL provided")
    sys.exit(1)

proxy_url = sys.argv[2] if len(sys.argv) > 2 else None

# ==========================================
# 🚀 ADVANCED REQUEST WITH RETRY (Production Grade)
# ==========================================

# Rotate Chrome impersonation fingerprints per request for better bypass
CHROME_FINGERPRINTS = [
    "chrome124", "chrome120", "chrome116", "chrome110"
]

USER_AGENTS = [
    'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/124.0.0.0 Safari/537.36',
    'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/123.0.0.0 Safari/537.36',
    'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/124.0.0.0 Safari/537.36',
    'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/122.0.0.0 Safari/537.36',
    'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/121.0.0.0 Safari/537.36',
]

def get_html(url, proxy=None, retries=3):
    proxies = {"http": proxy, "https": proxy} if proxy else None

    for attempt in range(retries):
        fingerprint = random.choice(CHROME_FINGERPRINTS)
        ua = random.choice(USER_AGENTS)

        headers = {
            'Accept': 'text/html,application/xhtml+xml,application/xml;q=0.9,image/avif,image/webp,image/apng,*/*;q=0.8',
            'Accept-Language': 'bn-BD,bn;q=0.9,en-US;q=0.8,en;q=0.7',
            'Accept-Encoding': 'gzip, deflate, br',
            'User-Agent': ua,
            'Upgrade-Insecure-Requests': '1',
            'Sec-Fetch-Dest': 'document',
            'Sec-Fetch-Mode': 'navigate',
            'Sec-Fetch-Site': 'none',
            'Sec-Fetch-User': '?1',
            'Cache-Control': 'max-age=0',
        }

        try:
            response = requests.get(
                url,
                impersonate=fingerprint,
                timeout=30,
                proxies=proxies,
                headers=headers,
                verify=False,
                allow_redirects=True,
            )
            if response.status_code == 200:
                if response.encoding is None or response.encoding.upper() in ['ISO-8859-1', 'LATIN-1']:
                    response.encoding = response.apparent_encoding or 'utf-8'
                content = response.text
                if len(content) > 1000:
                    return content
            elif response.status_code in [403, 429, 503]:
                wait = 2 + attempt * 2
                sys.stderr.write(f"⚠️ Blocked [{response.status_code}] attempt {attempt+1}, waiting {wait}s...\n")
                time.sleep(wait)
            elif response.status_code == 200 and len(response.text) < 1000:
                with open("fetch_error.log", "a") as f: f.write(f"⚠️ Response too short ({len(response.text)} chars), retrying...\n")
                time.sleep(1)
        except Exception as e:
            with open("fetch_error.log", "a") as f: f.write(f"⚠️ Request Error attempt {attempt+1}: {str(e)}\n")
            time.sleep(1 + attempt)

    return ""

html = get_html(target_url, proxy_url)
if html:
    print(html, end='')
else:
    with open("fetch_error.log", "a") as f: f.write("❌ All attempts failed - empty response\n")
    sys.exit(1)
