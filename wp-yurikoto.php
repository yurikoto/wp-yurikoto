<?php
/**
* Plugin Name: WP-Yurikoto
* Description: 将Yurikoto接入您的Wordpress站点，让您和您的用户欣赏到百合美图美句。
* Author: Yurikoto团队
* Author URI:https://yurikoto.com/
* Version: 1.0.1
* Network: True
* License: AGPLv3 or later
* License URI: https://www.gnu.org/licenses/agpl-3.0.en.html
*/

/**
 * 短代码
 * @return string
 */
function yurikoto_shortcode(){
    $do_pjax_optimize = get_option('yurikoto_do_pjax_optimize', 'false');
    if($do_pjax_optimize == 'false'){
        return "<p class='yurikoto-sentence'>Yurikoto</p>";
    }
    else{
        return "<p class='yurikoto-sentence'>Yurikoto</p><script>$.get('https://v1.yurikoto.com/sentence?encode=text', function(data, status){if(status === 'success'){
                    $('.yurikoto-sentence').text(data);
                    }
                    var yurikoto_is_loaded = 1;
                });</script>";
    }
}
add_shortcode('yurikoto', 'yurikoto_shortcode');

/**
 * 设置界面
 */
function yurikoto_options_page_html() {
    $cur_ver = '1.0.1';
    try{
        $header = array(
                'Referer: ' . get_site_url()
        );
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
        curl_setopt($ch, CURLOPT_URL, 'https://v1.yurikoto.com/statistic');
        // curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $output = curl_exec($ch);
        $res = json_decode($output, true);
        $latest_ver = $res['data']['other']['wp_plugin_latest'];

    }catch (Exception $e){
        $latest_ver = $cur_ver;
    }

    $is_latest = true;
    if($latest_ver != $cur_ver){
        $is_latest = false;
        echo '<div id="setting-error-settings-updated" class="updated settings-error notice is-dissmissible"><h3>检测到新版本 <a href="https://github.com/yurikoto/wp-yurikoto" target="_blank">点击查看</a></h3>  当前版本 ' . $cur_ver . ' 最新版本 ' . $latest_ver . '</div>';
    }

    // check user capabilities
    if (!current_user_can('manage_options')) {
        return;
    }
    if(array_key_exists('submit_yurikoto_update', $_POST)){
        update_option('yurikoto_js_source', $_POST['js_source']);
        update_option('yurikoto_import_jquery', $_POST['import_jquery']);
        update_option('yurikoto_do_pjax_optimize', $_POST['do_pjax_optimize']);
        ?>
        <div id="setting-error-settings-updated" class="updated settings-error notice is-dissmissible"><strong>设置已保存</strong></div>
        <?php
    }

    $js_source = get_option('yurikoto_js_source', 'local');
    $import_jquery = get_option('yurikoto_import_jquery', 'false');
    $do_pjax_optimize = get_option('yurikoto_do_pjax_optimize', 'false');

    ?>
    <div class=wrap>
        <h1><?= esc_html(get_admin_page_title()); ?></h1>
        <p>当前版本功能单一，更多功能敬请期待！</p>
        <form action="" method="post">
            <table class="form-table">
                <tr>
                    <h2>基本设置</h2>
                </tr>
                <tr>
                    <th>
                        <label for="import_jquery">是否引入jquery</label>
                    </th>
                    <td>
                        <select name="import_jquery">
                            <option value="true" <?php if($import_jquery == 'true'){echo 'selected=""';} ?>>是</option>
                            <option value="false" <?php if($import_jquery == 'false'){echo 'selected=""';} ?>>否</option>
                        </select>
                        <p>如果Yurikoto无法正常显示，请将此选项选为“是”。</p>
                    </td>
                </tr>
                <tr>
                    <th>
                        <label for="js_source">javascript源</label>
                    </th>
                    <td>
                        <select name="js_source">
                            <option value="local" <?php if($js_source == 'local'){echo 'selected=""';} ?>>本地</option>
                            <?php
                            if($is_latest){
                                ?>
                                <option value="jsdelivr" <?php if($js_source == 'jsdelivr'){echo 'selected=""';} ?>>jsdelivr</option>
                                <?php
                            }
                            ?>
                        </select>
                        <p>如果您使用的不是最新版本的WP-Yurikoto，请将此项选为“本地”。</p>
                    </td>
                </tr>
                <tr>
                    <th>
                        <label for="do_pjax_optimize">是否为pjax优化</label>
                    </th>
                    <td>
                        <select name="do_pjax_optimize">
                            <option value="true" <?php if($do_pjax_optimize == 'true'){echo 'selected=""';} ?>>是</option>
                            <option value="false" <?php if($do_pjax_optimize == 'false'){echo 'selected=""';} ?>>否</option>
                        </select>
                        <p>如果您的站点开启了pjax（表现为站内跳转浏览器不刷新），请将此项选为”是“。</p>
                    </td>
                </tr>
            </table><br>
            <input type="submit" name="submit_yurikoto_update" class="button button-primary" value="保存设置">
        </form>
    </div>
    <?php
}

/**
 * 输出js
 */
function yurikoto_display_js(){
    $js_source = get_option('yurikoto_js_source', 'local');
    $import_jquery = get_option('yurikoto_import_jquery', 'false');

    if($import_jquery == 'true'){
        echo '<script src="https://cdn.staticfile.org/jquery/3.2.1/jquery.min.js"></script>';
    }
    if($js_source == 'local'){
        $js_url = get_site_url() . '/wp-content/plugins/wp-yurikoto/js/yurikoto.js?ver=1.0.0';
    }
    else{
        $js_url = "https://cdn.jsdelivr.net/gh/yurikoto/wp-yurikoto@master/js/yurikoto.js?ver=1.0.0";
    }
    echo '<script type="text/javascript" src="' . $js_url . '" ></script>';
}
add_action('wp_head', 'yurikoto_display_js');

/**
 * 添加到设置菜单
 */
function yurikoto_options_page() {
    add_submenu_page(
        'options-general.php',
        'WP-Yurikoto设置',
        'WP-Yurikoto',
        'manage_options',
        'yurikoto',
        'yurikoto_options_page_html'
    );
}
add_action('admin_menu', 'yurikoto_options_page');
