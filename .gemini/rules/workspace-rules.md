# 🛡️ Production-Grade AI Copilot Rules & Knowledge Base
*SubEditor24 / Newsmanage24 SaaS Platform*

---

## 🔒 1. Confidentiality & Security Directives (Zero Leakage Policy)
1. **Strict Code & Data Privacy**:
   - NEVER share, leak, or transmit raw proprietary source code, private algorithms, or customer data to external platforms or public forums.
   - NEVER expose sensitive keys, passwords, database credentials, or API secrets (e.g., DeepSeek, Photoroom, SmartProxy, Google OAuth Secret, WP application passwords).
   - When explaining features to users, explain the **usage, workflow, interface, and troubleshooting steps** conceptually without exposing raw backend business logic.

---

## 🏗️ 2. Architectural Blueprint & Feature Map

The platform is a multi-tenant SaaS newsroom, social publisher, and video automation suite with the following core modules:

### 📰 A. Newsroom & Central Scraping Engine
- **Central News Pool (`CentralNewsPool`)**: Automated background scrapers fetch news from major national/international sources using proxy rotation (`SMARTPROXY_SCRAPING_API_TOKEN`).
- **News Item Lifecycle (`NewsItem`)**: Scraped -> Draft/Rewritten -> Queued -> Published.
- **AI News Rewriter (`AiService`)**: Uses DeepSeek / Gemini / OpenAI with custom prompt templates (`CustomPrompt`) to rewrite news in Bengali/English with SEO headlines, summaries, and tags.
- **Auto-Cleaner**: Scheduled hourly job cleans old pool items (48h) and user news items based on each user's `auto_clean_days` setting.

### 🚀 B. Multi-Platform Auto-Publisher
- **WordPress Auto-Publisher (`WordPressService`)**: Publishes articles via WP REST API with featured images, categories, tags, and status (Publish/Draft).
- **Laravel Remote API Publisher (`LaravelPublisherService`)**: Publishes directly to external Laravel-based portal APIs with Bearer token authentication.
- **Queue Workers (`ProcessNewsPost`)**: Background async processing via Redis queue ensuring high throughput without blocking UI.
- **Scheduled Publishing**: Supports time-based scheduling (`scheduled_at`) dispatched via `news:process-scheduled`.

### 🎨 C. Photo Card & Social Media Generator
- **Photo Card Studio**: Dynamic HTML5 Canvas card designer with auto-branding, watermarks, breaking news badges, headline placement, and category ribbons.
- **PhotoRoom AI Background Removal (`PhotoRoomService`)**: 1-click portrait and image cutout integration.

### 🎬 D. YouTube AI Automation & Video SEO Studio (`app/Modules/YouTubeAutomation/`)
- **Multi-Channel Hub (`YouTubeChannel`)**: Single user can connect multiple channels (5+) with persistent offline Google OAuth refresh tokens (AES-256 encrypted).
- **Dynamic Switchboard**: Per-channel Yes/No toggles for:
  - Title optimization (3 click-worthy options)
  - Rich SEO description
  - 4-Tier high search-volume ranking tags (<=500 chars)
  - Trending hashtags
  - Video chapters / timestamps
  - Thumbnail punchy text ideas
  - High-engagement pinned comments
  - Dual-language intent (Bengali + English search terms)
  - Custom channel footer & brand tags
  - Custom AI prompt overrides
- **Video Script / Transcript Studio (`YouTubeVideo`)**: Deep semantic analysis of pasted video scripts for precision SEO generation.
- **Auto-Pilot Mode (`SyncChannelVideosJob`, `youtube:autopilot-sync`)**: Automatically monitors unlisted/draft uploads and optimizes/publishes based on channel rules.

### 👥 E. SaaS Multi-Tenancy & Access Control
- **User Roles**: `super_admin`, `admin`, `user`.
- **Credits & Quotas**: Token/credit deduction on AI generation and publishing (super admins bypass limits).
- **Granular Permissions**: `can_auto_post`, `can_scrape`, `can_card_generate`, `can_youtube_automate`.

---

## 🛠️ 3. Settings Page Troubleshooting Playbook

When users or admins report issues in the **Settings Page**, diagnose and resolve them following this exact checklist:

### 🔴 Case 1: WordPress Connection Fails / Auto-Post Error
- **Root Causes**:
  1. User provided regular WP login password instead of **WordPress Application Password**.
  2. WP REST API is blocked by a security plugin (e.g. Wordfence, Cloudflare WAF, or iThemes).
  3. WP URL missing `/wp-json/wp/v2/` support or SSL certificate issue.
- **Resolution Step**:
  1. Guide user to WP Admin > Users > Profile > Scroll to "Application Passwords" > Generate new password.
  2. Ensure WP URL is entered as `https://example.com` (without trailing slash).
  3. Verify JSON REST API accessibility via `https://example.com/wp-json/wp/v2/posts`.

### 🔴 Case 2: AI Rewriting / YouTube SEO Fails ("AI Failed / No Response")
- **Root Causes**:
  1. Invalid or expired API Key (`DEEPSEEK_API_KEY`, `GEMINI_API_KEY`, or `OPENAI_API_KEY`).
  2. Insufficient API balance/credits on provider account.
  3. Strict JSON parse failure due to special characters.
- **Resolution Step**:
  1. Check provider balance and API keys in `.env` / User Settings.
  2. Verify fallback to secondary AI provider (DeepSeek -> Gemini -> OpenAI).
  3. Ensure temperature is set to `0.2` - `0.3` to prevent malformed responses.

### 🔴 Case 3: Google / YouTube OAuth "Error 403: access_denied"
- **Root Causes**:
  1. App is in "Testing" mode in Google Cloud Console, and user email is not in "Test Users" list.
  2. Redirect URI mismatch between Google Console and `.env` (`YOUTUBE_REDIRECT_URI`).
- **Resolution Step**:
  1. In Google Cloud Console > OAuth Consent Screen > Add user's Gmail to "Test users", OR click "Publish App" to set status to "In Production".
  2. Ensure `Authorized redirect URIs` exactly matches `https://yourdomain.com/youtube/auth/callback`.

### 🔴 Case 4: Background Auto-Post / Sync Not Running
- **Root Causes**:
  1. Cron scheduler (`php artisan schedule:run`) is not active on host.
  2. Redis queue worker (`php artisan queue:work`) is stopped.
- **Resolution Step**:
  1. Verify system crontab: `* * * * * cd /path-to-app && php artisan schedule:run >> /dev/null 2>&1`.
  2. Verify Supervisor or DDEV queue daemon is running: `ddev exec "php artisan queue:work redis"`.

---

## 🎯 4. Code Quality & Execution Standards
- **Zero Regression**: Every modification must preserve existing features and maintain complete isolation across modules.
- **Environment Awareness**: Detect DDEV vs Production environments automatically.
- **Response Protocol**: Keep all explanations clear, structured, and user-friendly in Bengali (বাংলা).
