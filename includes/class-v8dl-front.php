<?php
/**
 * 前台：文章底部下载框、AJAX 取链接、侧栏 widget、底部悬浮条。
 *
 * 关键修复：渲染模板一律 return 字符串（模板内不使用 ob_start / include 捕获），
 * 避免 the_content 在页面缓存等输出缓冲回调内执行时触发
 * "ob_start(): Cannot use output buffering in output buffering display handlers"。
 */

class V8DL_Front {

	public function __construct() {
		$opt = V8DL_Admin::cnf();

		if ( ! empty( $opt['switch'] ) ) {
			// AJAX 始终注册：暂停期间也要给出明确响应（code 4），
			// 让仍持有旧缓存页面（带按钮）的访客看到"维护中"而不是报错。
			add_action( 'wp_enqueue_scripts', array( $this, 'enqueue' ), 50 );
			add_action( 'wp_ajax_v8dl_front', array( $this, 'ajax' ) );
			add_action( 'wp_ajax_nopriv_v8dl_front', array( $this, 'ajax' ) );

			$paused = ! empty( $opt['pause_all'] );

			if ( ! $paused && ! empty( $opt['box_enabled'] ) ) {
				add_filter( 'the_content', array( $this, 'append_box' ), 40 );
			}
			if ( ! $paused && ! empty( $opt['sticky_enabled'] ) ) {
				add_action( 'wp_footer', array( $this, 'sticky_bar' ), 50 );
			}
			if ( ! $paused && ! empty( $opt['widget_enabled'] ) ) {
				add_action( 'widgets_init', array( $this, 'widgets_init' ) );
			}
		}

		// 兼容旧插件的自定义渲染过滤器。
		add_filter( 'wb_dlip_html', array( __CLASS__, 'down_html' ) );
	}

	public static function down_html( $with_title = true ) {
		return self::render_box( get_the_ID(), (bool) $with_title );
	}

	public function append_box( $content ) {
		if ( ! is_singular( 'post' ) || ! is_main_query() ) {
			return $content;
		}
		// 摘要/第三方过滤器场景不追加（沿用旧插件的白名单）。
		if ( doing_filter( 'get_the_excerpt' ) || doing_filter( 'the_excerpt' ) || doing_filter( 'wpjam_html' ) ) {
			return $content;
		}

		return $content . self::render_box( get_the_ID(), true );
	}

	/**
	 * 文章是否至少填写了一个下载通道链接。未填链接的文章一律不渲染任何下载模块。
	 */
	private static function has_links( $meta ) {
		return (bool) ( $meta['wb_down_url'] || $meta['wb_down_local_url'] || $meta['wb_down_url_ct']
			|| $meta['wb_down_url_magnet'] || $meta['wb_down_url_xunlei']
			|| $meta['wb_down_url_lanzou'] || $meta['wb_down_url_ali']
			|| $meta['wb_down_url_quark'] || $meta['wb_down_url_tianyi']
			|| $meta['wb_down_url_123pan'] );
	}

