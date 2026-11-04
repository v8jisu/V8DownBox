/**
 * V8下载信息框 - 前台脚本
 * 重写自 wbp_front.js（GPL-2.0+）：逻辑一致，接口换为 v8dl_front，
 * 修复旧版 clipboard 依赖隐藏 input 的问题，弹窗/提示仍复用 wbui。
 */
/* global wbui, v8dl_config */
(function ($) {
    var cfg = window.v8dl_config || {
        ajax_url: '/wp-admin/admin-ajax.php',
        pid: 0,
        dir: '',
        ver: '1'
    };

    function copyText(text) {
        var el = document.createElement('textarea');
        el.value = text;
        el.style.position = 'fixed';
        el.style.opacity = '0';
        document.body.appendChild(el);
        el.select();
        try { document.execCommand('copy'); } catch (e) { /* ignore */ }
        document.body.removeChild(el);
    }

    function escHtml(s) {
        return String(s == null ? '' : s)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#39;');
    }

    function pwdDialog(url, pwd) {
        return '<div class="wbdl-main" id="J_ppoDownloadDLIPP">'
            + '<div class="dl-info">'
            + '<svg class="wb-icon wbsico-dlipp-confirm"><use xlink:href="#wbsico-dlipp-confirm"></use></svg>'
            + '<span>密码</span> <b class="dl-psw">' + escHtml(pwd) + '</b> <span>已复制</span>'
            + '</div>'
            + '<a class="wb-btn wb-btn-outlined wb-btn-download j-copy-psw" href="' + escHtml(url) + '" data-clipboard-text="' + escHtml(pwd) + '" target="_blank" rel="nofollow">'
            + '<svg class="wb-icon-dlipp wbsico-dlipp-download"><use xlink:href="#wbsico-dlipp-download"></use></svg>'
            + '<span>前往下载页面</span></a>'
            + '</div>'
            + '<div class="info-for-sp"><span>低版本浏览器需手动复制密码再前往下载页面下载，谢谢</span></div>';
    }

    function blockedDialog(url) {
        return '<div class="dl-info">浏览器拦截了跳转，请重试。<br><br>'
            + '<a class="wb-btn wb-btn-outlined wb-btn-download" href="' + escHtml(url) + '" target="_blank" rel="nofollow">'
            + '<svg class="wb-icon-dlipp wbsico-dlipp-download"><use xlink:href="#wbsico-dlipp-download"></use></svg>'
            + '<span>去下载</span></a></div>';
    }

    $(function () {
        $(document).on('click', '.j-wbdlbtn-dlipp', function () {
            var $btn = $(this);
            var cachedUrl = $btn.data('dl-url');

            if (cachedUrl) {
                var cachedPwd = $btn.data('dl-pwd') || '';
                if (cachedPwd) {
                    copyText(cachedPwd);
                    wbui.open({ content: pwdDialog(cachedUrl, cachedPwd), type: 'wbdl-dialog' });
                } else if (!window.open(cachedUrl, '_blank')) {
                    wbui.open({ content: blockedDialog(cachedUrl), type: 'wbdl-dialog' });
                }
                return false;
            }

            $.post(cfg.ajax_url, {
                action: 'v8dl_front',
                pid: cfg.pid || 0,
                rid: $btn.data('rid')
            }, function (r) {
                if (!r) { return; }
                switch (r.code) {
                    case 0:
                        var url = r.data.url;
                        var pwd = r.data.pwd || '';
                        if (!url) {
                            wbui.alert('下载异常，请联系网站管理员修正下载链接');
                            break;
                        }
                        $btn.data('dl-url', url);
                        $btn.data('dl-pwd', pwd);
                        if (pwd) {
                            copyText(pwd);
                            wbui.open({ content: pwdDialog(url, pwd), type: 'wbdl-dialog' });
                        } else if (!window.open(url, '_blank')) {
                            wbui.open({ content: blockedDialog(url), type: 'wbdl-dialog' });
                        }
                        if (r.data.post_downs) {
                            $('.j-wbdl-count').text(r.data.post_downs);
                        }
                        break;
                    case 1:
                        wbui.alert('下载异常，请联系网站管理员');
                        break;
                    case 2:
                        wbui.alert('请先登录，谢谢');
                        break;
                    case 3:
                        wbui.toast('请先留下您的评论，谢谢');
                        break;
                    case 4:
                        wbui.toast('下载功能维护中，请稍后再试');
                        break;
                    default:
                        wbui.alert('下载异常，请联系网站管理员');
                }
            }, 'json');

            return false;
        });

        // 悬浮条滚动显隐
        if ($('#J_downloadBar').length) {
            $(window).on('scroll', function () {
                var top = $(window).scrollTop();
                $('#J_downloadBar')[(top > 200 ? 'add' : 'remove') + 'Class']('sb-active');
            });
        }
    });

    // SVG 图标 sprite：localStorage 缓存，避免每页重复请求。
    // 缓存键在用户侧可被篡改，注入前先校验内容形态，防止站点其他位置的 XSS 借此持久化。
    (function (w, d) {
        if (!d.createElementNS || !d.createElementNS('http://www.w3.org/2000/svg', 'svg').createSVGRect) {
            return;
        }
        var VER_KEY = 'V8DL_SVG_VER';
        var DATA_KEY = 'V8DL_SVG_DATA';
        var url = cfg.dir + 'assets/wb_svg.html';

        function safeSvg(svg) {
            if (typeof svg !== 'string') { return false; }
            var low = svg.replace(/^\s+/, '').toLowerCase();
            if (low.slice(0, 4) !== '<svg') { return false; }
            return low.indexOf('<script') === -1
                && low.indexOf('javascript:') === -1
                && !/on[a-z]+\s*=/.test(low);
        }

        function inject(svg) {
            d.body.insertAdjacentHTML('afterbegin', svg);
        }

        var hasLS = ('localStorage' in w) && w.localStorage !== null;
        if (hasLS && localStorage.getItem(VER_KEY) === cfg.ver) {
            var cached = localStorage.getItem(DATA_KEY);
            if (safeSvg(cached)) {
                inject(cached);
                return;
            }
        }

        var xhr = new XMLHttpRequest();
        xhr.open('GET', url, true);
        xhr.onload = function () {
            if (xhr.status >= 200 && xhr.status < 400 && safeSvg(xhr.responseText)) {
                inject(xhr.responseText);
                if (hasLS) {
                    localStorage.setItem(DATA_KEY, xhr.responseText);
                    localStorage.setItem(VER_KEY, cfg.ver);
                }
            }
        };
        xhr.send();
    })(window, document);
})(jQuery);
