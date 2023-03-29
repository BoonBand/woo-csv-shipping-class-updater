<?php
/*
Plugin Name: CSV Shipping Class Updater
Description: CSV Shipping Class Updater
Version: 1.0
Author: Boon.Band
Author URI: https://boon.band/
*/

if (!defined('ABSPATH')) {
    exit;
}

class CSVShippingClassUpdater {
    public function __construct() {
        add_action('admin_menu', [$this, 'add_admin_page']);
        add_action('admin_enqueue_scripts', [$this, 'enqueue_admin_scripts']);
        add_action('wp_ajax_process_csv', [$this, 'process_csv']);
    }

    public function add_admin_page() {
        add_submenu_page('woocommerce', 'CSV Shipping Class Updater', 'CSV Shipping Class Updater', 'manage_options', 'csv-shipping-class-updater', [$this, 'render_admin_page']);
    }

    public function render_admin_page() {
        ?>
        <div class="wrap">
            <h1>CSV Shipping Class Updater</h1>
            <form id="csv-form" enctype="multipart/form-data">
                <p>
                    <label for="csv-textarea">Enter CSV data:</label>
                    <br>
                    <textarea id="csv-textarea" name="csv_textarea" rows="10" cols="50"></textarea>
                </p>
                <p>or</p>
                <p>
                    <label for="csv-file">Select CSV file:</label>
                    <br>
                    <input type="file" id="csv-file" name="csv_file" accept=".csv">
                </p>
                <p>
                    <input type="submit" class="button button-primary" value="Update Shipping Class">
                </p>
                <div id="progress"></div>
            </form>
        </div>
        <?php
    }

    public function process_csv() {
        check_ajax_referer('csv-shipping-class-updater', 'security');

        if (!empty($_FILES['csv_file']['tmp_name'])) {
            $csv_content = file_get_contents($_FILES['csv_file']['tmp_name']);
        } else {
            $csv_content = $_POST['csv_textarea'];
        }

        $csv_lines = explode("\n", $csv_content);

        foreach ($csv_lines as $line) {
            $data = str_getcsv($line);

            if (count($data) !== 2) {
                continue;
            }

            $sku = $data[0];
            $shipping_class_slug = $data[1];

            $product_id = wc_get_product_id_by_sku($sku);

            if (!$product_id) {
                continue;
            }

            $shipping_class_term = get_term_by('slug', $shipping_class_slug, 'product_shipping_class');

            if (!$shipping_class_term) {
                continue;
            }

            wp_set_object_terms($product_id, $shipping_class_term->term_id, 'product_shipping_class');
        }

        wp_send_json_success(['message' => 'Processing completed.']);
    }

    public function enqueue_admin_scripts($hook) {
        if ('woocommerce_page_csv-shipping-class-updater' !== $hook) {
            return;
        }

        wp_enqueue_script('csv-shipping-class-updater', plugin_dir_url(__FILE__) . 'csv-shipping-class-updater.js', ['jquery'], '1.0', true);
        wp_localize_script('csv-shipping-class-updater', 'ajax_object', [
            'ajax_url' => admin_url('admin-ajax.php'),
            'ajax_nonce' => wp_create_nonce('csv-shipping-class-updater'),
        ]);
    }


}

new CSVShippingClassUpdater();
