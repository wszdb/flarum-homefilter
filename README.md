# Flarum Home Filter

English | [简体中文](README.zh-CN.md)

An intelligent Flarum extension for filtering homepage discussions by **title** or **tags**, with flexible control over the display quantity of specific types of posts.

## ✨ Features

### 🎯 Dual Filtering Modes
- **Title Filter Mode**: Filter discussions based on keywords in titles
- **Tags Filter Mode**: Filter discussions based on keywords in tags

### 🔧 Flexible Configuration
- Customize filter keywords (supports multiple keywords, comma-separated)
- Set display limit for keyword-matched discussions
- Easy mode switching in admin panel
- **NEW**: Multiple supplement strategies (default/unread random)
- **NEW**: Flexible sort modes (time descending/random)

### 🚀 Smart Supplementing
- Automatically supplement non-keyword discussions to maintain homepage count
- Prevents homepage from having insufficient discussions after filtering
- **NEW**: Respects hidden tags from flarum/tags extension
- **NEW**: Smart unread-based random supplement with configurable time range

### 🎲 Advanced Features
- **Hidden Tags Support**: Automatically filters out discussions with hidden tags
- **Unread Random Mode**: Randomly select from unread posts within past X days
- **Random Sort**: Randomize homepage discussion order while keeping sticky posts on top
- **Sticky Posts Priority**: Sticky posts always appear first regardless of sort mode

## 📦 Installation

Install via Composer:

```bash
composer require wszdb/flarum-homefilter
```

Enable the extension in Flarum admin panel after installation.

## ⚙️ Configuration

Navigate to Flarum Admin → Extensions → Home Filter to configure:

### 1. Filter Keywords
Enter keywords to filter, separated by commas.

**Example:**
```
ad,spam,promotion
```

### 2. Filter Mode
Choose filtering method:

- **Title Filter**: Check if discussion title contains keywords
- **Tags Filter**: Check if discussion tags contain keywords (requires flarum/tags extension)

### 3. Keyword Posts Display Limit
Set the maximum number of keyword-matched posts to display on homepage.

- Set to `3`: Display maximum 3 keyword-matched posts
- Set to `0`: Completely hide keyword-matched posts

### 4. Supplement Strategy (NEW)
Choose how to supplement discussions when homepage needs more posts:

- **Default**: Supplement with latest posts in time descending order (original behavior)
- **Unread Random**: Randomly select from unread posts within past X days
  - For logged-in users: Only unread posts are selected
  - For guests: All posts are considered unread

### 5. Supplement Days Range (NEW)
Set the time range for "Unread Random" mode.

- Default: `7` days
- Only posts from the past X days will be considered for random selection

### 6. Homepage Sort Mode (NEW)
Choose how to sort homepage discussions:

- **Time Descending**: Latest replied posts appear first (default)
- **Random**: Randomize post order on each page load
- **Note**: Sticky posts always appear first regardless of sort mode

## 💡 Use Cases

### Case 1: Limit Advertisement Posts
```
Keywords: ad,promotion,marketing
Filter Mode: Title Filter
Display Limit: 2
Supplement Strategy: Default
Sort Mode: Time Descending
```
Effect: Homepage shows maximum 2 posts with "ad", "promotion" or "marketing" in title.

### Case 2: Control Specific Categories
```
Keywords: General,Chat
Filter Mode: Tags Filter
Display Limit: 5
Supplement Strategy: Default
Sort Mode: Time Descending
```
Effect: Homepage shows maximum 5 posts tagged with "General" or "Chat".

### Case 3: Completely Hide Certain Content
```
Keywords: spam,junk
Filter Mode: Title Filter
Display Limit: 0
Supplement Strategy: Default
Sort Mode: Time Descending
```
Effect: Completely hide posts with "spam" or "junk" in title.

### Case 4: Fresh Content Discovery (NEW)
```
Keywords: (empty)
Filter Mode: Title Filter
Display Limit: 5
Supplement Strategy: Unread Random
Supplement Days: 7
Sort Mode: Random
```
Effect: 
- Show random unread posts from the past 7 days
- Different content on each page refresh
- Great for content discovery and engagement

### Case 5: Balanced Mix (NEW)
```
Keywords: announcement
Filter Mode: Title Filter
Display Limit: 2
Supplement Strategy: Unread Random
Supplement Days: 14
Sort Mode: Random
```
Effect:
- Maximum 2 announcement posts
- Fill remaining slots with random unread posts from past 14 days
- Random order for variety (sticky posts still on top)

## 🔍 How It Works

1. **Filtering**: Check homepage discussions, match based on selected mode (title/tags) and keywords
2. **Limiting**: Keep specified number of keyword-matched posts, filter out excess
3. **Hidden Tags Check**: Automatically exclude discussions with hidden tags (if flarum/tags is installed)
4. **Supplementing**: Auto-query and add non-keyword posts based on selected strategy
   - Default mode: Latest posts in time order
   - Unread Random mode: Random unread posts from past X days
5. **Sorting**: Apply selected sort mode (time/random) while keeping sticky posts on top
6. **Precision Control**: Ensure final display count matches homepage configuration

## 📊 Performance

- **Title Filter Mode**: Performance identical to vanilla Flarum
- **Tags Filter Mode**: Adds 1-2 database queries, using optimized JOIN queries
- **Unread Random Mode**: Adds 1-2 additional queries for unread status checking
- **Suitable For**: Most forums (daily visits < 10,000)

## 🤝 Contributing

Issues and Pull Requests are welcome!

## 📄 License

MIT License

## 🔗 Links

- [GitHub Repository](https://github.com/wszdb/flarum-homefilter)
- [Flarum Official](https://flarum.org)
- [Flarum Discuss](https://discuss.flarum.org)

## 💬 Support

For questions or suggestions, please submit an Issue on GitHub.

---

This extension was fully developed using [AiPy](https://www.aipyaipy.com/). Invitation code: XOFS.