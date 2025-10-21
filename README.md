# Flarum Home Filter Extension

[![MIT License](https://img.shields.io/badge/license-MIT-blue.svg)](LICENSE)
[![Flarum](https://img.shields.io/badge/flarum-^1.8.0-orange.svg)](https://flarum.org)

🎯 **智能首页帖子过滤插件** - 通过关键词限制特定类型帖子在首页的显示数量，保持内容多样性。

## ✨ 功能特性

- 🔍 **智能关键词匹配** - 支持多关键词逗号分隔配置
- 📊 **数量精确控制** - 灵活设置关键词帖子显示上限（1-50）
- 🔄 **自动补偿机制** - 保持首页帖子总数不变
- 🌐 **多语言支持** - 内置中文和英文语言包
- ⚡ **高性能设计** - 双重过滤机制（SQL查询层 + 数据处理层）
- 🎨 **友好管理界面** - 后台可视化配置，无需修改代码

## 📋 使用场景

适用于以下情况：
- 限制广告、推广类帖子在首页的曝光度
- 控制特定话题（如招聘、二手交易）的显示数量
- 平衡不同类型内容的展示比例
- 提升首页内容多样性和用户体验

## 🚀 安装方法

### 方法一：Composer 安装（推荐）

```bash
composer require wszdb/flarum-homefilter
php flarum cache:clear
```

### 方法二：手动安装

1. 下载本仓库到 Flarum 的 `extensions` 目录：
```bash
cd /path/to/flarum/extensions
git clone https://github.com/wszdb/flarum-homefilter.git wszdb-homefilter
```

2. 安装依赖并编译前端资源：
```bash
cd wszdb-homefilter
composer install --no-dev
npm install
npm run build
```

3. 在 Flarum 后台启用插件

## ⚙️ 配置说明

### 后台设置

进入 **管理后台 → 扩展 → Home Filter** 进行配置：

1. **过滤关键词**
   - 输入需要限制的关键词，多个关键词用英文逗号分隔
   - 示例：`广告,推广,营销,spam`
   - 匹配规则：帖子标题包含任一关键词即被识别

2. **关键词帖子数量限制**
   - 设置首页最多显示多少个包含关键词的帖子
   - 默认值：5
   - 取值范围：1-50

### 工作原理

```
原始首页（20个帖子）
├─ 包含关键词的帖子：10个
└─ 不包含关键词的帖子：10个

↓ 应用过滤（限制5个）

过滤后首页（20个帖子）
├─ 包含关键词的帖子：5个 ✓
└─ 不包含关键词的帖子：15个 ✓（自动补充）
```

## 🔧 技术实现

### 核心机制

1. **SQL 查询层过滤**
   - 在数据库查询阶段预先过滤
   - 使用子查询统计关键词帖子数量
   - 自动扩大查询范围以补偿被过滤的帖子

2. **数据处理层精确控制**
   - 对查询结果进行二次处理
   - 确保关键词帖子数量精确符合限制
   - 保证返回帖子总数与原始请求一致

### 兼容性

- ✅ Flarum 1.8.x
- ✅ Flarum 2.0+ (理论兼容，需测试)
- ✅ PHP 8.0+
- ✅ MySQL 5.7+ / MariaDB 10.2+

### 性能优化

- 仅在首页（无搜索/标签过滤）时启用
- 使用数据库索引加速关键词匹配
- 避免全表扫描，查询效率高

## 📁 项目结构

```
flarum-homefilter/
├── src/
│   └── Filter/
│       ├── DiscussionFilter.php      # SQL查询层过滤器
│       └── DiscussionProcessor.php   # 数据处理层过滤器
├── js/
│   ├── src/
│   │   ├── admin/
│   │   │   └── index.js              # 管理后台界面
│   │   └── forum/
│   │       └── index.js              # 前台扩展
│   └── dist/                         # 编译后的JS文件
├── locale/
│   ├── en.json                       # 英文语言包
│   └── zh-Hans.json                  # 简体中文语言包
├── composer.json                     # Composer配置
├── extend.php                        # 扩展注册文件
├── package.json                      # NPM配置
└── webpack.config.js                 # Webpack编译配置
```

## 🛠️ 开发指南

### 本地开发

1. 克隆仓库：
```bash
git clone https://github.com/wszdb/flarum-homefilter.git
cd flarum-homefilter
```

2. 安装依赖：
```bash
composer install
npm install
```

3. 监听文件变化并自动编译：
```bash
npm run watch
```

4. 生产环境编译：
```bash
npm run build
```

### 代码规范

- PHP 代码遵循 PSR-12 标准
- JavaScript 使用 ES6+ 语法
- 提交前请运行 `composer validate` 检查配置

## 🐛 问题反馈

遇到问题？请提交 [Issue](https://github.com/wszdb/flarum-homefilter/issues)

提交时请包含：
- Flarum 版本
- PHP 版本
- 插件版本
- 详细的错误信息或截图

## 📝 更新日志

### v1.0.0 (2025-10-20)

- 🎉 初始版本发布
- ✨ 实现关键词过滤功能
- 🎨 添加管理后台配置界面
- 🌐 支持中英文双语

## 📄 开源协议

本项目采用 [MIT License](LICENSE) 开源协议。

## 🙏 致谢

感谢 [Flarum](https://flarum.org) 社区的支持！

---

**Made with ❤️ by [wszdb](https://github.com/wszdb)**