	/**
	 * 组装模板变量并渲染下载框。模板 return 字符串，全程无输出缓冲。
	 */
	public static function render_box( $post_id, $with_title = true ) {
		$post_id = (int) $post_id;
		if ( ! $post_id ) {
			return '';
		}

		// 紧急总闸：暂停期间一切渲染路径（含 wb_dlip_html 兼容过滤器）均不输出。
		$opt = V8DL_Admin::cnf();
		if ( ! empty( $opt['pause_all'] ) ) {
			return '';
		}

		$meta = V8DL_Admin::meta_values( $post_id );

		// 文章未开启下载或没有任何链接。
		if ( ! $meta['wb_dl_type'] || ! self::has_links( $meta ) ) {
			return '';
		}

		$dl_info = array();
		if ( $meta['wb_down_local_url'] ) {
			$dl_info['local'] = array(
				'name' => '本地直接下载',
				'url'  => $meta['wb_down_local_url'],
				'psw'  => '',
			);
		}
		if ( $meta['wb_down_url'] ) {
			$dl_info['baidu'] = array(
				'name' => '百度网盘下载',
				'url'  => $meta['wb_down_url'],
				'psw'  => $meta['wb_down_pwd'],
			);
		}
		if ( $meta['wb_down_url_lanzou'] ) {
			$dl_info['lanzou'] = array(
				'name' => '蓝奏云下载',
				'url'  => $meta['wb_down_url_lanzou'],
				'psw'  => '',
			);
		}
		if ( $meta['wb_down_url_ali'] ) {
			$dl_info['ali'] = array(
				'name' => '阿里云盘下载',
				'url'  => $meta['wb_down_url_ali'],
				'psw'  => '',
			);
		}
		if ( $meta['wb_down_url_quark'] ) {
			$dl_info['quark'] = array(
				'name' => '夸克网盘下载',
				'url'  => $meta['wb_down_url_quark'],
				'psw'  => '',
			);
		}
		if ( $meta['wb_down_url_tianyi'] ) {
			$dl_info['tianyi'] = array(
				'name' => '天翼云盘下载',
				'url'  => $meta['wb_down_url_tianyi'],
				'psw'  => '',
			);
		}
		if ( $meta['wb_down_url_123pan'] ) {
			$dl_info['pan123'] = array(
				'name' => '123云盘下载',
				'url'  => $meta['wb_down_url_123pan'],
				'psw'  => '',
			);
		}
		if ( $meta['wb_down_url_ct'] ) {
			$dl_info['ct'] = array(
				'name' => '城通网盘下载',
				'url'  => $meta['wb_down_url_ct'],
				'psw'  => '',
			);
		}
		if ( $meta['wb_down_url_magnet'] ) {
			$dl_info['magnet'] = array(
				'name' => '磁力链接',
				'url'  => $meta['wb_down_url_magnet'],
				'psw'  => '',
			);
		}
		if ( $meta['wb_down_url_xunlei'] ) {
			$dl_info['xunlei'] = array(
				'name' => '迅雷下载',
				'url'  => $meta['wb_down_url_xunlei'],
				'psw'  => '',
			);
		}

		$opt         = V8DL_Admin::cnf();
		$remark_info = $opt['remark'];
		$need_login  = ! empty( $opt['need_member'] );
		$is_login    = is_user_logged_in();
		$need_comment = ( '1' === (string) $meta['wb_dl_mode'] ) || ! empty( $opt['need_comment'] );
		$is_comment  = $need_comment ? self::is_commenter( $post_id ) : false;

		// 1.4.0 起下载次数显示在下载框标题栏（原侧栏组件中的展示已移除）。
		$display_count = ! empty( $opt['display_count'] );
		$count         = $display_count ? (int) get_post_meta( $post_id, 'post_downs', true ) : 0;

		if ( $with_title ) {
			$template = V8DL_PATH . '/templates/box.php';
		} else {
			$template = V8DL_PATH . '/templates/widget.php';
		}

		$html = include $template;

		return is_string( $html ) ? $html : '';
	}

	/**
	 * 当前访客是否在该文章下留过言（登录用户按账号邮箱，匿名按评论 cookie）。
	 * 旧版把 cookie 值直接拼进 SQL，这里改为 prepare；且匿名 cookie 可被任意伪造
	 * （评论昵称是公开信息），因此要求同一行评论的邮箱与昵称同时匹配。
	 */
	public static function is_commenter( $post_id ) {
		global $wpdb;

		$user_id = (int) get_current_user_id();
		if ( $user_id > 0 ) {
			$user = get_userdata( $user_id );
			$email = $user ? $user->user_email : '';
			if ( '' === $email ) {
				return false;
			}
			// 登录用户的邮箱来自账户而非客户端 cookie，按邮箱匹配即可。
			$sql = $wpdb->prepare(
				"SELECT comment_ID FROM {$wpdb->comments}
				 WHERE comment_post_ID = %d AND comment_approved = '1'
				   AND comment_author_email = %s
				 LIMIT 1",
				$post_id,
				$email
			);
		} else {
			if ( ! isset( $_COOKIE[ 'comment_author_email_' . COOKIEHASH ] ) ) {
				return false;
			}
			$email = str_replace( '%40', '@', $_COOKIE[ 'comment_author_email_' . COOKIEHASH ] );
			$name  = isset( $_COOKIE[ 'comment_author_' . COOKIEHASH ] ) ? $_COOKIE[ 'comment_author_' . COOKIEHASH ] : '';
			if ( '' === $email || '' === $name ) {
				return false;
			}
			$sql = $wpdb->prepare(
				"SELECT comment_ID FROM {$wpdb->comments}
				 WHERE comment_post_ID = %d AND comment_approved = '1'
				   AND comment_author_email = %s AND comment_author = %s
				 LIMIT 1",
				$post_id,
				$email,
				$name
			);
		}

		return (bool) $wpdb->get_var( $sql );
	}

