# AGENTS.md

给 AI 编码代理（OpenCode / Codex / Claude Code 等）在本仓库工作时的指引。

## 项目速览

WordPress 下载信息框插件：文章底部下载框、AJAX 取链、底部/顶部悬浮条、侧栏小组件。
10 种下载通道（本地/百度/蓝奏/阿里/夸克/天翼/123云盘/城通/磁力/迅雷），回复可见、登录可见、防刷计数、紧急暂停。

- 运行环境：PHP 7.0+ / WordPress 5.0+（生产实测 PHP 7.4 + OPcache + fastcgi_cache 页面缓存）
- 许可：GPL-2.0-or-later

## 目录结构

```
v8jisu-download-box/
├── v8jisu-download-box.php        # 入口：常量、版本、类加载、旧函数兼容、changelog
├── includes/
│   ├── class-v8dl-admin.php       # 后台：设置页 + 文章元数据面板 + 保存/sanitize
│   └── class-v8dl-front.php       # 前台：下载框 / AJAX 取链 / 悬浮条 / widget / 评论校验
├── templates/                     # box.php 下载框 / widget.php 侧栏 / sticky.php 悬浮条 / meta-box.php 编辑面板
├── assets/                        # v8dl-front.js 前台脚本 / wbui 内置 UI 库 / SVG 图标 sprite
└── languages/                     # v8-download-box.pot / en_US 翻译
```

## 铁律（违反会出事故）

1. **模板必须 return 字符串，禁止 ob_start / 任何输出缓冲。**
   渲染跑在 `the_content` 过滤器里；页面缓存（fastcgi_cache / CDN）的输出缓冲回调中再调
   `ob_start` 会触发 `Cannot use output buffering in output buffering display handlers`
   → 文章页白屏。新模板一律照抄 `templates/box.php` 的写法。
2. **数据键名全部冻结，不可改名**：`wb_*` post meta、`post_downs` 计数、`v8dl_option`
   设置键与同类旧版下载框插件同键（零迁移接管），widget id_base `v8dl-download-info`
   同样冻结。改名 = 丢用户已有数据。
3. **AJAX 响应是与前端的契约**：`{code, is_login, data:{url, pwd, post_downs}}`，
   code 0 成功 / 1 无效或未开下载 / 2 需登录 / 3 需评论 / 4 紧急暂停中。
   改结构必须同步 `assets/v8dl-front.js` 的 switch。
4. **取链门禁顺序不可削弱**：紧急总闸(code 4) → 文章存在且 `publish` 且 type=post →
   通道白名单 → 登录门(code 2) → 评论门(code 3) → 文章开关与通道链接非空。
   任何重构不得跳过或调换。
5. **一切 SQL 走 `$wpdb->prepare`**。评论门读的 cookie 是客户端输入，匿名访客按
   同一行评论的邮箱+昵称双匹配校验，不要简化成单匹配。
6. **转义纪律**：模板输出 `esc_html / esc_attr / esc_url`；JS 拼 HTML 前过 `escHtml()`；
   新增输出点默认先转义，phpcs ignore 不得滥用。

## 有意设计（不要当 bug 修）

- **网盘 URL / 提取码不进页面 HTML**：只在通过门禁后经 admin-ajax 下发，
  页面缓存因此不泄链。新功能涉及链接展示时保持此模式。
- **公共 AJAX 无 nonce**：nonce 与页面缓存冲突（缓存页里的 nonce 必然过期）；
  端点只读，唯一写操作是已节流的计数。
- **计数防刷 = 失败不计 + transient 60 秒同源同文去重**：计数仅装饰展示，无需更强。
- **URL 字段保存走协议白名单**（http/https/magnet/thunder；空值与 `/` 开头的站点
  相对路径保留），防止 `javascript:` 伪协议入库后被前台原样输出。
- **SVG sprite 注入前有 safeSvg 形态校验**：localStorage 可被同源 XSS 篡改，
  校验拦 `<script>` / `javascript:` / `on*=`，防借道持久化。
- **生效文章类型默认仅 post**：设置页可勾选；AJAX 门禁与前台 is_singular 挂载
  统一走 `V8DL_Admin::post_types()`，调整默认值时须同步评估取链门禁暴露面。
- **i18n 约定**：用户可见字符串一律包 `v8-download-box` text domain（默认源串为中文）；
  JS 文案经 `v8dl_config.i18n` 下发（键=中文源串）；新增字符串必须同步补 en_US 表
  与 pot（生成脚本见发布流程）。
- **旧版「付费下载」`wb_dl_mode=2` 是兼容值**：一律按免费处理，勿恢复其逻辑。

## 版本与发布

- 版本号三处同步：主文件头 `Version:`、`V8DL_VER` 常量、README 徽章。
- 升版规则：功能变更或前端资源（js/css/图标）变更必升（兼做访客端缓存刷新）；
  纯文档不升。
- 每次功能变更在**主文件头 changelog** 加一行（README 的是摘要，不是记录处）。
- 发布：commit → tag →
  `git archive --format=zip --prefix=v8jisu-download-box/ <tag> -o v8jisu-download-box-<ver>.zip`
  → 上传 GitHub Release。

## 改动后自测清单

1. `php -l` 全部 php 文件
2. 草稿/待审/私密文章 → AJAX 必须返回 code 1（不泄链）
3. 已发布文章：下载框渲染、点按钮取链成功、60 秒内重复点击计数不变
4. 侧栏 widget 仅文章页显示（首页/列表页不出现）；悬浮条按 `sticky_mode` 生效
5. 紧急暂停打开 → 前台提示维护中、AJAX 返回 code 4；取消即恢复
6. 未填任何链接的文章：三个模块全不渲染

## 其他约定

- 代码风格：WordPress 默认（tab 缩进），注释用中文。
- 兼容函数 `wp_post_meta_download_info()` 与 `wb_dlip_html` 过滤器签名冻结（主题在用）。
- 提交信息用英文祈使句。
