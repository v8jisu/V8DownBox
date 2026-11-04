<?php
/**
 * 后台：全局设置页 + 文章编辑页"下载设置"面板。
 */

class V8DL_Admin {

	public static $option_name = 'v8dl_option';

	public static $meta_fields = array(
		'wb_dl_type',          // 下载开关
		'wb_dl_mode',          // 0 免费 / 1 回复后下载 / 2 付费（旧数据兼容，当前不可用）
		'wb_down_local_url',   // 本地直链
		'wb_down_url',         // 百度网盘
		'wb_down_pwd',         // 百度网盘提取码
		'wb_down_url_lanzou',  // 蓝奏云
		'wb_down_url_ali',     // 阿里云盘
		'wb_down_url_quark',   // 夸克网盘
		'wb_down_url_tianyi',  // 天翼云盘
		'wb_down_url_123pan',  // 123云盘
		'wb_down_url_ct',      // 城通网盘
		'wb_down_url_magnet',  // 磁力链接
		'wb_down_url_xunlei',  // 迅雷链接
	);

	/**
	 * 链接类字段的协议白名单（纵深防御：拒绝 javascript: 等伪协议入库，
	 * 避免其经前台 window.open / <a href> 原样输出）。
	 */
	public static $scheme_whitelist = array(
		'wb_down_local_url'  => array( 'http', 'https' ),
		'wb_down_url'        => array( 'http', 'https' ),
		'wb_down_url_lanzou' => array( 'http', 'https' ),
		'wb_down_url_ali'    => array( 'http', 'https' ),
		'wb_down_url_quark'  => array( 'http', 'https' ),
		'wb_down_url_tianyi' => array( 'http', 'https' ),
		'wb_down_url_123pan' => array( 'http', 'https' ),
		'wb_down_url_ct'     => array( 'http', 'https' ),
		'wb_down_url_magnet' => array( 'magnet' ),
		'wb_down_url_xunlei' => array( 'thunder' ),
	);

	public function __construct() {
		if ( is_admin() ) {
			add_action( 'admin_menu', array( $this, 'admin_menu' ) );
			add_action( 'admin_init', array( $this, 'admin_init' ) );
			add_action( 'add_meta_boxes', array( $this, 'add_meta_box' ) );
			add_action( 'save_post', array( $this, 'save_meta' ) );
		}
	}

	/**
	 * 全局设置（带进程内缓存）。键名与旧插件 dlip_option 一致，切换时由安装脚本迁移旧值。
	 */
	public static function cnf( $key = null, $default = null ) {
		static $option = null;

		if ( null === $option ) {
			$option = wp_parse_args(
				(array) get_option( self::$option_name, array() ),
				array(
					'switch'         => 1,
					'pause_all'      => 0,
					'box_enabled'    => 1,
					'sticky_enabled' => 1,
					'widget_enabled' => 1,
					'need_member'    => 0,
					'need_comment'   => 0,
					'display_count'  => 0,
					'sticky_mode'    => 0,
					'btn_align'      => 0,
					'remark'         => '',
				)
			);
		}

		if ( null === $key ) {
			return $option;
		}

		return isset( $option[ $key ] ) ? $option[ $key ] : $default;
	}

	/**
	 * 读取一篇文章的下载元数据（与旧插件字段完全一致）。
	 */
	public static function meta_values( $post_id ) {
		$values = array();
		foreach ( self::$meta_fields as $field ) {
			$values[ $field ] = get_post_meta( $post_id, $field, true );
		}
		if ( '' === $values['wb_dl_type'] ) {
			$values['wb_dl_type'] = '0';
		}
		if ( '' === $values['wb_dl_mode'] ) {
			$values['wb_dl_mode'] = '0';
		}
		return $values;
	}

	public function admin_menu() {
		add_options_page(
			'V8 Download Box Settings',
			'V8 Download Box',
			'manage_options',
			'v8dl-pack',
			array( $this, 'render_settings' )
		);
	}

	public function admin_init() {
		register_setting(
			self::$option_name,
			self::$option_name,
			array( 'sanitize_callback' => array( $this, 'sanitize_settings' ) )
		);
	}