	public function enqueue() {
		// wp_head 阶段主循环尚未开始，get_the_ID() 可能为空，必须用查询对象取 ID。
		$post_id = get_queried_object_id();
		if ( ! $post_id || ! is_singular( 'post' ) ) {
			return;
		}

		$meta = V8DL_Admin::meta_values( $post_id );
		if ( ! $meta['wb_dl_type'] ) {
			return;
		}

		if ( ! wp_script_is( 'jquery', 'enqueued' ) ) {
			wp_enqueue_script( 'jquery' );
		}

		// wbui 为内置 UI 样式库，若其他插件已加载同名资源则复用。
		if ( ! wp_style_is( 'wbui-css', 'registered' ) ) {
			wp_enqueue_style( 'wbui-css', plugin_dir_url( V8DL_BASE_FILE ) . 'assets/wbui/assets/wbui.css', array(), V8DL_VER );
			wp_enqueue_script( 'wbui-js', plugin_dir_url( V8DL_BASE_FILE ) . 'assets/wbui/wbui.js', array(), V8DL_VER, true );
		}

		wp_enqueue_style( 'v8dl-front', plugin_dir_url( V8DL_BASE_FILE ) . 'assets/wbp_dlipp.css', array(), V8DL_VER );
		wp_enqueue_script( 'v8dl-front', plugin_dir_url( V8DL_BASE_FILE ) . 'assets/v8dl-front.js', array( 'jquery' ), V8DL_VER, true );

		$config = 'var v8dl_config = ' . wp_json_encode(
			array(
				'ajax_url' => admin_url( 'admin-ajax.php' ),
				'pid'      => (int) $post_id,
				'dir'      => plugin_dir_url( V8DL_BASE_FILE ),
				'ver'      => V8DL_VER,
			)
		) . ';';
		wp_add_inline_script( 'v8dl-front', $config, 'before' );

		$opt = V8DL_Admin::cnf();

		// 回复后下载：提交评论时强制写入评论者 cookie，访客评论后即可直接下载。
		$need_comment = ( '1' === (string) $meta['wb_dl_mode'] ) || ! empty( $opt['need_comment'] );
		if ( $need_comment ) {
			add_filter( 'comment_form_field_cookies', '__return_false' );
			add_action( 'set_comment_cookies', array( __CLASS__, 'force_comment_cookies' ), 10, 3 );
		}

		if ( 2 === (int) $opt['sticky_mode'] ) {
			add_filter( 'body_class', array( __CLASS__, 'body_class' ) );
		}
	}

	public static function force_comment_cookies( $comment, $user, $cookies_consent ) {
		wp_set_comment_cookies( $comment, $user, true );
	}

	public static function body_class( $classes ) {
		$classes[] = 'wb-with-sticky-btm';
		return $classes;
	}

	/**
	 * AJAX：点击下载按钮后取真实链接。响应结构与旧插件一致：
	 * {code:0/1/2/3, is_login, data:{url, pwd, post_downs}}
	 */
	public function ajax() {
		$post_id = isset( $_POST['pid'] ) ? intval( $_POST['pid'] ) : 0;
		$rid     = isset( $_POST['rid'] ) ? sanitize_key( wp_unslash( $_POST['rid'] ) ) : '';
		$channels = array(
			'local'  => 'wb_down_local_url',
			'baidu'  => 'wb_down_url',
			'lanzou' => 'wb_down_url_lanzou',
			'ali'    => 'wb_down_url_ali',
			'quark'  => 'wb_down_url_quark',
			'tianyi' => 'wb_down_url_tianyi',
			'pan123' => 'wb_down_url_123pan',
			'ct'     => 'wb_down_url_ct',
			'xunlei' => 'wb_down_url_xunlei',
			'magnet' => 'wb_down_url_magnet',
		);

		$opt  = V8DL_Admin::cnf();
		$meta = V8DL_Admin::meta_values( $post_id );

		$ret = array(
			'code'     => 0,
			'is_login' => is_user_logged_in(),
			'data'     => array( 'url' => '', 'pwd' => '' ),
		);

		do {
			// 紧急总闸：暂停期间拒绝取链（code 4，前台提示"维护中"），计数不动。
			if ( ! empty( $opt['pause_all'] ) ) {
				$ret['code'] = 4;
				break;
			}

			if ( ! $post_id || ! isset( $channels[ $rid ] ) ) {
				$ret['code'] = 1;
				break;
			}

			$need_login  = ! empty( $opt['need_member'] );
			$need_comment = ( '1' === (string) $meta['wb_dl_mode'] ) || ! empty( $opt['need_comment'] );

			if ( $need_login && ! is_user_logged_in() ) {
				$ret['code'] = 2;
				break;
			}

			if ( $need_comment && ! self::is_commenter( $post_id ) ) {
				$ret['code'] = 3;
				break;
			}

			// 未开启下载、或所选通道没有链接：拒绝且不计数（防止刷任意文章的下载次数）。
			$url = (string) $meta[ $channels[ $rid ] ];
			if ( empty( $meta['wb_dl_type'] ) || '' === $url ) {
				$ret['code'] = 1;
				break;
			}

			$ret['data']['url'] = $url;
			if ( 'baidu' === $rid ) {
				$ret['data']['pwd'] = $meta['wb_down_pwd'];
			}

			// 下载计数，沿用旧键名 post_downs。
			$count = (int) get_post_meta( $post_id, 'post_downs', true );
			$count += 1;
			update_post_meta( $post_id, 'post_downs', $count );
			$ret['data']['post_downs'] = $count;
		} while ( false );

		wp_send_json( $ret );
	}

