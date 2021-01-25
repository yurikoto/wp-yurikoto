<?php
/**
* Plugin Name: WP-Yurikoto
* Description: 将Yurikoto接入您的Wordpress站点，让您和您的用户欣赏到百合美图美句。
* Author: Yurikoto团队
* Author URI:https://yurikoto.com/
* Version: 1.0.0
* Network: True
* License: AGPLv3 or later
* License URI: https://www.gnu.org/licenses/agpl-3.0.en.html
*/

/**
 * 短代码
 * @return string
 */
function yurikoto_shortcode(){
    return "<p class='yurikoto-sentence'>Yurikoto</p>";
}
add_shortcode('yurikoto', 'yurikoto_shortcode');

/**
 * 设置界面
 */
function yurikoto_options_page_html() {
    $cur_ver = '1.0.0';
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

    if($latest_ver != $cur_ver){
        echo '<div id="setting-error-settings-updated" class="updated settings-error notice is-dissmissible"><h3>检测到新版本 <a href="https://github.com/yurikoto/wp-yurikoto" target="_blank">点击查看</a></h3>  当前版本 ' . $cur_ver . ' 最新版本 ' . $latest_ver . '</div>';
    }

    // check user capabilities
    if (!current_user_can('manage_options')) {
        return;
    }
    if(array_key_exists('submit_yurikoto_update', $_POST)){
        update_option('yurikoto_js_source', $_POST['js_source']);
        ?>
        <div id="setting-error-settings-updated" class="updated settings-error notice is-dissmissible"><strong>设置已保存</strong></div>
        <?php
    }
    $js_source = get_option('yurikoto_js_source', 'local');
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
                        <label for="js_source">Javascript源</label>
                    </th>
                    <td>
                        <select name="js_source">
                            <option value="local" <?php if($js_source == 'local'){echo 'selected=""';} ?>>本地</option>
                            <option value="jsdelivr" <?php if($js_source == 'jsdelivr'){echo 'selected=""';} ?>>jsdelivr</option>
                        </select>
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
    echo '<script src="https://cdn.staticfile.org/jquery/3.2.1/jquery.min.js"></script>';
    if($js_source == 'local'){
        $js_url = get_site_url() . '/wp-content/plugins/wp-yurikoto/js/yurikoto.js?ver=1.0.0';
    }
    else{
        $js_url = "https://cdn.jsdelivr.net/gh/yurikoto/wp-yurikoto@master/js/yurikoto.js?ver=1.0.0";
    }
    echo '<script type="text/javascript" src="' . $js_url . '" ></script>';
}
add_action('wp_footer', 'yurikoto_display_js');

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
