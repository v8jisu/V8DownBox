<?php
/**
 * Plugin Name: V8 Download Box
 * Description: 资源下载类博客的下载信息框：本地/百度网盘/蓝奏云/阿里云盘/夸克网盘/天翼云盘/123云盘/城通网盘/磁力/迅雷多通道下载，支持回复后下载；数据兼容同类旧版下载框插件（wb_* 元数据零迁移）。
 * Version:     1.4.1
 * Author:      v8jisu
 * License:     GPL-2.0-or-later
 *
 * 实现说明（参考同类下载框插件的交互重写）：
 *  - 渲染不使用 ob_start（在 the_content 过滤器中调用 ob_start，在页面缓存的
 *    输出缓冲回调里执行会触发 PHP Fatal error: "Cannot use output buffering in
 *    output buffering display handlers"，导致文章页空白），模板全部改为 return 字符串。
 *  - 评论校验 SQL 使用 $wpdb->prepare，cookie 值不直接拼进 SQL。
 *  - 数据兼容：wb_* 元数据、post_downs 计数与同类旧版下载框插件同键，可直接接管；
 *    侧栏 widget 使用独立 id_base（v8dl-download-info），从旧版切换需在小工具页重新添加一次。
 *
 * 1.1.0 加固：评论门槛改同评论行邮箱+昵称双匹配；AJAX 校验文章开关与通道链接、
 * 防刷计数；元数据面板加 nonce；设置保存加 sanitize；SVG sprite 注入前校验；
 * 模板加 ABSPATH 守卫；前台弹窗 url/pwd 输出转义。
 *
 * 1.2.0 新增开关控制：紧急总闸 pause_all（一键暂停全站下载，前台提示维护中、
 * AJAX 返回 code 4）；模块开关 box_enabled / sticky_enabled / widget_enabled
 * 分别控制下载框、悬浮条、侧栏组件。
 *
 * 1.3.0 插件名改为 V8 Download Box；新增 5 个网盘通道：蓝奏云/阿里云盘/
 * 夸克网盘/天翼云盘/123云盘（元数据 wb_down_url_lanzou/ali/quark/tianyi/123pan，
 * AJAX 通道 rid lanzou/ali/quark/tianyi/pan123，图标随版本自动刷新缓存）。
 *
 * 1.3.1 侧栏组件改用 WP_Widget 标准注册：WP 5.8+ 区块小工具编辑器不显示
 * 老式 wp_register_sidebar_widget 组件，导致后台"找不到"该组件。
 *
 * 1.4.0 下载次数改在文章底部下载框标题栏显示（点击下载实时 +1），
 * 移除侧栏组件中的次数展示——次数与下载动作放在一起更直观。
 *
 * 1.4.1 未填写任何网盘链接的文章不渲染任何下载模块（下载框/侧栏组件/悬浮条
 * 统一由 has_links() 把关）；侧栏组件仅在文章页显示。
 *
 * 版本策略：日常小修只在本 changelog 记录、不升版本号；功能发布或前端资源
 * （js/css/图标）变更时统一升版本——升级同时用于刷新访客端的资源缓存。
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'V8DL_PATH', dirname( __FILE__ ) );
define( 'V8DL_BASE_FILE', __FILE__ );
define( 'V8DL_VER', '1.4.1' );

require_once V8DL_PATH . '/includes/class-v8dl-admin.php';
require_once V8DL_PATH . '/includes/class-v8dl-front.php';

new V8DL_Admin();
new V8DL_Front();

if ( ! function_exists( 'wp_post_meta_download_info' ) ) {
	/**
	 * 兼容旧插件的全局函数：返回某篇文章的下载元数据。
	 */
	function wp_post_meta_download_info( $post_id ) {
		return V8DL_Admin::meta_values( $post_id );
	}
}