	/**
	 * 设置保存白名单化：只保留已知键，布尔项归一为 0/1，sticky_mode 限定 0/1/2。
	 */
	public function sanitize_settings( $input ) {
		$input = is_array( $input ) ? wp_unslash( $input ) : array();
		$old   = (array) get_option( self::$option_name, array() );

		// btn_align 不在设置表单里，未提交时保留旧值，避免保存设置把它重置。
		$btn_align = isset( $input['btn_align'] ) ? (int) (bool) $input['btn_align'] : (int) ! empty( $old['btn_align'] );

		return array(
			'switch'         => empty( $input['switch'] ) ? 0 : 1,
			'pause_all'      => empty( $input['pause_all'] ) ? 0 : 1,
			'box_enabled'    => empty( $input['box_enabled'] ) ? 0 : 1,
			'sticky_enabled' => empty( $input['sticky_enabled'] ) ? 0 : 1,
			'widget_enabled' => empty( $input['widget_enabled'] ) ? 0 : 1,
			'need_member'    => empty( $input['need_member'] ) ? 0 : 1,
			'need_comment'   => empty( $input['need_comment'] ) ? 0 : 1,
			'display_count'  => empty( $input['display_count'] ) ? 0 : 1,
			'sticky_mode'    => ( isset( $input['sticky_mode'] ) && in_array( (int) $input['sticky_mode'], array( 0, 1, 2 ), true ) ) ? (int) $input['sticky_mode'] : 0,
			'btn_align'      => $btn_align,
			'remark'         => isset( $input['remark'] ) ? sanitize_text_field( $input['remark'] ) : '',
		);
	}

