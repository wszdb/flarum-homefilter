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

### 🚀 Smart Supplementing
- Automatically supplement non-keyword discussions to maintain homepage count
- Prevents homepage from having insufficient discussions after filtering

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

## 💡 Use Cases

### Case 1: Limit Advertisement Posts
```
Keywords: ad,promotion,marketing
Filter Mode: Title Filter
Display Limit: 2
```
Effect: Homepage shows maximum 2 posts with "ad", "promotion" or "marketing" in title.

### Case 2: Control Specific Categories
```
Keywords: General,Chat
Filter Mode: Tags Filter
Display Limit: 5
```
Effect: Homepage shows maximum 5 posts tagged with "General" or "Chat".

### Case 3: Completely Hide Certain Content
```
Keywords: spam,junk
Filter Mode: Title Filter
Display Limit: 0
```
Effect: Completely hide posts with "spam" or "junk" in title.

## 🔍 How It Works

1. **Filtering**: Check homepage discussions, match based on selected mode (title/tags) and keywords
2. **Limiting**: Keep specified number of keyword-matched posts, filter out excess
3. **Supplementing**: Auto-query and add non-keyword posts if count is insufficient after filtering
4. **Precision Control**: Ensure final display count matches homepage configuration

## 📊 Performance

- **Title Filter Mode**: Performance identical to vanilla Flarum
- **Tags Filter Mode**: Adds 1-2 database queries, using optimized JOIN queries
- **Suitable For**: Most forums (daily visits < 10,000)

## 🛠️ Technical Details

### Tags Filter Implementation
Uses direct database queries to fetch discussion-tag relationships:

```php
$db->table('discussion_tag')
    ->join('tags', 'discussion_tag.tag_id', '=', 'tags.id')
    ->whereIn('discussion_tag.discussion_id', $discussionIds)
    ->select('discussion_tag.discussion_id', 'tags.name')
    ->get();
```

### Requirements
- Flarum ^1.8.0
- For Tags Filter Mode: flarum/tags extension

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
