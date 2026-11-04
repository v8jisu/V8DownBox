# V8 Download Box

![License](https://img.shields.io/badge/License-GPL--2.0--or--later-blue)
![Version](https://img.shields.io/badge/Version-1.4.1-green)
![WordPress](https://img.shields.io/badge/WordPress-5.0%2B-21759B)
![PHP](https://img.shields.io/badge/PHP-7.0%2B-777BB4)

资源下载类博客的 WordPress 下载信息框插件：在文章底部渲染统一风格的下载信息框，
支持 10 种网盘/下载通道、回复可见、需登录下载、下载次数统计、底部悬浮条与侧栏小组件。

**English**: A download-info box plugin for resource blogs — 10 net-disk channels,
comment-gated downloads, anti-refresh counter, sticky bar & sidebar widget.

## 功能

- **10 种下载通道**：本地直链 / 百度网盘 / 蓝奏云 / 阿里云盘 / 夸克网盘 / 天翼云盘 / 123云盘 / 城通网盘 / 磁力链接 / 迅雷
- **回复可见 / 登录可见**：可全站生效，也可单篇文章单独设置；匿名访客按"同一行评论的邮箱+昵称双匹配"校验
- **下载次数统计**：点击下载实时 +1、防刷新；计数显示在下载框标题栏
- **三个前台模块独立开关**：文章底部下载框 / 底部悬浮条 / 侧栏下载信息 widget
- **紧急暂停**：一键暂停全站下载（前台提示"维护中"，文章数据与配置不受影响）
- **旧插件兼容**：`wp_post_meta_download_info()` 全局函数与 `wb_dlip_html` 过滤器沿用旧签名

## 环境要求

- WordPress 5.0+（侧栏组件使用标准 WP_Widget 注册，兼容 WP 5.8+ 区块编辑器）
- PHP 7.0+

## 安装

**方式一：git**

```bash
cd wp-content/plugins
git clone https://github.com/v8jisu/V8DownBox.git v8jisu-download-box
```

**方式二：Release zip**

到 [Releases](https://github.com/v8jisu/V8DownBox/releases) 下载
`v8jisu-download-box-x.x.x.zip`，后台「插件 → 安装插件 → 上传插件」。

**方式三：GitHub Download ZIP**

仓库页 Code → Download ZIP，解压出的目录整个放到 `wp-content/plugins/`。

激活后：

1. **设置 → V8 Download Box** 完成全站配置
2. 文章编辑页会出现「下载设置」面板，按文章填写各通道链接

### 更新

用新版本目录覆盖原目录即可，文章数据与配置不受影响。

## 使用

### 全站设置（设置 → V8 Download Box）

| 选项 | 说明 |
|---|---|
| 启用下载功能 | 在文章底部追加下载信息框（总开关） |
| 紧急暂停 | 一键暂停全站下载：前台显示"维护中"、AJAX 拒绝取链，取消勾选即恢复 |
| 模块开关 | 文章底部下载框 / 悬浮下载条 / 侧栏下载信息，三个模块互不影响 |
| 需登录后下载 | 未登录访客点击下载时提示先登录 |
| 需回复后下载 | 全站生效；单篇文章可在编辑页单独设置"回复后下载" |
| 显示下载次数 | 在下载框标题栏显示累计下载次数（点击后实时 +1） |
| 底部悬浮条 | 关闭 / 页面顶部 / 吸附底部 |
| 版权说明 | 下载框内展示的说明文案，留空显示默认 |

### 文章下载信息（编辑页「下载设置」面板）

勾选"启用下载" → 填写各通道链接（百度网盘可另填提取码）→ 选择免费或"回复后下载"。
**未填写任何链接的文章不会渲染任何下载模块**（下载框/侧栏/悬浮条统一判定）。

### 通道与元数据键

| 通道 | post meta 键 |
|---|---|
| 本地直链 | `wb_down_local_url` |
| 百度网盘 | `wb_down_url`（提取码 `wb_down_pwd`） |
| 蓝奏云 | `wb_down_url_lanzou` |
| 阿里云盘 | `wb_down_url_ali` |
| 夸克网盘 | `wb_down_url_quark` |
| 天翼云盘 | `wb_down_url_tianyi` |
| 123云盘 | `wb_down_url_123pan` |
| 城通网盘 | `wb_down_url_ct` |
| 磁力链接 | `wb_down_url_magnet` |
| 迅雷 | `wb_down_url_xunlei` |

辅助字段：下载开关 `wb_dl_type`（0 关 / 1 开）；下载模式 `wb_dl_mode`
（0 免费 / 1 回复可见 / 2 付费——旧数据兼容值，当前不可用）。

## 数据兼容（从旧版下载框插件切换）

- 文章数据（`wb_*` 元数据、`post_downs` 计数）**完全同键**：停用旧插件 → 启用本插件即可，零迁移
- 侧栏 widget 采用标准 WP_Widget 注册（id_base `v8dl-download-info`）；如旧插件已配置侧栏组件，需在「小工具」页重新添加一次「#下载信息#」
- 全站设置存储键为 `v8dl_option`，激活后在设置页保存一次即可
- 兼容函数 `wp_post_meta_download_info( $post_id )` 与 `wb_dlip_html` 过滤器签名不变，主题无需改动

## 目录结构

```
v8jisu-download-box/
├── v8jisu-download-box.php        # 入口：常量、类加载、旧函数兼容
├── includes/
│   ├── class-v8dl-admin.php       # 后台：设置页 + 文章元数据面板
│   └── class-v8dl-front.php       # 前台：下载框 / AJAX 取链 / 悬浮条 / widget / 评论校验
├── templates/
│   ├── box.php                    # 文章底部下载框
│   ├── widget.php                 # 侧栏小组件
│   ├── sticky.php                 # 底部悬浮条
│   └── meta-box.php               # 编辑页「下载设置」面板
└── assets/                        # 前后台样式 / 脚本 / SVG 图标
```

## 相对旧版的重写要点

- 渲染模板全部 return 字符串，不再使用 `ob_start`——在 `the_content` 过滤器中调用
  `ob_start`，在页面缓存等输出缓冲回调里执行会触发 PHP Fatal error
  （`Cannot use output buffering in output buffering display handlers`），导致文章页白屏
- 评论门校验改用 `$wpdb->prepare`（cookie 值不直接拼进 SQL）
- 元数据面板加 nonce；设置保存白名单化 sanitize；前端弹窗 url/密码输出转义；
  模板加 ABSPATH 守卫；下载计数防刷新

## 常见问题

**Q: 任何主题都能用吗？**

可以。三个前台模块都是标准 `the_content` 过滤器 / `wp_footer` / WP_Widget 挂载，
不依赖特定主题；个别主题样式冲突时在主题 CSS 里微调即可。

**Q: 和页面缓存 / CDN 兼容吗？**

兼容。渲染全程 return 字符串、无输出缓冲，页面缓存（fastcgi_cache / CDN）下工作正常；
紧急暂停时 AJAX 返回明确 code 4，让拿着旧缓存页面的访客也看到"维护中"而不是报错。

**Q: 如何自定义外观？**

`templates/` 与 `assets/` 均可直接改；下载框结构由 `templates/box.php` 输出，
配合主题 CSS 覆盖即可，无需动主逻辑。

**Q: 下载次数会被恶意刷吗？**

AJAX 侧有防刷新控制，正常博客量级足够；如需更严格可再加主题层验证。

**Q: PHP 8 能用吗？**

代码为经典 PHP 风格，生产实测于 PHP 7.4；PHP 8.x 如遇兼容问题欢迎提 Issue。

## 版本历史（摘要，详见主文件头注释）

- **1.4.1** 无任何链接的文章不渲染任何下载模块；侧栏组件仅在文章页显示
- **1.4.0** 下载次数移至下载框标题栏（点击实时 +1），侧栏不再重复展示
- **1.3.1** 侧栏组件改用 WP_Widget 标准注册
- **1.3.0** 新增蓝奏云/阿里云盘/夸克/天翼/123 五通道；插件更名 V8 Download Box
- **1.2.0** 紧急暂停总闸 + 三模块独立开关
- **1.1.0** 安全加固：评论门双匹配、AJAX 校验、防刷计数、nonce、sanitize、ABSPATH、转义

## 反馈与贡献

Issue / PR 欢迎：[Issues](https://github.com/v8jisu/V8DownBox/issues)

## License

[GPL-2.0-or-later](LICENSE)