	public function render_settings() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}
		$opt = self::cnf();
		?>
		<div class="wrap">
			<h1>V8 Download Box Settings</h1>
			<form method="post" action="options.php">
				<?php settings_fields( self::$option_name ); ?>
				<table class="form-table" role="presentation">
					<tr>
						<th scope="row">启用下载功能</th>
						<td>
							<label><input type="checkbox" name="<?php echo esc_attr( self::$option_name ); ?>[switch]" value="1" <?php checked( $opt['switch'] ); ?>> 在文章底部追加下载信息框</label>
						</td>
					</tr>
					<tr>
						<th scope="row">紧急暂停</th>
						<td>
							<label style="color:#b32d2e;"><input type="checkbox" name="<?php echo esc_attr( self::$option_name ); ?>[pause_all]" value="1" <?php checked( $opt['pause_all'] ); ?>> <strong>一键暂停全站下载</strong>（前台显示“维护中”，AJAX 拒绝取链；文章数据与配置不受影响，取消勾选即恢复）</label>
						</td>
					</tr>
					<tr>
						<th scope="row">模块开关</th>
						<td>
							<label><input type="checkbox" name="<?php echo esc_attr( self::$option_name ); ?>[box_enabled]" value="1" <?php checked( $opt['box_enabled'] ); ?>> 文章底部下载框</label>&nbsp;&nbsp;
							<label><input type="checkbox" name="<?php echo esc_attr( self::$option_name ); ?>[sticky_enabled]" value="1" <?php checked( $opt['sticky_enabled'] ); ?>> 悬浮下载条</label>&nbsp;&nbsp;
							<label><input type="checkbox" name="<?php echo esc_attr( self::$option_name ); ?>[widget_enabled]" value="1" <?php checked( $opt['widget_enabled'] ); ?>> 侧栏下载信息</label>
							<p class="description">分别控制三个前台模块的显示，互不影响；紧急暂停时全部隐藏。</p>
						</td>
					</tr>
					<tr>
						<th scope="row">需登录后下载</th>
						<td><label><input type="checkbox" name="<?php echo esc_attr( self::$option_name ); ?>[need_member]" value="1" <?php checked( $opt['need_member'] ); ?>> 未登录访客点击下载时提示先登录</label></td>
					</tr>
					<tr>
						<th scope="row">需回复后下载</th>
						<td><label><input type="checkbox" name="<?php echo esc_attr( self::$option_name ); ?>[need_comment]" value="1" <?php checked( $opt['need_comment'] ); ?>> 全站生效；单篇文章可在编辑页单独设置"回复后下载"</label></td>
					</tr>
					<tr>
						<th scope="row">显示下载次数</th>
						<td><label><input type="checkbox" name="<?php echo esc_attr( self::$option_name ); ?>[display_count]" value="1" <?php checked( $opt['display_count'] ); ?>> 在文章底部下载框标题栏显示累计下载次数（点击下载后实时 +1）</label></td>
					</tr>
					<tr>
						<th scope="row">底部悬浮条</th>
						<td>
							<label><input type="radio" name="<?php echo esc_attr( self::$option_name ); ?>[sticky_mode]" value="0" <?php checked( $opt['sticky_mode'], 0 ); ?>> 关闭</label>
							<label><input type="radio" name="<?php echo esc_attr( self::$option_name ); ?>[sticky_mode]" value="1" <?php checked( $opt['sticky_mode'], 1 ); ?>> 页面顶部</label>
							<label><input type="radio" name="<?php echo esc_attr( self::$option_name ); ?>[sticky_mode]" value="2" <?php checked( $opt['sticky_mode'], 2 ); ?>> 吸附底部</label>
						</td>
					</tr>
					<tr>
						<th scope="row">版权说明</th>
						<td>
							<input type="text" class="regular-text" name="<?php echo esc_attr( self::$option_name ); ?>[remark]" value="<?php echo esc_attr( $opt['remark'] ); ?>">
							<p class="description">留空则显示默认文案。</p>
						</td>
					</tr>
				</table>
				<?php submit_button( '保存设置' ); ?>
			</form>
		</div>
		<?php
	}

	public function add_meta_box() {
		add_meta_box(
			'v8dl_meta_box',
			'下载设置',
			array( $this, 'render_meta_box' ),
			'post',
			'normal',
			'high'
		);
	}

	public function render_meta_box( $post ) {
		wp_enqueue_style( 'v8dl-admin', plugin_dir_url( V8DL_BASE_FILE ) . 'assets/wbp_admin_dlipp.css', array(), V8DL_VER );
		wp_enqueue_script( 'v8dl-admin', plugin_dir_url( V8DL_BASE_FILE ) . 'assets/wbp_admin_dlipp.js', array(), V8DL_VER, true );

		$meta    = self::meta_values( $post->ID );
		$switch  = $meta['wb_dl_type'];
		$dl_mode = $meta['wb_dl_mode'];

		include V8DL_PATH . '/templates/meta-box.php';
	}

	public function save_meta( $post_id ) {
		if ( ! current_user_can( 'edit_post', $post_id ) || wp_is_post_revision( $post_id ) || wp_is_post_autosave( $post_id ) ) {
			return;
		}

		// 经典编辑器与 Gutenberg 都会把本面板字段随编辑表单提交，此处校验面板自带 nonce。
		if ( ! isset( $_POST['v8dl_meta_nonce'] )
			|| ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['v8dl_meta_nonce'] ) ), 'v8dl_save_meta' ) ) {
			return;
		}

		// 勾选了下载方式但没勾开关（旧插件的兼容逻辑，保留）。
		if ( isset( $_POST['wb_dl_mode'] ) && ! isset( $_POST['wb_dl_type'] ) ) {
			$_POST['wb_dl_type'] = 0;
		}

		foreach ( self::$meta_fields as $field ) {
			if ( ! isset( $_POST[ $field ] ) ) {
				continue;
			}
			$value = sanitize_text_field( trim( wp_unslash( $_POST[ $field ] ) ) );

			// 链接类字段按协议白名单校验，非法协议清空（留空与 / 开头的站点相对路径不受影响）。
			if ( isset( self::$scheme_whitelist[ $field ] ) && '' !== $value && '/' !== substr( $value, 0, 1 ) ) {
				$scheme = strtolower( (string) parse_url( $value, PHP_URL_SCHEME ) );
				if ( ! in_array( $scheme, self::$scheme_whitelist[ $field ], true ) ) {
					$value = '';
				}
			}

			update_post_meta( $post_id, $field, $value );
		}
	}
}
