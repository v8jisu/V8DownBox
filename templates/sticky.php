<?php
/**
 * 悬浮下载条模板。必须 return 字符串，禁止 ob_start。
 *
 * 可用变量：$sticky_mode
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( $sticky_mode <= 0 ) {
	return '';
}

$html = '';

$html .= '<div class="wb-sticky-bar' . ( 2 === (int) $sticky_mode ? ' at-bottom' : '' ) . '" id="J_downloadBar">';
$html .= '<div class="wbsb-inner pw">';
$html .= '<div class="sb-title">' . esc_html( get_the_title() ) . '</div>';
$html .= '<div class="ctrl-box">';
$html .= '<a class="wb-btn wb-btn-outlined wb-btn-download" href="#J_DLIPPCont">';
$html .= '<svg class="wb-icon-dlipp wbsico-dlipp-download"><use xlink:href="#wbsico-dlipp-download"></use></svg> <span>去下载</span>';
$html .= '</a>';
$html .= '</div>';
$html .= '</div>';
$html .= '</div>';

return $html;
