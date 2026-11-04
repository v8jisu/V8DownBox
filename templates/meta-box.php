<?php
/**
 * 编辑页"下载设置"面板（后台直接输出，无缓冲问题）。
 *
 * 可用变量：$meta, $switch, $dl_mode
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<div style=" display:none;">
    <svg aria-hidden="true" style="position: absolute; width: 0; height: 0; overflow: hidden;" version="1.1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink">
        <defs>
            <symbol id="sico-upload" viewBox="0 0 16 13">
                <path d="M9 8v3H7V8H4l4-4 4 4H9zm4-2.9V5a5 5 0 0 0-5-5 4.9 4.9 0 0 0-4.9 4.3A4.4 4.4 0 0 0 0 8.5C0 11 2 13 4.5 13H12a4 4 0 0 0 1-7.9z" fill="#666" fill-rule="evenodd"/>
            </symbol>
        </defs>
    </svg>
</div>

<div class="wb-post-sitting-panel">
    <?php wp_nonce_field( 'v8dl_save_meta', 'v8dl_meta_nonce' ); ?>
    <div class="selector-bar switch-bar">
        <label><strong>下载功能</strong> <input class="wb-switch" id="J_DLIPP_SWITCH" type="checkbox" data-target="#J_DIPPMain" name="wb_dl_type"<?php echo $switch ? ' checked' : ''; ?> value="1"></label>
    </div>

    <div class="wbsp-main default-hidden-box<?php echo $switch ? ' active' : ''; ?>" id="J_DIPPMain">
        <h3><strong>文件上传方式</strong></h3>

        <label class="wb-post-sitting-item section-upload">
            <span class="wb-form-label">上传文件</span>
            <div class="wbs-upload-box">
                <input class="wbs-input upload-input" type="text" placeholder="点击右侧上传按钮或者直接贴入下载链接" name="wb_down_local_url" id="wb_down_local_url" value="<?php echo esc_attr( $meta['wb_down_local_url'] ); ?>">
                <button type="button" class="wbs-btn wbs-upload-btn">
                    <svg class="wb-icon sico-upload"><use xlink:href="#sico-upload"></use></svg><span>上传</span>
                </button>
            </div>
        </label>

        <label class="wb-post-sitting-item">
            <span class="wb-form-label">城通网盘</span>
            <input class="wbs-input" type="text" name="wb_down_url_ct" placeholder="留意填写完整url" value="<?php echo esc_attr( $meta['wb_down_url_ct'] ); ?>">
        </label>

        <div class="wb-post-sitting-item dlipp-item-bdp">
            <label class="bdp-url-item">
                <span class="wb-form-label">百度网盘</span>
                <input class="wbs-input" type="text" name="wb_down_url" placeholder="留意填写完整url" id="wb_down_url" value="<?php echo esc_attr( $meta['wb_down_url'] ); ?>">
            </label>
            <label class="bdp-psw-item">
                <span class="wb-form-label">网盘密码</span>
                <input class="wbs-input" type="text" name="wb_down_pwd" placeholder="" id="wb_down_pwd" value="<?php echo esc_attr( $meta['wb_down_pwd'] ); ?>">
            </label>
        </div>
        <div class="wb-tip-txt">填入百度网盘客户端或者网页端分享链接及提取码，可自动识别链接和提取码填入哦。</div>

        <label class="wb-post-sitting-item">
            <span class="wb-form-label">蓝奏云</span>
            <input class="wbs-input" type="text" name="wb_down_url_lanzou" placeholder="例如 https://wwx.lanzouxx.com/xxxxxx" value="<?php echo esc_attr( $meta['wb_down_url_lanzou'] ); ?>">
        </label>

        <label class="wb-post-sitting-item">
            <span class="wb-form-label">阿里云盘</span>
            <input class="wbs-input" type="text" name="wb_down_url_ali" placeholder="例如 https://www.alipan.com/s/xxxxxx" value="<?php echo esc_attr( $meta['wb_down_url_ali'] ); ?>">
        </label>

        <label class="wb-post-sitting-item">
            <span class="wb-form-label">夸克网盘</span>
            <input class="wbs-input" type="text" name="wb_down_url_quark" placeholder="例如 https://pan.quark.cn/s/xxxxxxxx" value="<?php echo esc_attr( $meta['wb_down_url_quark'] ); ?>">
        </label>

        <label class="wb-post-sitting-item">
            <span class="wb-form-label">天翼云盘</span>
            <input class="wbs-input" type="text" name="wb_down_url_tianyi" placeholder="例如 https://cloud.189.cn/t/xxxxxx" value="<?php echo esc_attr( $meta['wb_down_url_tianyi'] ); ?>">
        </label>

        <label class="wb-post-sitting-item">
            <span class="wb-form-label">123云盘</span>
            <input class="wbs-input" type="text" name="wb_down_url_123pan" placeholder="例如 https://www.123pan.com/s/xxxxxx" value="<?php echo esc_attr( $meta['wb_down_url_123pan'] ); ?>">
        </label>

        <label class="wb-post-sitting-item">
            <span class="wb-form-label">磁力链接</span>
            <input class="wbs-input" type="text" name="wb_down_url_magnet" placeholder="请输入以magnet:开头的磁力链接" value="<?php echo esc_attr( $meta['wb_down_url_magnet'] ); ?>">
        </label>

        <label class="wb-post-sitting-item">
            <span class="wb-form-label">迅雷下载</span>
            <input class="wbs-input" type="text" name="wb_down_url_xunlei" placeholder="请输入以thunder://开头的迅雷专用下载链接" value="<?php echo esc_attr( $meta['wb_down_url_xunlei'] ); ?>">
        </label>

        <h3><strong>下载方式</strong></h3>

        <div class="wb-post-sitting-item">
            <span class="wb-form-label">选择方式</span>
            <div class="selector-bar">
                <label><input class="wbs-radio" type="radio" name="wb_dl_mode"<?php checked( '0', (string) $dl_mode ); ?> value="0"> 免费下载</label>
                <label><input class="wbs-radio" type="radio" name="wb_dl_mode"<?php checked( '1', (string) $dl_mode ); ?> value="1"> 回复后下载</label>
                <label><input class="wbs-radio" type="radio" name="wb_dl_mode" disabled<?php checked( '2', (string) $dl_mode ); ?> value="2"> 付费下载（暂不可用）</label>
            </div>
        </div>
        <div class="wb-tip-txt">旧数据中若存在"付费下载"设置，将按免费下载处理，如需恢复请告知站长。</div>
    </div>
</div>
