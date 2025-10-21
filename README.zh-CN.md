# Flarum 首页过滤插件

[![MIT 协议](https://img.shields.io/badge/license-MIT-blue.svg)](LICENSE)
[![Flarum](https://img.shields.io/badge/flarum-^1.8.0-orange.svg)](https://flarum.org)

🎯 **智能首页帖子过滤插件** - 限制特定关键词帖子在首页的显示数量，保持内容多样性。

[English](README.md) | 简体中文

## 功能说明

本插件解决了 Flarum 首页可能被某类帖子（如广告、推广）占据的问题。通过设置关键词和数量限制，确保首页内容更加多元化。

### 核心特性

- 🔍 关键词智能匹配（支持多个关键词）
- 📊 数量灵活控制（1-50个可调）
- 🔄 自动补充机制（保持总数不变）
- 🌐 中英文双语界面
- ⚡ 高性能双重过滤
- 🎨 可视化后台配置

## 快速开始

### 安装

```bash
composer require wszdb/flarum-homefilter
php flarum cache:clear
```

### 配置

1. 进入 **管理后台 → 扩展 → Home Filter**
2. 设置 **过滤关键词**（如：`广告,推广,营销`）
3. 设置 **数量限制**（默认5个）
4. 保存设置

## 使用示例

假设首页默认显示 20 个帖子：

**配置前：**
- 广告帖：12个
- 正常帖：8个

**配置后（限制5个广告帖）：**
- 广告帖：5个 ✓
- 正常帖：15个 ✓（自动补充）

## 常见问题

**Q: 插件会影响搜索结果吗？**  
A: 不会。过滤仅在首页生效，搜索、标签页等其他页面不受影响。

**Q: 关键词匹配是否区分大小写？**  
A: 不区分。"Spam" 和 "spam" 都会被匹配。

**Q: 可以设置不同关键词的不同限制吗？**  
A: 当前版本所有关键词共享同一个限制数量。

## 技术支持

- 问题反馈：[GitHub Issues](https://github.com/wszdb/flarum-homefilter/issues)
- 开发文档：[README.md](README.md)

## 开源协议

MIT License - 详见 [LICENSE](LICENSE) 文件

---

**用 ❤️ 制作 by [wszdb](https://github.com/wszdb)**
