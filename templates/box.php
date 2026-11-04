<?php
/**
 * 下载信息框模板。注意：本文件必须 return 字符串，禁止使用 ob_start。
 *
 * 可用变量（由 V8DL_Front::render_box 提供）：
 * $dl_info, $need_login, $is_login, $need_comment, $is_comment, $remark_info,
 * $display_count, $count
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$html = '';

$html .= '<div id="J_DLIPPCont" class="dlipp-cont-wp">';
$html .= '<div class="dlipp-cont-inner">';
$html .= '<div class="dlipp-cont-hd">';
$html .= '<svg class="wb-icon-dlipp wbsico-dlipp-local"><use xlink:href="#wbsico-dlipp-local"></use></svg> <span>相关文件下载地址</span>';
if ( ! empty( $display_count ) ) {
	$html .= '<span class="dl-count" style="float:right;font-weight:normal;font-size:12px;color:#999;">已下载<span class="j-wbdl-count">' . (int) $count . '</span>次</span>';
}
$html .= '</div>';

$html .= '<div class="dlipp-cont-bd">';

if ( $need_login && ! $is_login ) {
	$html .= '<div class="wb-tips">该资源需登录后下载，去<a class="link" href="' . esc_url( wp_login_url( get_permalink() ) ) . '">登录</a>?</div>';
} elseif ( $need_comment && ! $is_comment ) {
	$html .= '<div class="wb-tips">*该资源需回复评论后下载，马上去<a class="link" href="#comments">发表评论</a>?</div>';
} else {
	$html .= '<input class="with-psw" style="z-index: 0; opacity: 0; position: absolute; width:20px;" id="WBDL_PSW">';

	foreach ( $dl_info as $rid => $info ) {
		$html .= '<a class="dlipp-dl-btn j-wbdlbtn-dlipp" data-rid="' . esc_attr( $rid ) . '">';
		$html .= '<svg class="wb-icon-dlipp wbsico-dlipp-' . esc_attr( $rid ) . '"><use xlink:href="#wbsico-dlipp-' . esc_attr( $rid ) . '"></use></svg>';
		$html .= '<span>' . esc_html( $info['name'] ) . '</span>';
		$html .= '</a>';
	}
}

$html .= '</div>';

$default_remark = '©下载资源版权归作者所有；本站所有资源均来源于网络，仅供学习使用，请支持正版！';
$html .= '<div class="dlipp-cont-ft">' . ( $remark_info ? esc_html( $remark_info ) : $default_remark ) . '</div>';

$html .= '</div>';
$html .= '</div>';

return $html;