	/**
	 * 侧栏下载信息 widget。沿用旧 id_base，保证站点已添加的侧栏组件继续生效。
	 * 1.3.1 起改用 WP_Widget 类注册：WP 5.8+ 的区块小工具编辑器不显示
	 * 老式 wp_register_sidebar_widget 注册的 PHP 组件，导致后台找不到。
	 */
	public function widgets_init() {
		register_widget( 'V8DL_Widget' );
	}

	public static function render_widget() {
		// 组件内容依赖"当前文章"：首页/列表页无明确语境，且页内没有下载框锚点可跳。
		if ( ! is_singular( 'post' ) ) {
			return;
		}

		$post_id = get_the_ID();
		$opt     = V8DL_Admin::cnf();

		$meta = V8DL_Admin::meta_values( $post_id );
		// 未开启下载、或未填写任何网盘链接的文章不显示。
		if ( ! $meta['wb_dl_type'] || ! self::has_links( $meta ) ) {
			return;
		}

		$need_login   = ! empty( $opt['need_member'] );
		$is_login     = is_user_logged_in();
		$need_comment = ( '1' === (string) $meta['wb_dl_mode'] ) || ! empty( $opt['need_comment'] );
		$is_comment   = $need_comment ? self::is_commenter( $post_id ) : false;

		$html = include V8DL_PATH . '/templates/widget.php';

		if ( is_string( $html ) && '' !== $html ) {
			echo $html; // phpcs:ignore WordPress.Security.EscapeOutput -- 模板内部已转义
		}
	}

	/**
	 * 底部/顶部悬浮下载条（sticky_mode 1 顶部 / 2 底部）。
	 */
	public function sticky_bar() {
		if ( ! is_singular( 'post' ) ) {
			return;
		}

		$opt = V8DL_Admin::cnf();
		if ( empty( $opt['sticky_mode'] ) ) {
			return;
		}

		$sticky_mode = (int) $opt['sticky_mode'];
		$post_id     = get_the_ID();
		$meta        = V8DL_Admin::meta_values( $post_id );
		if ( ! $meta['wb_dl_type'] || ! self::has_links( $meta ) ) {
			return;
		}

		$html = include V8DL_PATH . '/templates/sticky.php';

		if ( is_string( $html ) && '' !== $html ) {
			echo $html; // phpcs:ignore WordPress.Security.EscapeOutput -- 模板内部已转义
		}
	}
}

/**
 * 侧栏"下载信息"组件（WP_Widget 标准注册，兼容 WP 5.8+ 区块小工具编辑器）。
 * id_base 沿用旧版命名（保证已有侧栏组件配置无缝接管），外观与老式渲染一致。
 */
class V8DL_Widget extends WP_Widget {

	public function __construct() {
		parent::__construct(
			'v8dl-download-info',
			'#下载信息#',
			array( 'description' => '侧栏展示当前文章的下载信息与下载次数' )
		);
	}

	public function widget( $args, $instance ) {
		V8DL_Front::render_widget();
	}

	public function form( $instance ) {
		$title = isset( $instance['title'] ) ? $instance['title'] : '';
		?>
		<p>
			<label>标题（留空使用默认样式）：</label>
			<input class="widefat" name="<?php echo esc_attr( $this->get_field_name( 'title' ) ); ?>" value="<?php echo esc_attr( $title ); ?>">
		</p>
		<p class="description">仅在已开启下载且填写了网盘链接的文章页显示。</p>
		<?php
	}

	public function update( $new_instance, $old_instance ) {
		$instance          = array();
		$instance['title'] = isset( $new_instance['title'] ) ? sanitize_text_field( $new_instance['title'] ) : '';
		return $instance;
	}
}
