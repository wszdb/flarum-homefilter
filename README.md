# Flarum Home Filter Extension



![MIT License](https://img.shields.io/badge/license-MIT-blue.svg)
![Flarum](https://img.shields.io/badge/flarum-^1.8.0-orange.svg)

<h4>📖 English | <a href="https://github.com/wszdb/flarum-homefilter/blob/main/README.zh-CN.md">简体中文</a></h4>

🎯 **Intelligent Home Page Post Filter Extension** - Limits the number of specific post types displayed on the home page via keywords to maintain content diversity.

## ✨ Features



* 🔍 **Intelligent Keyword Matching** - Supports configuration of multiple keywords separated by commas

* 📊 **Precise Quantity Control** - Flexibly set the maximum number of posts containing keywords (1-50)

* 🔄 **Automatic Compensation Mechanism** - Maintains the total number of posts on the home page

* 🌐 **Multi-Language Support** - Built-in Chinese and English language packs

* ⚡ **High-Performance Design** - Dual filtering mechanism (SQL Query Layer + Data Processing Layer)

* 🎨 **User-Friendly Admin Interface** - Visual configuration in the backend, no code modification required

## 📋 Use Cases

Ideal for the following scenarios:



* Limiting the exposure of advertisement and promotion posts on the home page

* Controlling the display quantity of specific topics (e.g., recruitment, second-hand transactions)

* Balancing the display ratio of different content types

* Enhancing home page content diversity and user experience

## 🚀 Installation Methods

### Method 1: Composer Installation (Recommended)



```
composer require wszdb/flarum-homefilter

php flarum cache:clear
```

### Method 2: Manual Installation



1. Download this repository to the `extensions` directory of Flarum:



```
cd /path/to/flarum/extensions

git clone https://github.com/wszdb/flarum-homefilter.git wszdb-homefilter
```



1. Install dependencies and compile frontend assets:



```
cd wszdb-homefilter

composer install --no-dev

npm install

npm run build
```



1. Enable the extension in the Flarum backend

## ⚙️ Configuration Instructions

### Backend Settings

Navigate to **Admin Dashboard → Extensions → Home Filter** to configure:



1. **Filter Keywords**

* Enter keywords to be restricted, with multiple keywords separated by English commas

* Example: `Advertisement,Promotion,Marketing,Spam`

* Matching Rule: A post is identified if its title contains any of the keywords

1. **Keyword Post Quantity Limit**

* Set the maximum number of posts containing keywords to be displayed on the home page

* Default Value: 5

* Value Range: 1-50

### Working Principle



```
Original Home Page (20 posts)

├─ Posts containing keywords: 10

└─ Posts without keywords: 10

↓ After Applying Filter (Limit: 5)

Filtered Home Page (20 posts)

├─ Posts containing keywords: 5 ✓

└─ Posts without keywords: 15 ✓ (Automatically supplemented)
```

## 🔧 Technical Implementation

### Core Mechanisms



1. **SQL Query Layer Filtering**

* Pre-filters during the database query phase

* Uses subqueries to count the number of keyword-containing posts

* Automatically expands the query scope to compensate for filtered posts

1. **Data Processing Layer Precision Control**

* Performs secondary processing on query results

* Ensures the number of keyword-containing posts strictly meets the limit

* Guarantees the total number of returned posts matches the original request

### Compatibility



* ✅ Flarum 1.8.x

* ✅ Flarum 2.0+ (Theoretically compatible, testing required)

* ✅ PHP 8.0+

* ✅ MySQL 5.7+ / MariaDB 10.2+

### Performance Optimization



* Only enabled on the home page (no search/tag filtering applied)

* Uses database indexes to speed up keyword matching

* Avoids full-table scans for high query efficiency

## 📁 Project Structure



```
flarum-homefilter/

├── src/

│   └── Filter/

│       ├── DiscussionFilter.php      # SQL Query Layer Filter

│       └── DiscussionProcessor.php   # Data Processing Layer Filter

├── js/

│   ├── src/

│   │   ├── admin/

│   │   │   └── index.js              # Admin Dashboard Interface

│   │   └── forum/

│   │       └── index.js              # Frontend Extension

│   └── dist/                         # Compiled JS Files

├── locale/

│   ├── en.json                       # English Language Pack

│   └── zh-Hans.json                  # Simplified Chinese Language Pack

├── composer.json                     # Composer Configuration

├── extend.php                        # Extension Registration File

├── package.json                      # NPM Configuration

└── webpack.config.js                 # Webpack Compilation Configuration
```

## 🛠️ Development Guide

### Local Development



1. Clone the repository:



```
git clone https://github.com/wszdb/flarum-homefilter.git

cd flarum-homefilter
```



1. Install dependencies:



```
composer install

npm install
```



1. Watch for file changes and auto-compile:



```
npm run watch
```



1. Compile for production environment:



```
npm run build
```

### Code Standards



* PHP code follows the PSR-12 standard

* JavaScript uses ES6+ syntax

* Run `composer validate` to check configurations before submission

## 🐛 Issue Reporting

Encounter an issue? Please submit an [Issue](https://github.com/wszdb/flarum-homefilter/issues)

Include the following information when submitting:



* Flarum version

* PHP version

* Extension version

* Detailed error information or screenshots

## 📄 Open Source License

This project is licensed under the [MIT License](LICENSE).

## 🙏 Acknowledgements

Thanks to the [Flarum](https://flarum.org) community for their support!



***

**Made with ❤️ by&#x20;**[wszdb](https://github.com/wszdb)

> （注：文档部分内容可能由 AI 生成）

