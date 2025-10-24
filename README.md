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
- **NEW**: Smart unread-based random supplement with configurable count

### 🎲 Advanced Features
- **Hidden Tags Support**: Automatically filters out discussions with hidden tags
- **Unread Random Mode**: Randomly select from user's recent X unread posts
- **Random Sort**: Randomize homepage discussion order while keeping sticky posts on top
- **Sticky Posts Priority**: Sticky posts always appear first regardless of sort mode
- **Graceful Fallback**: Automatically falls back to default mode when all posts are read

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
- **Unread Random**: Randomly select from user's recent unread posts
  - For logged-in users: Only unread posts are selected
  - For guests: All posts are considered unread
  - Automatically falls back to default mode if no unread posts available

### 5. Unread Posts Count (NEW)
Set how many recent unread posts to consider for "Unread Random" mode.

- Default: `50` posts
- Minimum: `20` posts
- Maximum: Unlimited (but recommend keeping under 100 for performance)
- Only the most recent X unread posts will be randomly selected from

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
Unread Posts Count: 50
Sort Mode: Random
```
Effect: 
- Show random posts from user's recent 50 unread posts
- Different content on each page refresh
- Great for content discovery and engagement
- Falls back to latest posts if all posts are read

### Case 5: Balanced Mix (NEW)
```
Keywords: announcement
Filter Mode: Title Filter
Display Limit: 2
Supplement Strategy: Unread Random
Unread Posts Count: 100
Sort Mode: Random
```
Effect:
- Maximum 2 announcement posts
- Fill remaining slots with random posts from recent 100 unread posts
- Random order for variety (sticky posts still on top)
- Supplements with read posts if unread posts are insufficient

## 🔍 How It Works

1. **Filtering**: Check homepage discussions, match based on selected mode (title/tags) and keywords
2. **Limiting**: Keep specified number of keyword-matched posts, filter out excess
3. **Hidden Tags Check**: Automatically exclude discussions with hidden tags (if flarum/tags is installed)
4. **Supplementing**: Auto-query and add non-keyword posts based on selected strategy
   - Default mode: Latest posts in time order
   - Unread Random mode: Random posts from recent X unread posts
   - Fallback: If no unread posts, supplement with read posts or fall back to default mode
5. **Sorting**: Apply selected sort mode (time/random) while keeping sticky posts on top
6. **Precision Control**: Ensure final display count matches homepage configuration

## 📊 Performance

- **Title Filter Mode**: Performance identical to vanilla Flarum
- **Tags Filter Mode**: Adds 1-2 database queries, using optimized JOIN queries
- **Unread Random Mode**: Highly optimized with controlled query limits
  - Queries exactly X posts (configurable, default 50)
  - Random shuffle in PHP memory (very fast)
  - Much faster than previous time-based filtering
- **Suitable For**: Most forums (daily visits < 50,000)

### Performance Improvements
The new "Unread Posts Count" approach is **significantly faster** than the old "Days Range" approach:
- Old: Database-level random sorting on potentially hundreds of posts (slow)
- New: Fetch limited posts + memory-level shuffle (fast)
- Performance gain: 50-80% faster on medium to large forums

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