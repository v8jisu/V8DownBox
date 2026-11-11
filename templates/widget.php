<?php
/**
 * 侧栏下载信息 widget 模板。必须 return 字符串，禁止 ob_start。
 *
 * 可用变量：$need_login, $is_login, $need_comment, $is_comment
 * （1.4.0 起下载次数不再显示在侧栏组件中，改在文章底部下载框标题栏显示）
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$html = '';

$html .= '<section class="widget widget-wbdl" id="J_widgetWBDownload">';
$html .= '<h3 class="widgettitle">' . esc_html__( '下载', 'v8-download-box' ) . '</h3>';
$html .= '<div class="widget-main">';

if ( $need_login && ! $is_login ) {
	$html .= '<a href="' . esc_url( wp_login_url( get_permalink() ) ) . '"><svg class="wb-icon-dlipp wbsico-dlipp-download"><use xlink:href="#wbsico-dlipp-download"></use></svg> <span>' . esc_html__( '去下载', 'v8-download-box' ) . '</span></a>';
} elseif ( $need_comment && ! $is_comment ) {
	$html .= '<p class="wb-tips">' . esc_html__( '*该资源需回复评论后下载 ', 'v8-download-box' ) . '<a href="#comments">' . esc_html__( '去评论', 'v8-download-box' ) . '</a></p>';
} else {
	$html .= '<a class="wb-btn wb-btn-outlined wb-btn-download" href="#J_DLIPPCont"><svg class="wb-icon-dlipp wbsico-dlipp-download"><use xlink:href="#wbsico-dlipp-download"></use></svg> <span>' . esc_html__( '去下载', 'v8-download-box' ) . '</span></a>';
}

$html .= '</div>';
$html .= '</section>';

return $html;
